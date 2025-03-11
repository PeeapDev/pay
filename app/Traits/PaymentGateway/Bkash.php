<?php

namespace App\Traits\PaymentGateway;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\TemporaryData;
use App\Models\UserNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Str;
use App\Traits\TransactionAgent;
use App\Traits\PayLink\TransactionTrait;
use GuzzleHttp\Exception\RequestException as RequestException2;

trait Bkash
{
    use TransactionAgent,TransactionTrait;
    public function bkashInit($output = null) {
        if(!$output) $output = $this->output;
        $credentials = $this->getBkashCredentials($output);
        if($output['type'] === PaymentGatewayConst::TYPEADDMONEY){
            return  $this->setupBkashInitAddMoney($output,$credentials);
         }else{
             return  $this->setupBkashPayLink($output,$credentials);
         }
    }
    public function setupBkashInitAddMoney($output,$credentials){
        $amount = $output['amount']->total_amount ? number_format($output['amount']->total_amount,2,'.','') : 0;
        $currency = $output['currency']['currency_code']??"USD";

        if(auth()->guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
            $user_email = $user->email;
        }

        $grant_token                = $this->createToken($credentials);

        if($grant_token['status'] == false){
            throw new Exception($grant_token['message'] ?? __("Something went wrong! Please try again."));
        }

        $base_url                   = $credentials->base_url . '/tokenized/checkout/create';
        $client                     = new \GuzzleHttp\Client();

        $temp_record_token = generate_unique_string('temporary_datas', 'identifier', 60);
        $this->setUrlParams("token=" . $temp_record_token); // set Parameter to URL for identifying when return success/cancel
        $redirection = $this->getRedirection();
        $url_parameter = $this->getUrlParams();

        $body_form                  = [
            'mode'                  => '0011',
            'payerReference'        => $temp_record_token,
            'callbackURL'           => $this->setGatewayRoute($redirection['return_url'], PaymentGatewayConst::BKASH, $url_parameter),
            "amount"                => $amount,
            "currency"              => $currency,
            "intent"                => "sale",
            "merchantInvoiceNumber" => generate_random_string_number(10)
        ];
        try{
            $response_data = $client->request('POST', $base_url, [
                'body' => json_encode($body_form),
                'headers' => [
                    'Authorization' => $grant_token['id_token'],
                    'X-APP-Key' => $credentials->app_key,
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);

            $response   = $response_data->getBody();
            $result     = json_decode($response,true);
            if(isset($result['statusCode'])){
                if($result['statusCode'] == 0000){
                    $this->bkashJunkInsert($result,$temp_record_token);
                    $redirect_url   = $result['bkashURL'];
                    return redirect()->away($redirect_url);
                }else{
                    throw new Exception($result['statusMessage'] ?? __("Something went wrong! Please try again."));

                }
            }

        }catch (RequestException2 $e) {
            throw new Exception(__("Something went wrong! Please try again."));
        }

    }
    public function setupBkashPayLink($output,$credentials){

        $amount = $output['charge_calculation']['requested_amount'] ? number_format($output['charge_calculation']['requested_amount'],2,'.','') : 0;
        $currency = $output['charge_calculation']['sender_cur_code']??"USD";

        $grant_token                = $this->createToken($credentials);

        if($grant_token['status'] == false){
            throw new Exception($grant_token['message'] ?? __("Something went wrong! Please try again."));
        }

        $base_url                   = $credentials->base_url . '/tokenized/checkout/create';
        $client                     = new \GuzzleHttp\Client();

        $temp_record_token = generate_unique_string('temporary_datas', 'identifier', 60);
        $this->setUrlParams("token=" . $temp_record_token); // set Parameter to URL for identifying when return success/cancel
        $redirection = $this->getRedirection();
        $url_parameter = $this->getUrlParams();


        $body_form                  = [
            'mode'                  => '0011',
            'payerReference'        => $temp_record_token,
            'callbackURL'           => $this->setGatewayRoute($redirection['return_url'], PaymentGatewayConst::BKASH, $url_parameter),
            "amount"                => $amount,
            "currency"              => $currency,
            "intent"                => "sale",
            "merchantInvoiceNumber" => generate_random_string_number(10)
        ];
        try{
            $response_data = $client->request('POST', $base_url, [
                'body' => json_encode($body_form),
                'headers' => [
                    'Authorization' => $grant_token['id_token'],
                    'X-APP-Key' => $credentials->app_key,
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);

            $response   = $response_data->getBody();
            $result     = json_decode($response,true);
            if(isset($result['statusCode'])){
                if($result['statusCode'] == 0000){
                    $this->bkashJunkInsertPayLink($result,$temp_record_token);
                    $redirect_url   = $result['bkashURL'];
                    return redirect()->away($redirect_url);
                }else{
                    throw new Exception($result['statusMessage'] ?? __("Something went wrong! Please try again."));
                }
            }

        }catch (RequestException2 $e) {
            throw new Exception(__("Something went wrong! Please try again."));
        }



    }
    public function getBkashCredentials($output) {
        $gateway        = $output['gateway'] ?? null;
        if(!$gateway) throw new Exception(__("Payment gateway not available"));
        $credentials    = $gateway->credentials;
        $app_key        = getPaymentCredentials($credentials,'App Key');
        $secret_key     = getPaymentCredentials($credentials,'Secret Key');
        $username       = getPaymentCredentials($credentials,'Username');
        $password       = getPaymentCredentials($credentials,'Password');
        $sandbox_url    = getPaymentCredentials($credentials,'Sandbox Url');
        $production_url = getPaymentCredentials($credentials,'Production Url');

        $mode           = $gateway->env;

        $paypal_register_mode = [
            PaymentGatewayConst::ENV_SANDBOX => "sandbox",
            PaymentGatewayConst::ENV_PRODUCTION => "live",
        ];
        if(array_key_exists($mode,$paypal_register_mode)) {
            $mode = $paypal_register_mode[$mode];
        }else {
            $mode = "sandbox";
        }

        if(strtoupper($mode) ==  PaymentGatewayConst::ENV_SANDBOX){
            $base_url = $sandbox_url;
        }else{
            $base_url = $production_url;
        }

        return (object) [
            'app_key'       => $app_key,
            'secret_key'    => $secret_key,
            'username'      => $username,
            'password'      => $password,
            'base_url'      => $base_url,
            'mode'          => $mode,
        ];

    }
    // create grant token for bkash
    public function createToken($credentials){

        $client             = new \GuzzleHttp\Client();
        $base_url           = $credentials->base_url.'/'.'tokenized/checkout/token/grant';
        try{
            $body_form          = [
                'app_key'       => $credentials->app_key,
                'app_secret'    => $credentials->secret_key,
            ];


            $response_data      = $client->request('POST', $base_url, [
            'body'              => json_encode($body_form),
            'headers'           => [
                'accept'        => 'application/json',
                'content-type'  => 'application/json',
                'username'      => $credentials->username,
                'password'      => $credentials->password,
            ],
            ]);
            $response           =  $response_data->getBody();
            $result             = json_decode($response,true);
            if(isset($result['statusCode'])){
                if($result['statusCode'] == 0000){
                    $data           = [
                        'status'    => true,
                        'id_token'  => $result['id_token'],
                    ];
                    return $data;
                }else{
                    $data           = [
                        'status'    => false,
                        'message'   => $result['statusMessage'],
                    ];
                    return $data;
                }
            }
        }catch (RequestException2 $e) {
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $errorMessage = json_decode($responseBody, true);
                throw new Exception($errorMessage['message'] ?? __("Something went wrong! Please try again."));
            } else {

                throw new Exception(__("Something went wrong! Please try again."));
            }
        }


    }

    public function bKashPlainText($string) {
        $string = Str::lower($string);
        return preg_replace("/[^A-Za-z0-9]/","",$string);
    }

    public function bkashJunkInsert($response,$temp_identifier) {
        $output = $this->output;
        $creator_table = $creator_id = $wallet_table = $wallet_id = null;

        if(authGuardApi()['type']  == "AGENT"){
            $creator_table = authGuardApi()['user']->getTable();
            $creator_id = authGuardApi()['user']->id;
            $creator_guard = authGuardApi()['guard'];
            $wallet_table = $output['wallet']->getTable();
            $wallet_id = $output['wallet']->id;
        }else{
            $creator_table = auth()->guard(get_auth_guard())->user()->getTable();
            $creator_id = auth()->guard(get_auth_guard())->user()->id;
            $creator_guard = get_auth_guard();
            $wallet_table = $output['wallet']->getTable();
            $wallet_id = $output['wallet']->id;
        }

            $data = [
                'gateway'       => $output['gateway']->id,
                'currency'      => $output['currency']->id,
                'amount'        => json_decode(json_encode($output['amount']),true),
                'response'      => $response,
                'wallet_table'  => $wallet_table,
                'wallet_id'     => $wallet_id,
                'creator_table' => $creator_table,
                'creator_id'    => $creator_id,
                'creator_guard' => $creator_guard,
            ];

        return TemporaryData::create([
            'type'          => PaymentGatewayConst::BKASH,
            'identifier'    => $temp_identifier,
            'data'          => $data,
        ]);
    }
    public function bkashJunkInsertPayLink($response,$temp_identifier) {
        $output = $this->output;
        $wallet_table = $output['wallet']->getTable();
        $wallet_id = $output['wallet']->id;
        $user_relation_name = strtolower($output['user_type'])??'user';

            $data = [
                'type'                  => $output['type'],
                'gateway'               => $output['gateway']->id,
                'currency'              => $output['currency']->id,
                'validated'             => $output['validated'],
                'charge_calculation'    => json_decode(json_encode($output['charge_calculation']),true),
                'response'              => $response,
                'wallet_table'          => $wallet_table,
                'wallet_id'             => $wallet_id,
                'creator_guard'         => $output['user_guard']??'',
                'user_type'             => $output['user_type']??'',
                'user_id'               => $output['wallet']->$user_relation_name->id??'',
            ];

        return TemporaryData::create([
            'type'          => PaymentGatewayConst::BKASH,
            'identifier'    => $temp_identifier,
            'data'          => $data,
        ]);
    }

    public function bkashSuccess($output = null) {

        $payment_id         = $output['tempData']['data']->response->paymentID;

        $credentials        = $this->getBkashCredentials($output);
        $grant_token                = $this->createToken($credentials);
        if($grant_token['status'] == false){
            return $grant_token;
        }

        $execute_url                   = $credentials->base_url . '/tokenized/checkout/execute';
        $client                     = new \GuzzleHttp\Client();

        $body_form                  = [
            'paymentID'             => $payment_id,

        ];

        $response_data = $client->request('POST', $execute_url, [
            'body' => json_encode($body_form),
            'headers' => [
                'Authorization' => $grant_token['id_token'],
                'X-APP-Key' => $credentials->app_key,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        $response   = $response_data->getBody();
        $result     = json_decode($response,true);

        $status = $result['statusCode'] == '0000' ? true : false;
        if(isset($result['statusCode'])){
            if($result['statusCode'] != 0000){
                throw new Exception($result['statusMessage'] ?? __("Something went wrong! Please try again."));
            }
        }

        if ($status != true) {
            $transaction_status = PaymentGatewayConst::STATUSREJECTED;
        } else {
            if (isset($result['transactionStatus']) && $result['transactionStatus'] == "Completed") {
                $transaction_status = PaymentGatewayConst::STATUSSUCCESS;
            } else {
                $transaction_status = PaymentGatewayConst::STATUSPENDING;
            }
        }
        $output['capture']      = $result;
        $output['callback_ref'] = $result['payerReference']; // it's also temporary identifier

        if (!$this->searchWithReferenceInTransaction($output['callback_ref'])) {

            try {
                if($output['type'] === PaymentGatewayConst::TYPEADDMONEY){
                    if(userGuard()['type'] == "USER"){
                        return $this->createTransactionBkash($output,$transaction_status);
                    }else{
                        return $this->createTransactionChildRecords($output,$transaction_status);
                    }
                }else{
                    return $this->createTransactionPayLink($output,$transaction_status);
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    public function createTransactionBkash($output,$status) {
        if($this->predefined_user) {
            $user = $this->predefined_user;
        }elseif(Auth::guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
        }
        $basic_setting = BasicSettings::first();
        $trx_id = 'AM'.getTrxNum();
        $inserted_id = $this->insertRecordBkash($output,$trx_id,$status);
        $this->insertChargesBkash($output,$inserted_id);
        $this->adminNotification($trx_id,$output,$status);
        $this->insertDeviceBkash($output,$inserted_id);
        // $this->removeTempDataBkash($output);
        //send sms & email notifications
        $this->sendNotifications($output,$trx_id);

        if($this->requestIsApiUser()) {
            // logout user
            $api_user_login_guard = $this->output['api_login_guard'] ?? null;
            if($api_user_login_guard != null) {
                auth()->guard($api_user_login_guard)->logout();
            }
        }

    }

    public function insertRecordBkash($output,$trx_id,$status) {

        $trx_id = $trx_id;
        DB::beginTransaction();
        try{
            if($this->predefined_user) {
                $user = $this->predefined_user;
            }elseif(Auth::guard(get_auth_guard())->check()){
                $user = auth()->guard(get_auth_guard())->user();
            }
            $user_id = $user->id;
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user_id,
                'user_wallet_id'                => $output['wallet']->id,
                'payment_gateway_currency_id'   => $output['currency']->id,
                'type'                          => "ADD-MONEY",
                'trx_id'                        => $trx_id,
                'request_amount'                => $output['amount']->requested_amount,
                'payable'                       => $output['amount']->total_amount,
                'available_balance'             => $output['wallet']->balance + $output['amount']->requested_amount,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEADDMONEY," ")) . " With " . $output['gateway']->name,
                'details'                       => PaymentGatewayConst::PAYSTACK." payment successful",
                'status'                        => $status,
                'attribute'                     => PaymentGatewayConst::SEND,
                'callback_ref'                  => $output['callback_ref'] ?? null,
                'created_at'                    => now(),
            ]);
            if($status == PaymentGatewayConst::STATUSSUCCESS){
                $this->updateWalletBalanceBkash($output);
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }

    public function updateWalletBalanceBkash($output) {
        $update_amount = $output['wallet']->balance + $output['amount']->requested_amount;
        $output['wallet']->update([
            'balance'   => $update_amount,
        ]);
    }

    public function insertChargesBkash($output,$id) {
        if($this->predefined_user) {
            $user = $this->predefined_user;
        }elseif(Auth::guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
        }
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $output['amount']->percent_charge,
                'fixed_charge'      => $output['amount']->fixed_charge,
                'total_charge'      => $output['amount']->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Add Money"),
                'message'       => __("Your Wallet")." (".$output['wallet']->currency->code.")  ".__("balance  has been added")." ".$output['amount']->requested_amount.' '. $output['wallet']->currency->code,
                'time'          => Carbon::now()->diffForHumans(),
                'image'         =>  get_image($user->image,'user-profile')
            ];

            UserNotification::create([
                'type'      => NotificationConst::BALANCE_ADDED,
                'user_id'  =>  $user->id,
                'message'   => $notification_content,
            ]);

            //Push Notifications
            try{
                (new PushNotificationHelper())->prepare([$user->id],[
                    'title' => $notification_content['title'],
                    'desc'  => $notification_content['message'],
                    'user_type' => 'user',
                ])->send();
            }catch(Exception $e) {}
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }

    public function insertDeviceBkash($output,$id) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();

        // $mac = exec('getmac');
        // $mac = explode(" ",$mac);
        // $mac = array_shift($mac);
        $mac = "";

        DB::beginTransaction();
        try{
            DB::table("transaction_devices")->insert([
                'transaction_id'=> $id,
                'ip'            => $client_ip,
                'mac'           => $mac,
                'city'          => $location['city'] ?? "",
                'country'       => $location['country'] ?? "",
                'longitude'     => $location['lon'] ?? "",
                'latitude'      => $location['lat'] ?? "",
                'timezone'      => $location['timezone'] ?? "",
                'browser'       => $agent->browser() ?? "",
                'os'            => $agent->platform() ?? "",
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }

    public function removeTempDataBkash($output) {
        TemporaryData::where("identifier",$output['tempData']['identifier'])->delete();
    }
    public function isBkash($gateway)
    {
        $search_keyword = ['bkash', 'bKash', 'bkash payment'];
        $gateway_name = $gateway->name;

        $search_text = Str::lower($gateway_name);
        $search_text = preg_replace("/[^A-Za-z0-9]/", "", $search_text);
        foreach ($search_keyword as $keyword) {
            $keyword = Str::lower($keyword);
            $keyword = preg_replace("/[^A-Za-z0-9]/", "", $keyword);
            if ($keyword == $search_text) {
                return true;
                break;
            }
        }
        return false;
    }
    //for api
    public function bkashInitApi($output = null) {
        if(!$output) $output = $this->output;
        $credentials = $this->getBkashCredentials($output);
        $amount = $output['amount']->total_amount ? number_format($output['amount']->total_amount,2,'.','') : 0;
        $currency = $output['currency']['currency_code']??"USD";


        $grant_token                = $this->createToken($credentials);

        if($grant_token['status'] == false){
            throw new Exception($grant_token['message'] ?? __("Something went wrong! Please try again."));
        }

        $base_url                   = $credentials->base_url . '/tokenized/checkout/create';
        $client                     = new \GuzzleHttp\Client();

        $temp_record_token = generate_unique_string('temporary_datas', 'identifier', 60);
        $this->setUrlParams("token=" . $temp_record_token); // set Parameter to URL for identifying when return success/cancel
        $redirection = $this->getRedirection();
        $url_parameter = $this->getUrlParams();


        $body_form                  = [
            'mode'                  => '0011',
            'payerReference'        => $temp_record_token,
            'callbackURL'           => $this->setGatewayRoute($redirection['return_url'], PaymentGatewayConst::BKASH, $url_parameter),
            "amount"                => $amount,
            "currency"              => $currency,
            "intent"                => "sale",
            "merchantInvoiceNumber" => generate_random_string_number(10)
        ];
        try{
            $response_data = $client->request('POST', $base_url, [
                'body' => json_encode($body_form),
                'headers' => [
                    'Authorization' => $grant_token['id_token'],
                    'X-APP-Key' => $credentials->app_key,
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);

            $response   = $response_data->getBody();
            $result     = json_decode($response,true);
            if(isset($result['statusCode'])){
                if($result['statusCode'] == 0000){
                    $this->bkashJunkInsert($result,$temp_record_token);
                    $redirect_url   = $result['bkashURL'];
                    $data['link']   = $redirect_url;
                    $data['trx']    =  $temp_record_token;
                    return $data;
                }else{
                    throw new Exception($result['statusMessage'] ?? __("Something went wrong! Please try again."));
                }
            }

        }catch (RequestException2 $e) {
            throw new Exception(__("Something went wrong! Please try again."));
        }

    }


}
