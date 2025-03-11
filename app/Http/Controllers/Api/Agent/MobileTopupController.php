<?php

namespace App\Http\Controllers\Api\Agent;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\AirtimeHelper;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\TransactionSetting;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
use App\Models\TopupCategory;
use App\Models\Transaction;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\MobileTopup\TopupAutomaticMail;
use Illuminate\Support\Str;
use App\Notifications\User\MobileTopup\TopupMail;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use App\Models\Admin\Currency;

class MobileTopupController extends Controller
{
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function topUpInfo(){
        $topupCharge = TransactionSetting::where('slug','mobile_topup')->where('status',1)->get()->map(function($data){
            return[
                'id'                        => $data->id,
                'slug'                      => $data->slug,
                'title'                     => $data->title,
                'fixed_charge'              => get_amount($data->fixed_charge,null,get_wallet_precision()),
                'percent_charge'            => get_amount($data->percent_charge,null,get_wallet_precision()),
                'min_limit'                 => get_amount($data->min_limit,null,get_wallet_precision()),
                'max_limit'                 => get_amount($data->max_limit,null,get_wallet_precision()),
                'monthly_limit'             => get_amount($data->monthly_limit,null,get_wallet_precision()),
                'daily_limit'               => get_amount($data->daily_limit,null,get_wallet_precision()),
                'agent_fixed_commissions'   => get_amount($data->agent_fixed_commissions,null,get_wallet_precision()),
                'agent_percent_commissions' => get_amount($data->agent_percent_commissions,null,get_wallet_precision()),
                'agent_profit'              => $data->agent_profit,
            ];
        })->first();
        $topupType = TopupCategory::active()->orderByDesc('id')->get();
        $transactions = Transaction::agentAuth()->mobileTopup()->latest()->take(5)->get()->map(function($item){
            $statusInfo = [
                "success"       => 1,
                "pending"       => 2,
                "hold"          => 3,
                "rejected"      => 4,
                "waiting"       => 5,
                "failed"        => 6,
                "processing"    => 7,
                ];
                if(isset($item->details->topup_type) && $item->details->topup_type == "MANUAL"){
                    $exchange_rate = get_amount(1,$item->details->charges->destination_currency)." = ".get_amount($item->details->charges->exchange_rate,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency));
                    $will_get = get_amount($item->details->charges->sender_amount,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency));
                }elseif(isset($item->details->topup_type) && $item->details->topup_type == "AUTOMATIC"){
                    $exchange_rate = get_amount(1,$item->details->charges->sender_currency)." = ".get_amount($item->details->charges->exchange_rate,$item->details->charges->destination_currency,get_wallet_precision($item->creator_wallet->currency));
                    $will_get = get_amount($item->details->charges->conversion_amount,$item->details->charges->destination_currency,get_wallet_precision($item->creator_wallet->currency));
                }else{
                    $exchange_rate = get_amount(1,$item->details->charges->destination_currency)." = ".get_amount($item->details->charges->exchange_rate,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency));
                    $will_get = get_amount($item->details->charges->sender_amount,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency));
                }


            return[
                'id' => $item->id,
                'trx' => $item->trx_id,
                'transaction_type' => $item->type,
                'request_amount' => get_amount($item->request_amount,topUpCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                'exchange_rate'  => $exchange_rate,
                'payable' => get_amount($item->payable,topUpCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                'will_get' => $will_get,
                'topup_type' => $item->details->topup_type_name,
                'mobile_number' =>$item->details->mobile_number,
                'total_charge' => get_amount($item->charge->total_charge,topUpCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                'current_balance' => get_amount($item->available_balance,topUpCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                'status' => $item->stringStatus->value,
                'status_value' => $item->status,
                'date_time' => $item->created_at,
                'status_info' =>(object)$statusInfo,
                'rejection_reason' =>$item->reject_reason??"",

            ];
        });
        $all_countries = freedom_countries(GlobalConst::AGENT);
        $get_remaining_fields = [
            'transaction_type'  =>  PaymentGatewayConst::MOBILETOPUP,
            'attribute'         =>  PaymentGatewayConst::SEND,
        ];
        $data =[
            'base_curr'             => get_default_currency_code(),
            'base_curr_rate'        => get_amount(get_default_currency_rate(),null,get_wallet_precision()),
            'get_remaining_fields'  => (object) $get_remaining_fields,
            'topupCharge'           => (object)$topupCharge,
            'topupTypes'            => $topupType,
            'all_countries'         => $all_countries,
            'transactions'          => $transactions,
        ];
        $message =  ['success'=>['Mobile TopUp Information']];
        return Helpers::success($data,$message);
    }
    //Start Manual
    public function topUpConfirmed(Request $request){
        $validator = Validator::make(request()->all(), [
            'topup_type'    => 'required|exists:topup_categories,id',
            'currency'      => 'required|exists:currencies,code',
            'mobile_code'   => 'required',
            'mobile_number' => 'required|max:15',
            'amount'        => 'required|numeric|gt:0',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated =  $validator->validate();

        $basic_setting = BasicSettings::first();
        $user =  authGuardApi()['user'];
        $phone = remove_special_char($validated['mobile_code']).$validated['mobile_number'];

        $sender_wallet = AgentWallet::auth()->active()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['currency'])->active();
        })->active()->first();
        if(!$sender_wallet){
            $error = ['error'=>[__('Agent wallet not found!')]];
            return Helpers::error($error);
        }
        $topup_type = TopupCategory::where('id', $validated['topup_type'])->first();
        if(! $topup_type){
            $error = ['error'=>[__('Invalid type')]];
            return Helpers::error($error);
        }
        $topupCharge = TransactionSetting::where('slug','mobile_topup')->where('status',1)->first();
        $charges = $this->topupCharge($validated['amount'],$topupCharge,$sender_wallet);

        $sender_currency_rate = $sender_wallet->currency->rate;
        $min_amount = $topupCharge->min_limit * $sender_currency_rate;
        $max_amount = $topupCharge->max_limit * $sender_currency_rate;

        if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }
        try{
            (new TransactionLimit())->trxLimit('agent_id',$sender_wallet->agent->id,PaymentGatewayConst::MOBILETOPUP,$sender_wallet->currency,$validated['amount'],$topupCharge,PaymentGatewayConst::SEND);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
            $error = ['error'=>[__($errorData['message'] ?? __("Something went wrong! Please try again."))]];
            return Helpers::error($error);
        }
        if($charges['payable'] > $sender_wallet->balance) {
           $error = ['error'=>[__('Sorry, insufficient balance')]];
           return Helpers::error($error);
        }
        try{
            $trx_id = 'MP'.getTrxNum();
            $sender = $this->insertSender($trx_id,$sender_wallet,$charges,$topup_type,$phone);
            $this->insertSenderCharges($sender,$charges,$sender_wallet);
            if( $basic_setting->agent_email_notification == true){
                //send notifications
                $notifyData = [
                    'trx_id'  => $trx_id,
                    'topup_type'  => @$topup_type->name,
                    'mobile_number'  => $phone,
                    'request_amount'   => $charges['sender_amount'],
                    'charges'   => $charges['total_charge'],
                    'payable'  => $charges['payable'],
                   'current_balance'  => getAmount($sender_wallet->balance,$charges['precision_digit']),
                    'status'  => __("Pending"),
                ];
                try{
                    $user->notify(new TopupMail($user,(object)$notifyData,$charges));
                }catch(Exception $e){}
            }
            if($this->basic_settings->agent_sms_notification == true){
                try{
                    sendSms($user,'MOBILE_TOPUP',[
                        'amount'        => get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']),
                        'name'          => $topup_type->name??'',
                        'mobile_number' => $phone,
                        'charge'        => get_amount($charges['total_charge'],$charges['sender_currency']),
                        'payable'       => get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit']),
                        'trx'           => $trx_id,
                        'time'          => now()->format('Y-m-d h:i:s A')
                    ]);
                }catch(Exception $e) {}
            }
            //admin notification
            $this->adminNotificationManual($trx_id,$charges,$topup_type,$user,$phone);
            $message =  ['success'=>[__('Mobile topup request send to admin successful')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
           $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
    public function insertSender($trx_id,$sender_wallet, $charges, $topup_type,$mobile_number) {
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge = ($authWallet->balance -  $charges['payable']);
        $details =[
            'topup_type'        => PaymentGatewayConst::MANUAL,
            'topup_type_id'     => $topup_type->id??'',
            'topup_type_name'   => $topup_type->name??'',
            'mobile_number'     => $mobile_number,
            'topup_amount'      => $charges['sender_amount']??"",
            'charges'           => $charges,
            'api_response'      => [],
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $sender_wallet->agent->id,
                'agent_wallet_id'               => $sender_wallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::MOBILETOPUP,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_special_char(PaymentGatewayConst::MOBILETOPUP," ")) . "  Request To Admin",
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => PaymentGatewayConst::STATUSPENDING,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
           $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function insertSenderCharges($id,$charges,$sender_wallet){
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    =>  $id,
                'percent_charge'    =>  $charges['percent_charge'],
                'fixed_charge'      =>  $charges['fixed_charge'],
                'total_charge'      =>  $charges['total_charge'],
                'created_at'        =>  now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Mobile Topup"),
                'message'       => __('Mobile topup request send to admin')." " .$charges['sender_amount'].' '.$charges['sender_currency']." ".__("Successful"),
                'image'         => get_image($sender_wallet->agent->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MOBILE_TOPUP,
                'agent_id'  => $sender_wallet->agent->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            if( $this->basic_settings->agent_push_notification == true){
                try{
                    (new PushNotificationHelper())->prepareApi([$sender_wallet->agent->id],[
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
    public function topupCharge($sender_amount,$charges,$sender_wallet) {
        $sPrecision = get_wallet_precision($sender_wallet->currency);
        $exchange_rate = get_default_currency_rate() * $sender_wallet->currency->rate;

        $data['exchange_rate' ]                     = $exchange_rate;
        $data['sender_amount']                      = $sender_amount;
        $data['sender_currency']                    = $sender_wallet->currency->code;
        $data['sender_currency_rate']               = $sender_wallet->currency->rate;
        $data['destination_currency']               = get_default_currency_code();
        $data['destination_currency_rate']          = get_default_currency_rate();
        $data['conversion_amount']                  = $sender_amount * $exchange_rate;
        $data['percent_charge']                     = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']                       = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']                       = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']              = $sender_wallet->balance;
        $data['payable']                            = $sender_amount + $data['total_charge'];

        $data['agent_percent_commission']           = ($sender_amount / 100) * $charges->agent_percent_commissions ?? 0;
        $data['agent_fixed_commission']             = $sender_wallet->currency->rate * $charges->agent_fixed_commissions ?? 0;
        $data['agent_total_commission']             = $data['agent_percent_commission'] + $data['agent_fixed_commission'];
        $data['precision_digit']                    = $sPrecision;

        return $data;
    }
    //Start Automatic
    public function checkOperator(){
        $validator = Validator::make(request()->all(), [
            'mobile_code' => 'required',
            'mobile_number' => 'required',
            'country_code' => 'required|string',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $mobile_code = remove_special_char($validated['mobile_code']);
        $mobile = remove_special_char($validated['mobile_number']);
        $phone = $mobile_code.$mobile;
        $iso = $validated['country_code'] ;
        $operator = (new AirtimeHelper())->autoDetectOperator($phone,$iso);
        if($operator['status'] === false){
            $data = [
                'status' => false,
                'message' => $operator['message']??"",
                'data' => (object)[],
            ];
        }else{
            $operator['receiver_currency_rate'] = getAmount(receiver_currency($operator['destinationCurrencyCode'])['rate'],8);
            $operator['receiver_currency_code'] = receiver_currency($operator['destinationCurrencyCode'])['currency'];
            $data = [
                'status' => true,
                'message' => 'Successfully Get Operator',
                'data' => $operator,
            ];
        }
        $message =  ['success'=>[__('Mobile Topup')]];
        return Helpers::success($data,$message);
    }
    public function payAutomatic(Request $request){
        $validator = Validator::make(request()->all(), [
            'operator_id'   => 'required',
            'mobile_code'   => 'required',
            'mobile_number' => 'required|max:15',
            'country_code'  => 'required',
            'amount'        => 'required|numeric|gt:0',
            'currency'      => 'required|exists:currencies,code',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated =  $validator->validate();

        $user = authGuardApi()['user'];
        $sender_phone = $user->full_mobile??"";
        $sender_country_name = @$user->address->country;
        $foundItem = '';
        foreach (freedom_countries(GlobalConst::AGENT) ?? [] as $item) {
            if ($item->name === $sender_country_name) {
                $foundItem = $item;
            }
        }
        $sender_country_iso = $foundItem->iso2;

        $phone = remove_special_char($validated['mobile_code']).$validated['mobile_number'];
        $operator = (new AirtimeHelper())->autoDetectOperator($phone,$validated['country_code']);
        if($operator['status'] === false){
            $error = ['error'=>[__($operator['message']??"")]];
            return Helpers::error($error);
        }
        $sender_wallet = AgentWallet::auth()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['currency'])->active();
        })->active()->first();
        if(!$sender_wallet){
            $error = ['error'=>[__('Agent wallet not found!')]];
            return Helpers::error($error);
        }
        $topupCharge = TransactionSetting::where('slug','mobile_topup')->where('status',1)->first();
        $charges = $this->topupChargeAutomatic($validated['amount'],$topupCharge,$operator,$sender_wallet);

        if($operator['denominationType'] === "RANGE"){
            $min_amount = 0;
            $max_amount = 0;
            if($operator["supportsLocalAmounts"] == true && $operator["destinationCurrencyCode"] == $operator["senderCurrencyCode"] && $operator["localMinAmount"] == null && $operator["localMaxAmount"] == null){
                $min_amount = get_amount($operator['minAmount'] / $charges['exchange_rate'],null,$charges['precision_digit']);
                $max_amount = get_amount($operator['maxAmount'] / $charges['exchange_rate'],null,$charges['precision_digit']);
            }else if($operator["supportsLocalAmounts"] == true && $operator["localMinAmount"] != null && $operator["localMaxAmount"] != null){
                $min_amount = get_amount($operator["localMinAmount"] / $charges['exchange_rate'],null,$charges['precision_digit']);
                $max_amount = get_amount($operator["localMaxAmount"] / $charges['exchange_rate'],null,$charges['precision_digit']);
            }else{
                $fxRate = $operator['fx']['rate'] ?? 1;
                $min_amount = get_amount((($operator['minAmount'] * $fxRate) / $charges['exchange_rate']),null,$charges['precision_digit']);
                $max_amount = get_amount((($operator['maxAmount'] * $fxRate) / $charges['exchange_rate'] ),null,$charges['precision_digit']);
            }
            if($charges['sender_amount'] < ($min_amount) || $charges['sender_amount'] > ($max_amount)) {
                $error = ['error'=>[__("Please follow the transaction limit")]];
                return Helpers::error($error);
            }
        }
        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('agent_id',$sender_wallet->agent->id,PaymentGatewayConst::MOBILETOPUP,$sender_wallet->currency,$validated['amount'],$topupCharge,PaymentGatewayConst::SEND);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
            $error = ['error'=>[__($errorData['message'] ?? __("Something went wrong! Please try again."))]];
            return Helpers::error($error);
        }
        if($charges['payable'] > $sender_wallet->balance) {
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
        }
        //topup api
         $topUpData = [
            'operatorId'        => $operator['operatorId'],
            'amount'            => getAmount($charges['payable_to_provider'],2),
            'useLocalAmount'    => $operator['supportsLocalAmounts'],
            'customIdentifier'  => Str::uuid() . "|" . "AIRTIME",
            'recipientEmail'    => null,
            'recipientPhone'    => [
                'countryCode'   => $validated['country_code'],
                'number'        => $phone,
            ],
            'senderPhone'   => [
                'countryCode' => $sender_country_iso,
                'number'      => $sender_phone,
            ]

        ];
        $topUpData = (new AirtimeHelper())->makeTopUp($topUpData);
        if( isset($topUpData['status']) && $topUpData['status'] === false){
            $error = ['error'=>[__($topUpData['message'])]];
            return Helpers::error($error);
        }

        try{
            $trx_id = 'MP'.getTrxNum();
            $sender = $this->insertTransaction($trx_id,$sender_wallet,$charges,$operator,$phone,$topUpData);
            $this->insertAutomaticCharges($sender,$charges,$sender_wallet);
            if($this->basic_settings->agent_email_notification == true){
                //send notifications
                $notifyData = [
                    'trx_id'            => $trx_id,
                    'operator_name'     => $operator['name']??'',
                    'mobile_number'     => $phone,
                    'request_amount'    => get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']),
                    'exchange_rate'     => get_amount(1,$charges['sender_currency'])." = ".get_amount($charges['exchange_rate'],$charges['destination_currency'],$charges['precision_digit']),
                    'will_get'          => get_amount($charges['conversion_amount'],$charges['destination_currency'],$charges['precision_digit']),
                    'charges'           => get_amount($charges['total_charge'],$charges['sender_currency'],$charges['precision_digit']),
                    'payable'           => get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit']),
                    'current_balance'   => get_amount($sender_wallet->balance,$charges['sender_currency'],$charges['precision_digit']),
                    'status'            => __("Successful"),
                ];
                try{
                    $user->notify(new TopupAutomaticMail($user,(object)$notifyData));
                }catch(Exception $e){}
            }
            if($this->basic_settings->agent_sms_notification == true){
                try{
                    sendSms($user,'MOBILE_TOPUP',[
                        'amount'        => get_amount($charges['conversion_amount'],$charges['destination_currency'],$charges['precision_digit']),
                        'name'          => $operator['name']??'',
                        'mobile_number' => $phone,
                        'charge'        => get_amount($charges['total_charge'],$charges['sender_currency'],$charges['precision_digit']),
                        'payable'       => get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit']),
                        'trx'           => $trx_id,
                        'time'          => now()->format('Y-m-d h:i:s A')
                    ]);
                }catch(Exception $e) {}
            }
             //admin notification
             $this->adminNotificationAutomatic($trx_id,$charges,$operator,$user,$phone,$topUpData);
            $message =  ['success'=>[__('Mobile topup request successful')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function insertTransaction($trx_id,$sender_wallet,$charges,$operator,$mobile_number,$topUpData) {
        if(isset($topUpData) && isset($topUpData['status']) && $topUpData['status'] === "SUCCESSFUL"){
            $status = PaymentGatewayConst::STATUSSUCCESS;
        }else{
            $status = PaymentGatewayConst::STATUSPROCESSING;
        }
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge =  (($authWallet->balance - $charges['payable']) + $charges['agent_total_commission']);
        $details =[
            'topup_type'        => PaymentGatewayConst::AUTOMATIC,
            'topup_type_id'     => $operator['operatorId']??'',
            'topup_type_name'   => $operator['name']??'',
            'mobile_number'     => $mobile_number,
            'topup_amount'      => $charges['sender_amount']??0,
            'charges'           => $charges,
            'operator'          => $operator??[],
            'api_response'      => $topUpData??[],
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $sender_wallet->agent->id,
                'agent_wallet_id'               => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::MOBILETOPUP,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_special_char(PaymentGatewayConst::MOBILETOPUP," ")) . " Request Successful",
                'details'                       => json_encode($details),
                'attribute'                     => PaymentGatewayConst::SEND,
                'callback_ref'                  => $topUpData['customIdentifier'],
                'status'                        => $status,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);
            $this->agentProfitInsert($id,$sender_wallet,$charges);


            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function insertAutomaticCharges($id,$charges,$sender_wallet) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    =>  $id,
                'percent_charge'    =>  $charges['percent_charge'],
                'fixed_charge'      =>  $charges['fixed_charge'],
                'total_charge'      =>  $charges['total_charge'],
                'created_at'        =>  now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Mobile Topup"),
                'message'       => __('Mobile topup request successful')." " .$charges['sender_amount'].' '.$charges['sender_currency'],
                'image'         => get_image($sender_wallet->agent->image,'agent-profile'),
            ];

            //user Notification
            AgentNotification::create([
                'type'      =>  NotificationConst::MOBILE_TOPUP,
                'agent_id'  =>  $sender_wallet->agent->id,
                'message'   =>  $notification_content,
            ]);
            //Push Notification
            if( $this->basic_settings->agent_push_notification == true){
                try{
                    (new PushNotificationHelper())->prepareApi([$sender_wallet->agent->id],[
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
    public function topupChargeAutomatic($sender_amount,$charges,$operator,$sender_wallet) {

        $sPrecision = get_wallet_precision($sender_wallet->currency);
        $destinationCurrency = Currency::where(['code' => $operator['destinationCurrencyCode']])->first();
        $exchange_rate = $destinationCurrency->rate/$sender_wallet->currency->rate;

        $data['exchange_rate' ]                     = $exchange_rate;
        $data['sender_amount']                      = $sender_amount;
        $data['sender_currency']                    = $sender_wallet->currency->code;
        $data['sender_currency_rate']               = $sender_wallet->currency->rate;
        $data['destination_currency']               = $destinationCurrency->code;
        $data['destination_currency_rate']          = $destinationCurrency->rate;
        $data['conversion_amount']                  = $sender_amount * $exchange_rate;
        $data['percent_charge']                     = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']                       = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']                       = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']              = $sender_wallet->balance;
        $data['payable']                            = $data['sender_amount'] + $data['total_charge'];
        $data['payable_to_provider']                = $operator['supportsLocalAmounts'] == false ? $data['conversion_amount']/$operator['fx']['rate'] ?? 1 : $data['conversion_amount'];

        $data['agent_percent_commission']           = ($data['conversion_amount'] / 100) * $charges->agent_percent_commissions ?? 0;
        $data['agent_fixed_commission']             = $sender_wallet->currency->rate * $charges->agent_fixed_commissions ?? 0;
        $data['agent_total_commission']             = $data['agent_percent_commission'] + $data['agent_fixed_commission'];
        $data['precision_digit']                    = $sPrecision;

        return $data;
    }
    //agent profit
     public function agentProfitInsert($id,$authWallet,$charges) {
        DB::beginTransaction();
        try{
            DB::table('agent_profits')->insert([
                'agent_id'          => $authWallet->agent->id,
                'transaction_id'    => $id,
                'percent_charge'    => $charges['agent_percent_commission']??0,
                'fixed_charge'      => $charges['agent_fixed_commission']??0,
                'total_charge'      => $charges['agent_total_commission']??0,
                'created_at'        => now(),
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
           //
        }
    }
    //End Automatic
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }

    //admin notification
    public function adminNotificationManual($trx_id,$charges,$topup_type,$user,$phone){
        $exchange_rate = get_amount(1,$charges['destination_currency'])." = ".get_amount($charges['exchange_rate'],$charges['sender_currency'],$charges['precision_digit']);
        $notification_content = [
            //email notification
            'subject' => __("Mobile Top Up For")." ". $topup_type->name.' ('.$phone.' )',
            'greeting' =>__("Mobile topup request send to admin successful")." (".$topup_type->name."-".$phone." )",
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("Mobile Number")." : ".$phone."<br>".__("Operator Name")." : ".$topup_type->name."<br>".__("request Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Status")." : ".__("Pending"),

            //push notification
            'push_title' => __("Mobile topup request send to admin successful")." (".authGuardApi()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id.",".__("request Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']).",".__("Operator Name")." : ".$topup_type->name.",".__("Mobile Number")." : ".$phone,

            //admin db notification
            'notification_type' =>  NotificationConst::MOBILE_TOPUP,
            'admin_db_title' => "Mobile topup request send to admin successful"." (".authGuardApi()['type'].")",
            'admin_db_message' =>"Transaction ID"." : ".$trx_id.","."Request Amount"." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']).","."Operator Name"." : ".$topup_type->name.","."Mobile Number"." : ".$phone.","."Total Payable Amount"." : ".get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit'])." (".$user->email.")"
        ];
        try{
            //notification
            (new NotificationHelper())->admin(['admin.mobile.topup.index','admin.mobile.topup.pending','admin.mobile.topup.processing','admin.mobile.topup.complete','admin.mobile.topup.canceled','admin.mobile.topup.details','admin.mobile.topup.approved','admin.mobile.topup.rejected','admin.mobile.topup.export.data'])
                                    ->mail(ActivityNotification::class, [
                                        'subject'   => $notification_content['subject'],
                                        'greeting'  => $notification_content['greeting'],
                                        'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                        'user_type' => "admin",
                                        'title' => $notification_content['push_title'],
                                        'desc'  => $notification_content['push_content'],
                                        'from'  => 'api',
                                    ])
                                    ->adminDbContent([
                                        'type' => $notification_content['notification_type'],
                                        'title' => $notification_content['admin_db_title'],
                                        'message'  => $notification_content['admin_db_message'],
                                    ])
                                    ->send();


        }catch(Exception $e) {}

    }
    public function adminNotificationAutomatic($trx_id,$charges,$operator,$user,$phone,$topUpData){
        $exchange_rate = get_amount(1,$charges['sender_currency'])." = ".get_amount($charges['exchange_rate'],$charges['destination_currency'],$charges['precision_digit']);
        if(isset($topUpData) && isset($topUpData['status']) && $topUpData['status'] === "SUCCESSFUL"){
            $status ="success";
        }else{
            $status ="Processing";
        }
        $notification_content = [
            //email notification
            'subject' => __("Mobile Top Up For")." ". $operator['name'].' ('.$phone.' )',
            'greeting' =>__("Mobile topup request successful")." (".$operator['name']."-".$phone." )",
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("Mobile Number")." : ".$phone."<br>".__("Operator Name")." : ".$operator['name']."<br>".__("request Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Will Get")." : ".get_amount($charges['conversion_amount'],$charges['destination_currency'],$charges['precision_digit'])."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Status")." : ".__($status),

            //push notification
            'push_title' => __("Mobile topup request successful")." (".authGuardApi()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id.",".__("request Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']).",".__("Operator Name")." : ".$operator['name'].",".__("Mobile Number")." : ".$phone,

            //admin db notification
            'notification_type' =>  NotificationConst::MOBILE_TOPUP,
            'admin_db_title' => "Mobile topup request successful"." (".authGuardApi()['type'].")",
            'admin_db_message' =>"Transaction ID"." : ".$trx_id.","."Request Amount"." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']).","."Operator Name"." : ".$operator['name'].","."Mobile Number"." : ".$phone.","."Total Payable Amount"." : ".get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit'])." (".$user->email.")"
        ];
        try{
            //notification
            (new NotificationHelper())->admin(['admin.mobile.topup.index','admin.mobile.topup.pending','admin.mobile.topup.processing','admin.mobile.topup.complete','admin.mobile.topup.canceled','admin.mobile.topup.details','admin.mobile.topup.approved','admin.mobile.topup.rejected','admin.mobile.topup.export.data'])
                                    ->mail(ActivityNotification::class, [
                                        'subject'   => $notification_content['subject'],
                                        'greeting'  => $notification_content['greeting'],
                                        'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                        'user_type' => "admin",
                                        'title' => $notification_content['push_title'],
                                        'desc'  => $notification_content['push_content'],
                                        'from'  => 'api',
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
