<?php

namespace App\Http\Controllers\Api\Agent;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\PaymentGateway;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
use App\Models\TemporaryData;
use App\Models\Transaction;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\Agent\Withdraw\Api\WithdrawMail;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WithdrawController extends Controller
{
    use ControlDynamicInputFields;

    protected $basic_settings;
    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }

    public function withdrawInfo(){
        $transactions = Transaction::agentAuth()->moneyOut()->latest()->take(5)->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
            return[
                'id'                        => $item->id,
                'trx'                       => $item->trx_id,
                'gateway_name'              => $item->currency->gateway->name,
                'gateway_currency_name'     => @$item->currency->name,
                'transaction_type'          => "WITHDRAW",
                'request_amount'            => get_amount($item->request_amount,withdrawCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                'payable'                   => get_amount($item->details->charges->payable??$item->request_amount,withdrawCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                'will_get'                  => isCrypto($item->payable,withdrawCurrency($item)['gateway_currency'],$item->currency->gateway->crypto),
                'exchange_rate'             => '1 ' .withdrawCurrency($item)['wallet_currency'].' = '.isCrypto($item->details->charges->exchange_rate??$item->currency->rate??1,$item->currency->currency_code??get_default_currency_code(),$item->currency->gateway->crypto),
                'total_charge'              => get_amount($item->charge->total_charge??0,withdrawCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                'current_balance'           => get_amount($item->available_balance,withdrawCurrency($item)['wallet_currency']??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                'status'                    => $item->stringStatus->value,
                'date_time'                 => $item->created_at,
                'status_info'               => (object)$statusInfo,
                'rejection_reason'          => $item->reject_reason??"",

            ];
        });
        $gateways = PaymentGateway::where('status', 1)->where('slug', PaymentGatewayConst::money_out_slug())->get()->map(function($gateway){
                $currencies = PaymentGatewayCurrency::where('payment_gateway_id',$gateway->id)->get()->map(function($data){
                $precision = get_precision($data->gateway);
                return[
                    'id'                    => $data->id,
                    'payment_gateway_id'    => $data->payment_gateway_id,
                    'crypto'                => $data->gateway->crypto,
                    'type'                  => $data->gateway->type,
                    'name'                  => $data->name,
                    'alias'                 => $data->alias,
                    'currency_code'         => $data->currency_code,
                    'currency_symbol'       => $data->currency_symbol,
                    'image'                 => $data->image,
                    'min_limit'             => get_amount($data->min_limit,null,$precision),
                    'max_limit'             => get_amount($data->max_limit,null,$precision),
                    'percent_charge'        => get_amount($data->percent_charge,null,$precision),
                    'fixed_charge'          => get_amount($data->fixed_charge,null,$precision),
                    'daily_limit'           => get_amount($data->daily_limit,null,$precision),
                    'monthly_limit'         => get_amount($data->monthly_limit,null,$precision),
                    'rate'                  => get_amount($data->rate,null,$precision),
                    'created_at'            => $data->created_at,
                    'updated_at'            => $data->updated_at,
                ];

                });
                return[
                    'id'                    => $gateway->id,
                    'name'                  => $gateway->name,
                    'image'                 => $gateway->image,
                    'slug'                  => $gateway->slug,
                    'code'                  => $gateway->code,
                    'type'                  => $gateway->type,
                    'alias'                 => $gateway->alias,
                    'crypto'                => $gateway->crypto,
                    'supported_currencies'  => $gateway->supported_currencies,
                    'input_fields'          => $gateway->input_fields??null,
                    'status'                => $gateway->status,
                    'currencies'            => $currencies

                ];
        });
        $get_remaining_fields = [
            'transaction_type'  =>  PaymentGatewayConst::TYPEMONEYOUT,
            'attribute'         =>  PaymentGatewayConst::SEND,
        ];
        $data =[
            'base_curr'             => get_default_currency_code(),
            'base_curr_rate'        => get_amount(get_default_currency_rate(),null,get_wallet_precision()),
            'get_remaining_fields'  => (object) $get_remaining_fields,
            'default_image'         => files_asset_path_basename("default"),
            "image_path"            => files_asset_path_basename("payment-gateways"),
            'gateways'              => $gateways,
            'transactions'          => $transactions,
        ];
        $message =  ['success'=>[__('Withdraw Information!')]];
        return Helpers::success($data,$message);

    }
    public function withdrawInsert(Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|gt:0',
            'gateway' => 'required',
            'currency'     => "required|string|exists:currencies,code",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $amount = $request->amount;
        $agent = authGuardApi()['user'];
        $agentWallet = AgentWallet::auth()->active()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['currency'])->active();
        })->active()->first();
        if(!$agentWallet){
            $error = ['error'=>[__('Agent wallet not found!')]];
            return Helpers::error($error);
        }
        $gate = PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
            $gateway->where('slug', PaymentGatewayConst::money_out_slug());
            $gateway->where('status', 1);
        })->where('alias',$request->gateway)->first();
        if (!$gate) {
            $error = ['error'=>[__("Gateway is not available right now! Please contact with system administration")]];
            return Helpers::error($error);
        }
        $precision = get_precision($gate->gateway);
        $charges = $this->withdrawCharges($validated['amount'],$agentWallet,$gate,$precision);


        $min_amount = get_amount($gate->min_limit / $charges['exchange_rate'],null,$charges['wallet_precision']);
        $max_amount = get_amount($gate->max_limit / $charges['exchange_rate'],null,$charges['wallet_precision']);

        if($charges['requested_amount'] < $min_amount || $charges['requested_amount'] > $max_amount) {
           $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }
        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('agent_id',$agentWallet->agent->id,PaymentGatewayConst::TYPEMONEYOUT,$agentWallet->currency,$validated['amount'], $gate,PaymentGatewayConst::SEND);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
            $error = ['error'=>[__($errorData['message'] ?? __("Something went wrong! Please try again."))]];
            return Helpers::error($error);
        }
        if($charges['payable'] > $agentWallet->balance) {
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
        }

        $insertData['agent_id']             = $agent->id;
        $insertData['gateway_name']         = $gate->gateway->name;
        $insertData['gateway_type']         = $gate->gateway->type;
        $insertData['wallet_id']            = $agentWallet->id;
        $insertData['trx_id']               = 'WM'.getTrxNum();
        $insertData['amount']               = $amount;
        $insertData['gateway_id']           = $gate->gateway->id;
        $insertData['gateway_currency_id']  = $gate->id;
        $insertData['gateway_currency']     = strtoupper($gate->currency_code);
        $insertData['charges']              = $charges;

        $identifier = generate_unique_string("transactions","trx_id",16);
        $inserted = TemporaryData::create([
            'type'          => PaymentGatewayConst::TYPEMONEYOUT,
            'identifier'    => $identifier,
            'data'          => $insertData,
        ]);
        if( $inserted){
            $payment_gateway = PaymentGateway::where('id',$gate->payment_gateway_id)->first();
            $payment_information =[
                'trx'                   => $identifier,
                'gateway_currency_name' => $gate->name,
                'request_amount'        => get_amount($request->amount,$insertData['charges']['wallet_cur_code'],$charges['wallet_precision']),
                'exchange_rate'         => "1".' '.$insertData['charges']['wallet_cur_code'].' = '.get_amount($insertData['charges']['exchange_rate'],$insertData['charges']['gateway_cur_code'],$precision),
                'conversion_amount'     => get_amount($insertData['charges']['conversion_amount'],$insertData['charges']['gateway_cur_code'],$precision),
                'total_charge'          => get_amount($insertData['charges']['total_charge'],$insertData['charges']['wallet_cur_code'],$charges['wallet_precision']),
                'will_get'              => get_amount($insertData['charges']['conversion_amount'],$insertData['charges']['gateway_cur_code'],$precision),
                'payable'               => get_amount($insertData['charges']['payable'],$insertData['charges']['wallet_cur_code'],$charges['wallet_precision']),

            ];
            if($gate->gateway->type == "AUTOMATIC"){
                if($gate->gateway->name == "Flutterwave"){
                    $input_fields = get_flutter_wave_dynamic_fields($insertData);
                    $url = route('api.withdraw.automatic.confirmed');
                    $data =[
                            'payment_information'       => $payment_information,
                            'gateway_type'              => $payment_gateway->type,
                            'gateway_currency_name'     => $gate->name,
                            'gateway_currency_code'     => $gate->currency_code,
                            'branch_available'          => branch_required_permission(getewayIso2($insertData['gateway_currency'])),
                            'alias'                     => $gate->alias,
                            'input_fields'              => $input_fields,
                            'url'                       => $url??'',
                            'method'                    => "post",
                    ];
                    $message =  ['success'=>[__("Withdraw Money Inserted Successfully")]];
                    return Helpers::success($data, $message);
                }
            }else{
                $url = route('api.withdraw.manual.confirmed');
                $data =[
                        'payment_information'   => $payment_information,
                        'gateway_type'          => $payment_gateway->type,
                        'gateway_currency_name' => $gate->name,
                        'alias'                 => $gate->alias,
                        'details'               => $payment_gateway->desc??null,
                        'input_fields'          => $payment_gateway->input_fields??null,
                        'url'                   => $url??'',
                        'method'                => "post",
                ];
                $message =  ['success'=>[__("Withdraw Money Inserted Successfully")]];
                return Helpers::success($data, $message);
            }


        }else{
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    //manual confirmed
    public function withdrawConfirmed(Request $request){
        $validator = Validator::make($request->all(), [
            'trx'  => "required",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $basic_setting = BasicSettings::first();
        $agent = authGuardApi()['user'];
        $track = TemporaryData::where('identifier',$request->trx)->where('type',PaymentGatewayConst::TYPEMONEYOUT)->first();
        if(!$track){
            $error = ['error'=>[__("Sorry, your payment information is invalid")]];
            return Helpers::error($error);
        }
        $withdrawData =  $track->data;
        $gateway = PaymentGateway::where('id', $withdrawData->gateway_id)->first();
        if($gateway->type != "MANUAL"){
            $error = ['error'=>["Invalid request, it is not manual gateway request"]];
            return Helpers::error($error);
        }
        $precision = get_precision($gateway);
        $payment_fields = $gateway->input_fields ?? [];
        $validation_rules = $this->generateValidationRules($payment_fields);
        $validator2 = Validator::make($request->all(), $validation_rules);
        if ($validator2->fails()) {
            $message =  ['error' => $validator2->errors()->all()];
            return Helpers::error($message);
        }
        $validated = $validator2->validate();
        $get_values = $this->placeValueWithFields($payment_fields, $validated);
            try{
                $get_values =[
                    'user_data' => $get_values,
                    'charges' => $withdrawData->charges,
                ];
                //send notifications
                $inserted_id = $this->insertRecordManual($withdrawData,$gateway,$get_values,$reference= null,PaymentGatewayConst::STATUSPENDING);
                $this->insertChargesManual($withdrawData,$inserted_id,$precision);
                $this->adminNotification($withdrawData,PaymentGatewayConst::STATUSPENDING,$precision);
                $this->insertDeviceManual($withdrawData,$inserted_id);

                try{
                    if( $basic_setting->agent_email_notification == true){
                        $agent->notify(new WithdrawMail($agent,$withdrawData,$precision));
                    }
                }catch(Exception $e){}
                if($basic_setting->agent_sms_notification == true){
                    try{
                        //sms notification
                        sendSms(auth()->user(),'WITHDRAW_REQUEST',[
                            'amount'        => get_amount($withdrawData->amount,$withdrawData->charges->wallet_cur_code,$withdrawData->charges->wallet_precision),
                            'method_name'   => $withdrawData->gateway_name,
                            'currency'      => $withdrawData->gateway_currency,
                            'will_get'      => get_amount($withdrawData->charges->will_get,$withdrawData->gateway_currency,$withdrawData->charges->gateway_precision),
                            'trx'           => $withdrawData->trx_id,
                            'time'          => now()->format('Y-m-d h:i:s A'),
                        ]);
                    }catch(Exception $e) {}
                }
                $track->delete();
                $message =  ['success'=>[__('Withdraw money request send to admin successful')]];
                return Helpers::onlysuccess($message);
            }catch(Exception $e) {
                  $error = ['error'=>[__("Something went wrong! Please try again.")]];
                return Helpers::error($error);
            }

    }
    //automatic confirmed
    public function confirmWithdrawAutomatic(Request $request){
        $validator = Validator::make($request->all(), [
            'trx'  => "required",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $track = TemporaryData::where('identifier',$request->trx)->where('type',PaymentGatewayConst::TYPEMONEYOUT)->first();
        if(!$track){
            $error = ['error'=>[__("Sorry, your payment information is invalid")]];
            return Helpers::error($error);
        }
        $gateway = PaymentGateway::where('id', $track->data->gateway_id)->first();
        if($gateway->type != "AUTOMATIC"){
            $error = ['error'=>[__("Invalid request, it is not automatic gateway request")]];
            return Helpers::error($error);
        }
        $precision = get_precision($gateway);
        //flutterwave automatic
         if($track->data->gateway_name == "Flutterwave"){
            $callback_url       = url('/').'/flutterwave/withdraw_webhooks';
            $reference          = generateTransactionReference();
            $get_validate_data  = get_flutter_wave_api_data($request->all(),$track->data,$callback_url,$reference);
            $validator          = Validator::make($request->all(),$get_validate_data['validate_data']);

            if($validator->fails()){
                $error =  ['error'=>$validator->errors()->all()];
                return Helpers::validation($error);
            }
            return $this->flutterwavePay($gateway,$request,$track,$callback_url,$reference,$precision);
         }else{
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
         }

    }
    public function insertRecordManual($withdrawData,$gateway,$get_values,$reference,$status) {

        $trx_id = $withdrawData->trx_id ??'MO'.getTrxNum();
        $authWallet = AgentWallet::where('id',$withdrawData->wallet_id)->where('agent_id',$withdrawData->agent_id)->first();

        if($withdrawData->gateway_type != "AUTOMATIC"){
            $afterCharge = ($authWallet->balance - ($withdrawData->charges->payable??$withdrawData->amount));
        }else{
            $afterCharge = $authWallet->balance;
        }

        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      =>authGuardApi()['user']->id,
                'agent_wallet_id'               => $withdrawData->wallet_id,
                'payment_gateway_currency_id'   => $withdrawData->gateway_currency_id,
                'type'                          => PaymentGatewayConst::TYPEMONEYOUT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $withdrawData->charges->requested_amount,
                'payable'                       => $withdrawData->charges->will_get,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEMONEYOUT," ")) . " by " .$gateway->name,
                'details'                       => json_encode($get_values),
                'status'                        => $status,
                'callback_ref'                  => $reference??null,
                'created_at'                    => now(),
            ]);
            if($withdrawData->gateway_type != "AUTOMATIC"){
                $this->updateWalletBalanceManual($authWallet,$afterCharge);
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
              $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateWalletBalanceManual($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertChargesManual($withdrawData,$id,$precision) {
        $agent = authGuardApi()['user'];
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $withdrawData->charges->percent_charge,
                'fixed_charge'      => $withdrawData->charges->fixed_charge,
                'total_charge'      => $withdrawData->charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Withdraw Money"),
                'message'       =>__("Your Withdraw Request Send To Admin")." " .get_amount($withdrawData->amount,$withdrawData->charges->wallet_cur_code,$precision)." ".__("Successful"),
                'image'         => get_image($agent->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MONEY_OUT,
                'agent_id'  =>  $agent->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            if( $this->basic_settings->agent_push_notification == true){
                try{
                    (new PushNotificationHelper())->prepareApi([$agent->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'agent',
                    ])->send();
                }catch(Exception $e) {}
            }

        }catch(Exception $e) {
            DB::rollBack();
              $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function insertChargesAutomatic($withdrawData,$id,$precision) {
        $agent = authGuardApi()['user'];
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $withdrawData->charges->percent_charge,
                'fixed_charge'      => $withdrawData->charges->fixed_charge,
                'total_charge'      => $withdrawData->charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Withdraw Money"),
               'message'       => __("Your Withdraw Request")." " .get_amount($withdrawData->amount,$withdrawData->charges->wallet_cur_code,$precision)." ".__("Successful"),
                'image'         => get_image($agent->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MONEY_OUT,
                'agent_id'  =>  $agent->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            if( $this->basic_settings->agent_push_notification == true){
                try{
                    (new PushNotificationHelper())->prepareApi([$agent->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'agent',
                    ])->send();
                }catch(Exception $e) {}
            }
        }catch(Exception $e) {
            DB::rollBack();
              $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function insertDeviceManual($output,$id) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();
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
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    //fluttrwave
    public function flutterwavePay($gateway,$request,$track,$callback_url,$reference,$precision){
        $withdrawdata =  $track->data;
        $basic_setting = BasicSettings::first();
        $credentials = $gateway->credentials;
        $secret_key = getPaymentCredentials($credentials,'Secret key');
        $base_url = getPaymentCredentials($credentials,'Base Url');
        $callback_url = $callback_url;
        $get_api_data = get_flutter_wave_api_data($request->all(),$withdrawdata,$callback_url,$reference);

        $ch = curl_init();
        $url =  $base_url.'/transfers';

        $headers = [
            'Authorization: Bearer '.$secret_key,
            'Content-Type: application/json'
        ];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($get_api_data['api_send_data']));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return back()->with(['error' => [curl_error($ch)]]);
        } else {
            $result = json_decode($response,true);
            if($result['status'] && $result['status'] == 'success'){
                $get_values =[
                    'user_data' => null,
                    'charges' => $withdrawdata->charges,
                ];
                try{
                    $user = authGuardApi()['user'];
                    $inserted_id = $this->insertRecordManual($withdrawdata,$gateway,$get_values,$reference,PaymentGatewayConst::STATUSWAITING);
                    $this->insertChargesAutomatic($withdrawdata,$inserted_id,$precision);
                    $this->adminNotification($withdrawdata,PaymentGatewayConst::STATUSSUCCESS,$precision);
                    $this->insertDeviceManual($withdrawdata,$inserted_id);

                    //send notifications
                    try{
                        if( $basic_setting->agent_email_notification == true){
                            $user->notify(new WithdrawMail($user,$withdrawdata,$precision));
                        }
                    }catch(Exception $e){}
                    if($basic_setting->agent_sms_notification == true){
                        try{
                            //sms notification
                            sendSms(auth()->user(),'WITHDRAW_REQUEST',[
                                'amount'        => get_amount($withdrawdata->amount,$withdrawdata->charges->wallet_cur_code,$withdrawdata->charges->wallet_precision),
                                'method_name'   => $withdrawdata->gateway_name,
                                'currency'      => $withdrawdata->gateway_currency,
                                'will_get'      => get_amount($withdrawdata->charges->will_get,$withdrawdata->gateway_currency,$withdrawdata->charges->gateway_precision),
                                'trx'           => $withdrawdata->trx_id,
                                'time'          => now()->format('Y-m-d h:i:s A'),
                            ]);
                        }catch(Exception $e) {}
                    }

                    $track->delete();
                    $message =  ['success'=>[__('Withdraw money request send successful')]];
                    return Helpers::onlysuccess($message);
                }catch(Exception $e) {
                      $error = ['error'=>[__("Something went wrong! Please try again.")]];
                    return Helpers::error($error);
                }

            }else if($result['status'] && $result['status'] == 'error'){
                if(isset($result['data'])){
                    $errors = $result['message'].",".$result['data']['complete_message']??"";
                }else{
                    $errors = $result['message'];
                }
                $error = ['error'=>[$errors]];
                return Helpers::error($error);
            }else{
                $error = ['error'=>[$result['message']]];
                return Helpers::error($error);
            }
        }

        curl_close($ch);

    }
    //get flutterwave banks
    public function getBanks(){
        $validator = Validator::make(request()->all(), [
            'trx'  => "required",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $track = TemporaryData::where('identifier',request()->trx)->where('type',PaymentGatewayConst::TYPEMONEYOUT)->first();
        if(!$track){
            $error = ['error'=>[__("Sorry, your payment information is invalid")]];
            return Helpers::error($error);
        }
        if($track['data']->gateway_name != "Flutterwave"){
            $error = ['error'=>[__("Sorry, This Payment Request Is Not For FlutterWave")]];
            return Helpers::error($error);
        }
        $countries = get_all_countries();
        $currency = $track['data']->gateway_currency;
        $country = Collection::make($countries)->first(function ($item) use ($currency) {
            return $item->currency_code === $currency;
        });

        $allBanks = getFlutterwaveBanks($country->iso2);
        $data =[
            'bank_info' => array_values($allBanks)??[]
        ];
        $message =  ['success'=>[__("All Bank Fetch Successfully")]];
        return Helpers::success($data, $message);

    }
    //get bank branches
    public function getFlutterWaveBankBranches(){
        $validator = Validator::make(request()->all(), [
            'trx'       => "required",
            'bank_id'   => "required",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $track = TemporaryData::where('identifier',request()->trx)->where('type',PaymentGatewayConst::TYPEMONEYOUT)->first();
        if(!$track){
            $error = ['error'=>[__("Sorry, your payment information is invalid")]];
            return Helpers::error($error);
        }

        if($track['data']->gateway_name != "Flutterwave"){
            $error = ['error'=>[__("Sorry, This Payment Request Is Not For FlutterWave")]];
            return Helpers::error($error);
        }
        $countries = get_all_countries();
        $currency = $track['data']->gateway_currency;
        $country = Collection::make($countries)->first(function ($item) use ($currency) {
            return $item->currency_code === $currency;
        });

        $bank_branches = branch_required_countries($country->iso2,request()->bank_id);

        $data =[
            'bank_branches' =>$bank_branches['branches']??[]
        ];
        $message =  ['success'=>[__("Bank branches fetched successfully")]];
        return Helpers::success($data, $message);

    }
    //admin notification global(Agent & User)
    public function adminNotification($data,$status,$precision){
        $user = auth()->guard(authGuardApi()['guard'])->user();
        $exchange_rate = " 1 ". $data->charges->wallet_cur_code.' = '. get_amount($data->charges->exchange_rate,$data->charges->gateway_cur_code,$precision);
        if($status == PaymentGatewayConst::STATUSSUCCESS){
            $status ="success";
        }elseif($status == PaymentGatewayConst::STATUSPENDING){
            $status ="Pending";
        }elseif($status == PaymentGatewayConst::STATUSHOLD){
            $status ="Hold";
        }elseif($status == PaymentGatewayConst::STATUSWAITING){
            $status ="Waiting";
        }elseif($status == PaymentGatewayConst::STATUSPROCESSING){
            $status ="Processing";
        }elseif($status == PaymentGatewayConst::STATUSFAILD){
            $status ="Failed";
        }

        $notification_content = [
            //email notification
            'subject' =>__("Withdraw Money")." (".authGuardApi()['type'].")",
            'greeting' =>__("Withdraw Money Via")." ".$data->gateway_name.' ('.$data->gateway_type.' )',
            'email_content' =>__("web_trx_id")." : ".$data->trx_id."<br>".__("request Amount")." : ".get_amount($data->amount,$data->charges->wallet_cur_code,$precision)."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ". get_amount($data->charges->total_charge,$data->charges->wallet_cur_code,$precision)."<br>".__("Total Payable Amount")." : ".get_amount($data->charges->payable,$data->charges->wallet_cur_code,$precision)."<br>".__("Will Get")." : ".get_amount($data->charges->will_get,$data->charges->gateway_cur_code,$precision)."<br>".__("Status")." : ".__($status),
            //push notification
            'push_title' =>  __("Withdraw Money")." (".authGuardApi()['type'].")",
            'push_content' => __('web_trx_id')." ".$data->trx_id." ". __("Withdraw Money").' '.get_amount($data->amount,$data->charges->wallet_cur_code,$precision).' '.__('By').' '.$data->gateway_name.' ('.$user->username.')',

            //admin db notification
            'notification_type' =>  NotificationConst::MONEY_OUT,
            'trx_id' => $data->trx_id,
            'admin_db_title' =>  "Withdraw Money"." (".authGuardApi()['type'].")",
            'admin_db_message' =>  "Withdraw Money".' '.get_amount($data->amount,$data->charges->wallet_cur_code,$precision).' '.'By'.' '.$data->gateway_name.' ('.$user->username.')'
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.money.out.index','admin.money.out.pending','admin.money.out.complete','admin.money.out.canceled','admin.money.out.details','admin.money.out.approved','admin.money.out.rejected','admin.money.out.export.data'])
                                    ->mail(ActivityNotification::class, [
                                        'subject'   => $notification_content['subject'],
                                        'greeting'  => $notification_content['greeting'],
                                        'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                        'user_type' => "admin",
                                        'title' => $notification_content['push_title'],
                                        'desc'  => $notification_content['push_content'],
                                    ])
                                    ->adminDbContent([
                                        'type' => $notification_content['notification_type'],
                                        'title' => $notification_content['admin_db_title'],
                                        'message'  => $notification_content['admin_db_message'],
                                    ])
                                    ->send();


        }catch(Exception $e) {}

    }
    public function withdrawCharges($sender_amount,$userWallet,$gate,$precision) {

        $wallet_precision   = get_wallet_precision( $userWallet->currency);
        $gateway_rate       = get_amount($gate->rate,null,$precision);
        $wallet_rate        = get_amount($userWallet->currency->rate,null,$wallet_precision);
        $exchange_rate      = get_amount($gateway_rate / $wallet_rate,null,$precision);

        $data['exchange_rate']          = get_amount($exchange_rate,null,$precision);
        $data['requested_amount']       = get_amount($sender_amount,null,$wallet_precision);
        $data['gateway_cur_code']       = $gate->currency_code;
        $data['gateway_cur_rate']       = get_amount($gate->rate,null,$precision);
        $data['wallet_cur_code']        = $userWallet->currency->code;
        $data['wallet_cur_rate']        = get_amount($userWallet->currency->rate,null,$wallet_precision);
        $data['will_get']               = get_amount($sender_amount * $exchange_rate,null,$precision);
        $data['conversion_amount']      = get_amount($sender_amount * $exchange_rate,null,$precision);
        $data['percent_charge']         = get_amount(($sender_amount / 100) * $gate->percent_charge,null,$wallet_precision) ?? 0;
        $data['fixed_charge']           = get_amount($gate->fixed_charge/$exchange_rate,null,$wallet_precision) ?? 0;
        $data['total_charge']           = get_amount($data['percent_charge'] + $data['fixed_charge'],null,$wallet_precision);
        $data['sender_wallet_balance']  = get_amount($userWallet->balance,null,$precision);
        $data['payable']                = get_amount($sender_amount + $data['total_charge'],null,$wallet_precision);
        $data['default_currency']       = get_default_currency_code();
        $data['gateway_precision']      = $precision;
        $data['wallet_precision']       = $wallet_precision;

        return $data;
    }
}
