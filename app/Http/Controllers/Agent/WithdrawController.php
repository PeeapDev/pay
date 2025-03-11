<?php

namespace App\Http\Controllers\Agent;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use Illuminate\Http\Request;
use App\Models\Admin\Currency;
use App\Models\Admin\PaymentGateway;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use App\Traits\ControlDynamicInputFields;
use Exception;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use App\Models\Admin\BasicSettings;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\Agent\Withdraw\WithdrawMail;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WithdrawController extends Controller
{
    use ControlDynamicInputFields;

    protected $basic_settings;
    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }

    public function index()
    {
        $page_title = "Withdraw Money";
        $agent_wallets = AgentWallet::auth()->get();
        $wallet_currencies = Currency::whereIn('id',$agent_wallets->pluck('currency_id')->toArray())->get();
        $payment_gateways = PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
            $gateway->where('slug', PaymentGatewayConst::money_out_slug());
            $gateway->where('status', 1);
        })->get();
        $transactions = Transaction::agentAuth()->moneyOut()->orderByDesc("id")->latest()->take(10)->get();
        return view('agent.sections.money-out.index',compact('page_title','payment_gateways','transactions','wallet_currencies'));
    }
    public function paymentInsert(Request $request){
            $validated = Validator::make($request->all(),[
                'amount'            => 'required|numeric|gt:0',
                'gateway'           => 'required',
                'currency'     => "required|string|exists:currencies,code",
            ])->validate();

            $amount = $validated['amount'];
            $user = authGuardApi()['user'];

            $userWallet = AgentWallet::auth()->active()->whereHas("currency",function($q) use ($validated) {
                $q->where("code",$validated['currency'])->active();
            })->active()->first();
            if(!$userWallet){
                return back()->with(['error' => ['Agent wallet not found']]);
            }
            $gate =PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
                $gateway->where('slug', PaymentGatewayConst::money_out_slug());
                $gateway->where('status', 1);
            })->where('alias',$request->gateway)->first();

            if (!$gate) {
                return back()->with(['error' => ['Gateway is not available right now! Please contact with system administration']]);
            }
            $precision = get_precision($gate->gateway);
            $amount = $validated['amount'];
            $charges = $this->withdrawCharges($validated['amount'],$userWallet,$gate,$precision);

            $min_amount = get_amount($gate->min_limit / $charges['exchange_rate'],null,$charges['wallet_precision']);
            $max_amount = get_amount($gate->max_limit / $charges['exchange_rate'],null,$charges['wallet_precision']);

            if($charges['requested_amount'] < $min_amount || $charges['requested_amount'] > $max_amount) {
                return back()->with(['error' => [__("Please follow the transaction limit")]]);
            }

            //daily and monthly
            try{
                (new TransactionLimit())->trxLimit('agent_id',$userWallet->agent->id,PaymentGatewayConst::TYPEMONEYOUT,$userWallet->currency,$validated['amount'],$gate,PaymentGatewayConst::SEND);
            }catch(Exception $e){
                $errorData = json_decode($e->getMessage(), true);
                return back()->with(['error' => [__($errorData['message'] ?? __("Something went wrong! Please try again."))]]);
            }

            if($charges['payable'] > $userWallet->balance) {
                return back()->with(['error' => [__("Sorry, insufficient balance")]]);
            }

            $data['agent_id']= $user->id;
            $data['gateway_name']= $gate->gateway->name;
            $data['gateway_type']= $gate->gateway->type;
            $data['wallet_id']= $userWallet->id;
            $data['trx_id']= 'WM'.getTrxNum();
            $data['amount'] =  $amount;
            $data['gateway_id'] = $gate->gateway->id;
            $data['gateway_currency_id'] = $gate->id;
            $data['gateway_currency'] = strtoupper($gate->currency_code);
            $data['charges'] = $charges;

            session()->put('moneyoutData', $data);
            return redirect()->route('agent.money.out.preview');
    }
    public function preview(){
        $moneyOutData = (object)session()->get('moneyoutData');
        $moneyOutDataExist = session()->get('moneyoutData');
        if($moneyOutDataExist  == null){
            return redirect()->route('agent.money.out.index');
        }
        $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();
        if($gateway->type == "AUTOMATIC"){
            $page_title = __("Withdraw Via").' '.$gateway->name;
            if(strtolower($gateway->name) == "flutterwave"){
                $credentials = $gateway->credentials;
                $data = null;
                foreach ($credentials as $object) {
                    $object = (object)$object;
                    if ($object->label === "Secret key") {
                        $data = $object;
                        break;
                    }
                }
                $countries = get_all_countries();
                $currency =  $moneyOutData->gateway_currency;
                $country = Collection::make($countries)->first(function ($item) use ($currency) {
                    if($currency == "GBP"){
                        return $item->currency_code === $currency && $item->name === "United Kingdom";
                    }else{
                        return $item->currency_code === $currency;
                    }
                });
                $allBanks = getFlutterwaveBanks($country->iso2);
                return view('agent.sections.money-out.automatic.'.strtolower($gateway->name),compact('page_title','gateway','moneyOutData','allBanks','country','countries'));
            }else{
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
        }else{
            $page_title = __("Withdraw Via").' '.$gateway->name;
            return view('agent.sections.money-out.preview',compact('page_title','gateway','moneyOutData'));
        }

    }
    public function confirmMoneyOut(Request $request){
        $basic_setting = BasicSettings::first();
        $moneyOutData = (object)session()->get('moneyoutData');
        $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();
        $precision = get_precision($gateway);
        $payment_fields = $gateway->input_fields ?? [];
        $validation_rules = $this->generateValidationRules($payment_fields);
        $payment_field_validate = Validator::make($request->all(),$validation_rules)->validate();
        $get_values = $this->placeValueWithFields($payment_fields,$payment_field_validate);
            try{
                //send notifications
                $get_values =[
                    'user_data' => $get_values,
                    'charges' => $moneyOutData->charges,

                ];
                $user = auth()->user();
                $inserted_id = $this->insertRecordManual($moneyOutData,$gateway,$get_values,$reference= null,PaymentGatewayConst::STATUSPENDING);
                $this->insertChargesManual($moneyOutData,$inserted_id,$precision);
                $this->adminNotification($moneyOutData,PaymentGatewayConst::STATUSPENDING,$precision);
                $this->insertDeviceManual($moneyOutData,$inserted_id);
                try{
                    if( $basic_setting->agent_email_notification == true){
                        $user->notify(new WithdrawMail($user,$moneyOutData,$precision));
                    }
                }catch(Exception $e){}
                if($basic_setting->agent_sms_notification == true){
                    try{
                        //sms notification
                        sendSms(auth()->user(),'WITHDRAW_REQUEST',[
                            'amount'        => get_amount($moneyOutData->amount,$moneyOutData->charges['wallet_cur_code'],$moneyOutData->charges['wallet_precision']),
                            'method_name'   => $moneyOutData->gateway_name,
                            'currency'      => $moneyOutData->gateway_currency,
                            'will_get'      => get_amount($moneyOutData->charges['will_get'],$moneyOutData->gateway_currency,$moneyOutData->charges['gateway_precision']),
                            'trx'           => $moneyOutData->trx_id,
                            'time'          => now()->format('Y-m-d h:i:s A'),
                        ]);
                    }catch(Exception $e) {}
                }

                session()->forget('moneyoutData');

                return redirect()->route("agent.money.out.index")->with(['success' => [__('Withdraw Money Request Send To Admin Successful')]]);
            }catch(Exception $e) {
                return back()->with(['error' => [$e->getMessage()]]);
            }

    }
    public function confirmMoneyOutAutomatic(Request $request){
        $basic_setting = BasicSettings::first();
        $moneyOutData = (object)session()->get('moneyoutData');
        $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();
        $precision = get_precision($gateway);

        if($request->gateway_name == 'flutterwave'){
            $callback_url = url('/').'/flutterwave/withdraw_webhooks';
            $reference =  generateTransactionReference();
            $get_data = get_flutter_wave_api_data($request->all(),$moneyOutData,$callback_url,$reference);
            $request->validate($get_data['validate_data']);


            $credentials = $gateway->credentials;
            $secret_key = getPaymentCredentials($credentials,'Secret key');
            $base_url = getPaymentCredentials($credentials,'Base Url');
            $ch = curl_init();
            $url =  $base_url.'/transfers';

            $headers = [
                'Authorization: Bearer '.$secret_key,
                'Content-Type: application/json'
            ];

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($get_data['api_send_data']));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $result = json_decode($response,true);
            if($result['status'] && $result['status'] == 'success'){
                try{
                    $get_values =[
                        'user_data' => $result['data'],
                        'charges' => $moneyOutData->charges,
                    ];
                    //send notifications
                    $user = auth()->user();
                    $inserted_id = $this->insertRecordManual($moneyOutData,$gateway,$get_values,$reference,PaymentGatewayConst::STATUSWAITING);
                    $this->insertChargesAutomatic($moneyOutData,$inserted_id,$precision);
                    $this->adminNotification($moneyOutData,PaymentGatewayConst::STATUSSUCCESS, $precision);
                    $this->insertDeviceManual($moneyOutData,$inserted_id);
                    session()->forget('moneyoutData');
                    try{
                        if( $basic_setting->email_notification == true){
                            $user->notify(new WithdrawMail($user,$moneyOutData,$precision));
                        }
                    }catch(Exception $e){}
                    if($basic_setting->agent_sms_notification == true){
                        try{
                            //sms notification
                            sendSms(auth()->user(),'WITHDRAW_REQUEST',[
                                'amount'        => get_amount($moneyOutData->amount,$moneyOutData->charges['wallet_cur_code'],$moneyOutData->charges['wallet_precision']),
                                'method_name'   => $moneyOutData->gateway_name,
                                'currency'      => $moneyOutData->gateway_currency,
                                'will_get'      => get_amount($moneyOutData->charges['will_get'],$moneyOutData->gateway_currency,$moneyOutData->charges['gateway_precision']),
                                'trx'           => $moneyOutData->trx_id,
                                'time'          => now()->format('Y-m-d h:i:s A'),
                            ]);
                        }catch(Exception $e) {}
                    }

                    return redirect()->route("agent.money.out.index")->with(['success' => [__('Withdraw money request send to admin successful')]]);
                }catch(Exception $e) {
                    return back()->with(['error' => [$e->getMessage()]]);
                }

            }else if($result['status'] && $result['status'] == 'error'){
                if(isset($result['data'])){
                    $errors = $result['message'].",".$result['data']['complete_message']??"";
                }else{
                    $errors = $result['message'];
                }
                return back()->with(['error' => [ $errors]]);
            }else{
                return back()->with(['error' => [$result['message']]]);
            }

            curl_close($ch);

        }else{
            return back()->with(['error' => [__("Invalid request,please try again later")]]);
        }

    }
   //check flutterwave banks
    public function checkBanks(Request $request){
        $bank_account = $request->account_number;
        $bank_code = $request->bank_code;
        $exist['data'] = (checkBankAccount($bank_account,$bank_code));
        return response( $exist);
    }
     //Get flutterwave banks branches
     public function getFlutterWaveBankBranches(Request $request){
        $iso2 = $request->iso2;
        $bank_id = $request->bank_id;
        $data = branch_required_countries($iso2,$bank_id);
        return response($data);
    }
    public function insertRecordManual($moneyOutData,$gateway,$get_values,$reference,$status) {

        $trx_id = $moneyOutData->trx_id ??'MO'.getTrxNum();
        $authWallet = AgentWallet::where('id',$moneyOutData->wallet_id)->where('agent_id',$moneyOutData->agent_id)->first();

        if($moneyOutData->gateway_type != "AUTOMATIC"){
            $afterCharge = ($authWallet->balance - ($moneyOutData->charges['payable']));
        }else{
            $afterCharge = $authWallet->balance;
        }
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      =>authGuardApi()['user']->id,
                'agent_wallet_id'               => $moneyOutData->wallet_id,
                'payment_gateway_currency_id'   => $moneyOutData->gateway_currency_id,
                'type'                          => PaymentGatewayConst::TYPEMONEYOUT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $moneyOutData->amount,
                'payable'                       => $moneyOutData->charges['will_get'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEMONEYOUT," ")) . " by " .$gateway->name,
                'details'                       => json_encode($get_values),
                'status'                        => $status,
                'callback_ref'                  => $reference??null,
                'created_at'                    => now(),
            ]);

            if($moneyOutData->gateway_type != "AUTOMATIC"){
                $this->updateWalletBalanceManual($authWallet,$afterCharge);
            }


            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return $id;
    }
    public function updateWalletBalanceManual($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertChargesManual($moneyOutData,$id,$precision) {

        if(Auth::guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
        }
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $moneyOutData->charges['percent_charge'],
                'fixed_charge'      => $moneyOutData->charges['fixed_charge'],
                'total_charge'      => $moneyOutData->charges['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Withdraw"),
                'message'       => __("Your Withdraw Request Send To Admin")." " .get_amount($moneyOutData->amount,$moneyOutData->charges['wallet_cur_code'],$precision)." ".__("Successful"),
                'image'         => get_image($user->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MONEY_OUT,
                'agent_id'  =>  $user->id,
                'message'   => $notification_content,
            ]);

            //Push Notifications
            if( $this->basic_settings->agent_push_notification == true){
                try{
                    (new PushNotificationHelper())->prepare([$user->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'agent',
                    ])->send();
                }catch(Exception $e) {}
            }
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function insertChargesAutomatic($moneyOutData,$id,$precision) {

        if(Auth::guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
        }
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $moneyOutData->charges['percent_charge'],
                'fixed_charge'      => $moneyOutData->charges['fixed_charge'],
                'total_charge'      => $moneyOutData->charges['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__( "Withdraw"),
                'message'       => __("Your Withdraw Request")." " .get_amount($moneyOutData->amount,$moneyOutData->charges['wallet_cur_code'],$precision)." ".__("Successful"),
                'image'         => get_image($user->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MONEY_OUT,
                'agent_id'  =>  $user->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            if( $this->basic_settings->agent_push_notification == true){
                try{
                    (new PushNotificationHelper())->prepare([$user->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'agent',
                    ])->send();
                }catch(Exception $e) {}
            }
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function insertDeviceManual($output,$id) {
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
            throw new Exception($e->getMessage());
        }
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

    //admin notification global(Agent & User)
    public function adminNotification($data,$status,$precision){
        $user = auth()->guard(userGuard()['guard'])->user();
        $exchange_rate = " 1 ". $data->charges['wallet_cur_code'].' = '. get_amount($data->charges['exchange_rate'],$data->charges['gateway_cur_code'],$precision);
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
            'subject' =>__("Withdraw Money")." (".userGuard()['type'].")",
            'greeting' =>__("Withdraw Money Via")." ".$data->gateway_name.' ('.$data->gateway_type.' )',
            'email_content' =>__("web_trx_id")." : ".$data->trx_id."<br>".__("request Amount")." : ".get_amount($data->amount,$data->charges['wallet_cur_code'],$precision)."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ". get_amount($data->charges['total_charge'],$data->charges['wallet_cur_code'],$precision)."<br>".__("Total Payable Amount")." : ".get_amount($data->charges['payable'],$data->charges['wallet_cur_code'],$precision)."<br>".__("Will Get")." : ".get_amount($data->charges['will_get'],$data->charges['gateway_cur_code'],$precision)."<br>".__("Status")." : ".__($status),
            //push notification
            'push_title' =>  __("Withdraw Money")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." ".$data->trx_id." ". __("Withdraw Money").' '.get_amount($data->amount,$data->charges['wallet_cur_code'],$precision).' '.__('By').' '.$data->gateway_name.' ('.$user->username.')',

            //admin db notification
            'notification_type' =>  NotificationConst::MONEY_OUT,
            'trx_id' => $data->trx_id,
            'admin_db_title' =>  "Withdraw Money"." (".userGuard()['type'].")",
            'admin_db_message' =>  "Withdraw Money".' '.get_amount($data->amount,$data->charges['wallet_cur_code'],$precision).' '.'By'.' '.$data->gateway_name.' ('.$user->username.')'
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
}
