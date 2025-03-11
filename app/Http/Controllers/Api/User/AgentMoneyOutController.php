<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\Agent;
use App\Models\AgentNotification;
use App\Models\AgentQrCode;
use App\Models\AgentWallet;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\MoneyOut\ReceiverMail;
use App\Notifications\User\MoneyOut\SenderMail;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AgentMoneyOutController extends Controller
{
    protected  $trx_id;
    protected $basic_settings;
    public function __construct()
    {
        $this->trx_id = 'AMO'.getTrxNum();
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function index() {
        $moneyOutCharge = TransactionSetting::where('slug','money-out')->where('status',1)->get()->map(function($data){
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
        $transactions = Transaction::auth()->agentMoneyOut()->latest()->take(10)->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
                if($item->attribute == payment_gateway_const()::SEND){
                    return[
                        'id'                        => @$item->id,
                        'type'                      => $item->attribute,
                        'trx'                       => @$item->trx_id,
                        'transaction_type'          => $item->type,
                        'transaction_heading'       => "Money Out to @" . @$item->details->receiver_email,
                        'request_amount'            => get_amount($item->details->charges->payable,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency)),
                        'payable'                   => get_amount($item->details->charges->payable,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency)),
                        'exchange_rate'             => get_amount(1,$item->details->charges->sender_amount) ." = ". get_amount($item->details->charges->exchange_rate,$item->details->charges->receiver_currency,$item->details->charges->r_precision_digit??2),
                        'total_charge'              => get_amount($item->charge->total_charge,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency)),
                        'recipient_received'        => get_amount($item->details->charges->receiver_amount,$item->details->charges->receiver_currency,$item->details->charges->r_precision_digit??2),
                        'current_balance'           => get_amount($item->available_balance,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency)),
                        'status'                    => @$item->stringStatus->value,
                        'status_values'             => @$item->status,
                        'date_time'                 => @$item->created_at,
                        'status_info'               =>(object)@$statusInfo,
                    ];
                }
        });
        $get_remaining_fields = [
            'transaction_type'  =>  PaymentGatewayConst::AGENTMONEYOUT,
            'attribute'         =>  PaymentGatewayConst::SEND,
        ];

        $data =[
            'base_curr'             => get_default_currency_code(),
            'base_curr_rate'        => get_amount(get_default_currency_rate(),null,get_wallet_precision()),
            'get_remaining_fields'  => (object) $get_remaining_fields,
            'moneyOutCharge'        => (object) $moneyOutCharge,
            'transactions'          => $transactions,
        ];
        $message =  ['success'=>[__('Money Out Information')]];
        return Helpers::success($data,$message);
    }
    public function checkAgent(Request $request){
        $validator = Validator::make(request()->all(), [
            'credentials'     => "required",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $credentials = $request->credentials;
        $exist = Agent::where(function($query) use ($credentials) {
            $query->where('email', $credentials)
                  ->orWhere('mobile',(int)$credentials)
                  ->orWhere('full_mobile', $credentials);
        })->active()->first();
        if( !$exist){
            $error = ['error'=>[__("Agent doesn't exists.")]];
            return Helpers::error($error);
        }

        $user = authGuardApi()['user'];
        if(@$exist && @$user->email == @$exist->email || @$user->full_mobile == @$exist->full_mobile){
            $error = ['error'=>[__("Can't money out to your own")]];
            return Helpers::error($error);
        }
        $data =[
            'agent_email'   => $exist->email??$exist->full_mobile,
        ];
        $message =  ['success'=>[__('Valid agent for transaction.')]];
        return Helpers::success($data,$message);
    }

    public function qrScan(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'qr_code'     => "required|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $qr_code = $request->qr_code;
        $qrCode = AgentQrCode::where('qr_code',$qr_code)->first();
        if(!$qrCode){
            $error = ['error'=>[__('Not found')]];
            return Helpers::error($error);
        }
        $user = Agent::where('id',$qrCode->agent_id)->active()->first();
        if(!$user){
            $error = ['error'=>[__("Agent doesn't exists.")]];
            return Helpers::error($error);
        }
        if( @$user->email == @auth()->user()->email || @$user->full_mobile ==  @auth()->user()->full_mobile){
            $error = ['error'=>[__("Can't money out to your own")]];
            return Helpers::error($error);
        }
        $data =[
            'agent_email'   => $user->email??$user->full_mobile,
            ];
        $message =  ['success'=>[__('QR Scan Result.')]];
        return Helpers::success($data,$message);
    }
    public function confirmed(Request $request){
        $validator = Validator::make(request()->all(), [
            'sender_amount'     => "required|numeric|gt:0",
            'sender_wallet'     => "required|string|exists:currencies,code",
            'receiver_amount'   => "required|numeric|gt:0",
            'receiver_wallet'   => "required|string|exists:currencies,code",
            'credentials'       => 'required',
            'remark'            => "nullable|string|max:300"
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $basic_setting = BasicSettings::first();
        $credentials = $validated['credentials'];
        $sender_wallet = UserWallet::auth()->active()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['sender_wallet'])->active();
        })->active()->first();
        if(!$sender_wallet){
            $error = ['error'=>[__('User wallet not found')]];
            return Helpers::error($error);
        }

        $receiver_currency = Currency::receiver()->active()->where('code',$validated['receiver_wallet'])->first();
        if(!$receiver_currency){
            $error = ['error'=>[__('Receiver Currency Not Found')]];
            return Helpers::error($error);
        }

        $receiver = Agent::where(function($query) use ($credentials) {
            $query->where('email', $credentials)
                  ->orWhere('mobile',(int)$credentials)
                  ->orWhere('full_mobile', $credentials);
        })->active()->first();
        if(!$receiver){
            $error = ['error'=>[__("Receiver doesn't exists or Receiver is temporary banned")]];
            return Helpers::error($error);
        }

        $receiver_wallet = AgentWallet::where("agent_id",$receiver->id)->whereHas("currency",function($q) use ($receiver_currency){
            $q->receiver()->where("code",$receiver_currency->code);
        })->first();
        if(!$receiver_wallet){
            $error = ['error'=>[__('Receiver wallet not found')]];
            return Helpers::error($error);
        }
        if( $sender_wallet->user->email == $receiver->email || $sender_wallet->user->email == $receiver->full_mobile){
            $error = ['error'=>[__("Can't Money out to your own")]];
            return Helpers::error($error);
        }
        $trx_charges =  TransactionSetting::where('slug','money-out')->where('status',1)->first();
        $charges = $this->moneyOutCharge($validated['sender_amount'],$trx_charges,$sender_wallet,$receiver_currency);

        $sender_currency_rate = $sender_wallet->currency->rate;
        $min_amount = $trx_charges->min_limit * $sender_currency_rate;
        $max_amount = $trx_charges->max_limit * $sender_currency_rate;

        if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount){
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }

        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('user_id',$sender_wallet->user->id,PaymentGatewayConst::AGENTMONEYOUT,$sender_wallet->currency,$validated['sender_amount'],$trx_charges,PaymentGatewayConst::SEND);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
            $error = ['error'=>[__($errorData['message'] ?? __("Something went wrong! Please try again."))]];
            return Helpers::error($error);
        }

        if($charges['payable'] > $sender_wallet->balance){
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
        }

        try{
            $trx_id = $this->trx_id;
            $sender = $this->insertSender($trx_id,$sender_wallet,$charges,$receiver_wallet,$validated['remark']);
            if($sender){
                 $this->insertSenderCharges($sender,$charges,$sender_wallet,$receiver_wallet);
                try{
                    if( $basic_setting->email_notification == true){
                        $notifyDataSender = [
                            'trx_id'            => $trx_id,
                            'title'             => __("Money Out to")." @" . @$receiver_wallet->agent->username." (".@$receiver_wallet->agent->fullname.")",
                            'request_amount'    => get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']),
                            'payable'           => get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit']),
                            'charges'           => get_amount( $charges['total_charge'],$charges['sender_currency'],$charges['precision_digit']),
                            'exchange_rate'     => get_amount(1,$charges['sender_currency']).' = '. get_amount($charges['exchange_rate'],$charges['receiver_currency'],$charges['r_precision_digit']),
                            'received_amount'   => get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),
                            'status'            => __("success"),
                        ];
                        //sender notifications
                        $sender_wallet->user->notify(new SenderMail($sender_wallet->user,(object)$notifyDataSender));
                    }
                }catch(Exception $e){}

                if( $basic_setting->sms_notification == true){
                    try{
                        sendSms($sender_wallet->user,'MONEY_OUT',[
                            'amount'    => get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']),
                            'agent'     => $receiver->fullname.'( '.$receiver->fullname.' )',
                            'charge'    => get_amount( $charges['total_charge'],$charges['sender_currency'],$charges['precision_digit']),
                            'trx'       => $trx_id,
                            'time'      => now()->format('Y-m-d h:i:s A')
                        ]);
                    }catch(Exception $e){}
                }
            }

            $receiverTrans = $this->insertReceiver($trx_id, $sender_wallet,$charges,$receiver_wallet,$validated['remark']);
            if($receiverTrans){
                 $this->insertReceiverCharges($receiverTrans,$charges,$sender_wallet,$receiver_wallet);
                 //Receiver notifications
                try{
                    if( $basic_setting->email_notification == true){
                        $notifyDataReceiver = [
                            'trx_id'  => $trx_id,
                            'title'  => __("Money Out From")." @" .@$sender_wallet->user->username." (".@$sender_wallet->user->fullname.")",
                            'received_amount'  => get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),
                            'status'  => __("success"),
                        ];
                        //send notifications
                        $receiver->notify(new ReceiverMail($receiver,(object)$notifyDataReceiver));
                    }
                }catch(Exception $e){}
                if( $basic_setting->agent_sms_notification == true){
                    try{
                        sendSms($receiver,'MONEY_OUT_TO_AGENT',[
                            'amount'        => get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),
                            'user'          => $sender_wallet->user->fullname.'('.$sender_wallet->user->fullname.')',
                            'trx'           => $trx_id,
                            'time'          =>  now()->format('Y-m-d h:i:s A')
                        ]);
                    }catch(Exception $e){}
                }

            }
             //admin notification
             $this->adminNotification($trx_id,$charges,$sender_wallet,$receiver_wallet);
            $message =  ['success'=>[__('Money Out Successful')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    //sender transaction
     public function insertSender($trx_id,$sender_wallet,$charges,$receiver_wallet,$remark) {
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge = ($authWallet->balance - $charges['payable']);

        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $sender_wallet->user->id,
                'user_wallet_id'                => $sender_wallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::AGENTMONEYOUT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => $remark??"",
                'details'                       => json_encode([
                                                        'receiver_username'=> $receiver_wallet->agent->username,
                                                        'receiver_email'=> $receiver_wallet->agent->email,
                                                        'sender_username'=> $sender_wallet->user->username,
                                                        'sender_email'=> $sender_wallet->user->email,
                                                        'charges' => $charges
                                                    ]),
                'attribute'                     => PaymentGatewayConst::SEND,
                'status'                        => GlobalConst::SUCCESS,
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
    public function agentProfitInsert($id,$receiverWallet,$charges) {
        DB::beginTransaction();
        try{
            DB::table('agent_profits')->insert([
                'agent_id'          => $receiverWallet->agent->id,
                'transaction_id'    => $id,
                'percent_charge'    => $charges['agent_percent_commission'],
                'fixed_charge'      => $charges['agent_fixed_commission'],
                'total_charge'      => $charges['agent_total_commission'],
                'created_at'        => now(),
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($id,$charges,$sender_wallet,$receiver_wallet) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges['percent_charge'],
                'fixed_charge'      => $charges['fixed_charge'],
                'total_charge'      => $charges['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            //store notification
            $notification_content = [
                'title'         =>__("Money Out"),
                'message'       => __('Money Out To')." ".$receiver_wallet->agent->fullname.' ' .get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit'])." ".__("Successful"),
                'image'         =>  get_image($sender_wallet->user->image,'user-profile'),
            ];
            UserNotification::create([
                'type'      => NotificationConst::AGENTMONEYOUT,
                'user_id'  => $sender_wallet->user->id,
                'message'   => $notification_content,
            ]);
             //Push Notification
            if( $this->basic_settings->push_notification == true){
                try{
                    (new PushNotificationHelper())->prepareApi([$sender_wallet->user->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'user',
                    ])->send();
                }catch(Exception $e) {}
             }


        }catch(Exception $e) {

            DB::rollBack();
             $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$sender_wallet,$charges,$receiver_wallet,$remark) {
        $trx_id = $trx_id;
        $receiverWallet = $receiver_wallet;
        $recipient_amount = ($receiverWallet->balance +  $charges['receiver_amount']) + $charges['agent_total_commission'];


        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $receiver_wallet->agent->id,
                'agent_wallet_id'               => $receiver_wallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::AGENTMONEYOUT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['receiver_amount'],
                'payable'                       => $charges['receiver_amount'],
                'available_balance'             => $receiver_wallet->balance + $charges['receiver_amount'],
                'remark'                        => $remark??"",
                'details'                       => json_encode([
                                                        'receiver_username'=> $receiver_wallet->agent->username,
                                                        'receiver_email'=> $receiver_wallet->agent->email,
                                                        'sender_username'=> $sender_wallet->user->username,
                                                        'sender_email'=> $sender_wallet->user->email,
                                                        'charges' => $charges
                                                    ]),
                'attribute'                     =>PaymentGatewayConst::RECEIVED,
                'status'                        => GlobalConst::SUCCESS,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);
            $this->agentProfitInsert($id,$receiverWallet,$charges);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
             $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateReceiverWalletBalance($receiverWallet,$recipient_amount) {
        $receiverWallet->update([
            'balance'   => $recipient_amount,
        ]);
    }
    public function insertReceiverCharges($id,$charges,$sender_wallet,$receiver_wallet) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => 0,
                'fixed_charge'      => 0,
                'total_charge'      => 0,
                'created_at'        => now(),
            ]);
            DB::commit();

            //store notification
            $notification_content = [
                'title'         =>__("Money Out"),
                'message'       => __('Money Out From')." ".$sender_wallet->user->fullname.' ' .get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit'])." ".__("Successful"),
                'image'         => get_image($receiver_wallet->agent->image,'agent-profile'),
            ];
            AgentNotification::create([
                'type'      => NotificationConst::AGENTMONEYOUT,
                'agent_id'   => $receiver_wallet->agent->id,
                'message'   => $notification_content,
            ]);
            //Push Notification
            if( $this->basic_settings->agent_push_notification == true){
                try{
                    (new PushNotificationHelper())->prepareApi([$receiver_wallet->agent->id],[
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
    public function moneyOutCharge($sender_amount,$charges,$sender_wallet,$receiver_currency) {
        $sPrecision = get_wallet_precision($sender_wallet->currency);
        $rPrecision = get_wallet_precision($receiver_currency);
        $exchange_rate = $receiver_currency->rate / $sender_wallet->currency->rate;

        $data['exchange_rate']                      = $exchange_rate;
        $data['sender_amount']                      = $sender_amount;
        $data['sender_currency']                    = $sender_wallet->currency->code;
        $data['receiver_amount']                    = $sender_amount * $exchange_rate;
        $data['receiver_currency']                  = $receiver_currency->code;
        $data['percent_charge']                     = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']                       = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']                       = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']              = $sender_wallet->balance;
        $data['payable']                            = $sender_amount + $data['total_charge'];
        $data['agent_percent_commission']           = ($sender_amount / 100) * $charges->agent_percent_commissions ?? 0;
        $data['agent_fixed_commission']             = $exchange_rate * $charges->agent_fixed_commissions ?? 0;
        $data['agent_total_commission']             = $data['agent_percent_commission'] + $data['agent_fixed_commission'];
        $data['precision_digit']                    = $sPrecision;
        $data['r_precision_digit']                  = $rPrecision;

        return $data;
    }
    //admin notification
    public function adminNotification($trx_id,$charges,$sender_wallet,$receiver_wallet){
        $notification_content = [
            //email notification
            'subject' => __("Money Out to")." @" . @$receiver_wallet->agent->username." (".@$receiver_wallet->agent->email.")",
            'greeting' =>__("Money Out Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$sender_wallet->user->email."<br>".__("Receiver").": @".$receiver_wallet->agent->email."<br>".__("Sender Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'])."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['total_charge'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__('Exchange Rate')." : ".get_amount(1,$charges['sender_currency']).' = '. get_amount($charges['exchange_rate'],$charges['receiver_currency'],$charges['precision_digit'])."<br>".__("Recipient Received")." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit'])."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Money Out")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$sender_wallet->user->email." ".__("Receiver").": @".$receiver_wallet->agent->email." ".__("Sender Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit'])." ".__("Receiver Amount")." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),

            //admin db notification
            'notification_type' =>  NotificationConst::AGENTMONEYOUT,
            'admin_db_title' => "Money Out"." (".userGuard()['type'].")",
            'admin_db_message' =>"Sender".": @".$sender_wallet->user->email.","."Receiver".": @".$receiver_wallet->agent->email.","."Sender Amount"." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']).","."Receiver Amount"." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit'])
        ];
        try{
            //notification
            (new NotificationHelper())->admin(['admin.agent.money.out.index','admin.agent.money.out.export.data'])
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
