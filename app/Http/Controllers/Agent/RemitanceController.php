<?php

namespace App\Http\Controllers\Agent;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\Response;
use App\Http\Helpers\TransactionLimit;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Notifications\Agent\Remittance\SenderEmail;
use App\Models\AgentNotification;
use App\Models\AgentRecipient;
use App\Models\AgentWallet;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class RemitanceController extends Controller
{
    protected  $trx_id;
    protected $basic_settings;

    public function __construct()
    {
        $this->trx_id = 'RT'.getTrxNum();
        $this->basic_settings = BasicSettingsProvider::get();
    }

    public function index() {
        $page_title = "Remittance";
        $exchangeCharge = TransactionSetting::where('slug','remittance')->where('status',1)->first();
        $senderCountries = Currency::sender()->active()->get();
        $receiverCountries = Currency::receiver()->active()->orderBy('id',"DESC")->get();
        $transactions = Transaction::agentAuth()->remitance()->latest()->take(5)->get();
        return view('agent.sections.remittance.index',compact(
            "page_title",
            'exchangeCharge',
            'senderCountries',
            'receiverCountries',
            'transactions'
        ));
    }
    //confirmed remittance
    public function confirmed(Request $request){
        $validated = Validator::make($request->all(),[
            'form_country'               =>'required',
            'to_country'                 =>'required',
            'transaction_type'           =>'required|string',
            'sender_recipient'           =>'required',
            'receiver_recipient'         =>'required',
            'send_amount'                =>"required|numeric",
        ])->validate();

        $exchangeCharge = TransactionSetting::where('slug','remittance')->where('status',1)->first();
        $user = authGuardApi()['user'];
        $transaction_type = $validated['transaction_type'];
        $basic_setting = BasicSettings::first();

        $sender_currency = Currency::where('id',$request->form_country)->active()->first();
        if(!$sender_currency) return back()->with(['error' => [__('Sender Country Not Found')]]);

        $userWallet = AgentWallet::where('agent_id',$user->id)->whereHas("currency",function($q) use ($sender_currency) {
            $q->where("code",$sender_currency->code)->active();
        })->active()->first();
        if(!$userWallet){
            return back()->with(['error' => [__("Agent doesn't exists.")]]);
        }

        $to_country = Currency::where('id',$request->to_country)->active()->first();
        if(!$to_country){
            return back()->with(['error' => [__('Receiver country not found')]]);
        }
        if($sender_currency->code == $to_country->code){
            return back()->with(['error' => [__("Remittances cannot be sent within the same country")]]);
        }
        $recipient = AgentRecipient::auth()->sender()->where("id",$request->sender_recipient)->first();
        if(!$recipient){
            return back()->with(['error' => [__('Recipient is invalid/mismatch transaction type or country')]]);
        }
        $receiver_recipient = AgentRecipient::auth()->receiver()->where("id",$request->receiver_recipient)->first();
        if(!$receiver_recipient){
            return back()->with(['error' => [__('Receiver Recipient is invalid')]]);
        }
        $charges = $this->chargeCalculate($userWallet->currency,$receiver_recipient->receiver_country, $validated['send_amount'],$exchangeCharge);

        $sender_currency_rate = $userWallet->currency->rate;
        $min_amount = $exchangeCharge->min_limit * $sender_currency_rate;
        $max_amount = $exchangeCharge->max_limit * $sender_currency_rate;

        if($charges->sender_amount < $min_amount || $charges->sender_amount > $max_amount) {
            return back()->with(['error' => [__('Please follow the transaction limit')]]);
        }

        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('agent_id',$userWallet->user->id,PaymentGatewayConst::SENDREMITTANCE,$userWallet->currency,$validated['send_amount'],$exchangeCharge,PaymentGatewayConst::SEND);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
            return back()->with(['error' => [$errorData['message']??""]]);
        }

        if($charges->payable > $userWallet->balance) {
            return back()->with(['error' => [__('Sorry, insufficient balance')]]);
        }
        $trx_id = $this->trx_id;
        $notifyData = [
            'trx_id'                    => $trx_id??'ndf',
            'title'                     => __("Send Remittance to")." @" . $receiver_recipient->fullname." (".@$receiver_recipient->email.")",
            'request_amount'            => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
            'exchange_rate'             => get_amount(1,$charges->sender_cur_code).' = '.get_amount($charges->exchange_rate,$charges->receiver_cur_code,$charges->r_precision_digit),
            'charges'                   => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
            'payable'                   => get_amount($charges->payable,$charges->sender_cur_code,$charges->precision_digit),
            'sending_country'           => @$sender_currency->country,
            'receiving_country'         => @$to_country->country,
            'sender_recipient_name'     => @$recipient->fullname,
            'receiver_recipient_name'   => @$receiver_recipient->fullname,
            'alias'                     => ucwords(str_replace('-', ' ', @$recipient->alias)),
            'transaction_type'          => @$transaction_type,
            'receiver_get'              => get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),
            'status'                    => __("Pending"),
        ];
        try{
            if($transaction_type === Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $receiver_user =  json_decode($receiver_recipient->details);
                $receiver_user_info =  $receiver_user;
                $receiver_user =  $receiver_user->id;
                $receiver_wallet = UserWallet::where('user_id',$receiver_user)->whereHas("currency",function($q) use ($to_country) {
                    $q->where("code",$to_country->code)->active();
                })->active()->first();
                if(!$receiver_wallet){
                    return back()->with(['error' => [__('Sorry, Receiver wallet not found')]]);
                }

                $sender = $this->insertSender( $trx_id,$userWallet,$recipient,$sender_currency,$to_country,$transaction_type, $receiver_recipient,$charges);
                if($sender){
                    $this->insertSenderCharges( $sender,$charges,$user,$receiver_recipient);
                    try{
                        if( $basic_setting->agent_email_notification == true){
                            $user->notify(new SenderEmail($user,(object)$notifyData));
                        }
                    }catch(Exception $e){}
                    try{
                        if( $basic_setting->agent_sms_notification == true){
                            sendSms($user,'SEND_REMITTANCE_AGENT',[
                                'form_country'          => @$sender_currency->country,
                                'to_country'            => $to_country->country,
                                'transaction_type'      => ucwords(str_replace('-', ' ', @$transaction_type)),
                                'sender_recipient'      => @$recipient->fullname,
                                'receiver_recipient'    => $receiver_recipient->fullname,
                                'send_amount'           => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
                                'recipient_amount'      => get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),
                                'trx'                   => $trx_id,
                                'time'                  => now()->format('Y-m-d h:i:s A'),
                                'balance'               => get_amount($userWallet->balance,$userWallet->currency->code,$charges->precision_digit),
                            ]);
                        }
                     }catch(Exception $e){}

                }
                $receiverTrans = $this->insertReceiver($trx_id,$userWallet,$recipient,$sender_currency,$to_country,$transaction_type,$receiver_user,$receiver_wallet,$receiver_recipient,$charges);
                if($receiverTrans){
                        $this->insertReceiverCharges( $receiverTrans,$charges,$user,$recipient,$receiver_recipient,$receiver_user_info);
                }
                session()->forget('sender_remittance_token');
                session()->forget('receiver_remittance_token');

            }else{
                $trx_id = $this->trx_id;
                $sender = $this->insertSender($trx_id,$userWallet,$recipient,$sender_currency,$to_country,$transaction_type, $receiver_recipient,$charges);
                if($sender){
                    $this->insertSenderCharges($sender,$charges,$user,$receiver_recipient);
                    try{
                        if( $basic_setting->agent_email_notification == true){
                            $user->notify(new SenderEmail($user,(object)$notifyData));
                        }
                    }catch(Exception $e){}
                    try{
                        if( $basic_setting->agent_sms_notification == true){
                            sendSms($user,'SEND_REMITTANCE_AGENT',[
                                'form_country'          => @$sender_currency->country,
                                'to_country'            => $to_country->country,
                                'transaction_type'      => ucwords(str_replace('-', ' ', @$transaction_type)),
                                'sender_recipient'      => @$recipient->fullname,
                                'receiver_recipient'    => $receiver_recipient->fullname,
                                'send_amount'           => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
                                'recipient_amount'      => get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),
                                'trx'                   => $trx_id,
                                'time'                  => now()->format('Y-m-d h:i:s A'),
                                'balance'               => get_amount($userWallet->balance,$userWallet->currency->code,$charges->precision_digit),
                            ]);
                        }
                     }catch(Exception $e){}
                    session()->forget('sender_remittance_token');
                    session()->forget('receiver_remittance_token');
                }
            }
            if($transaction_type != Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $this->adminNotification($trx_id,$charges,$user,$recipient,$receiver_recipient,$to_country,$sender_currency,$transaction_type);
            }
            return back()->with(['success' => [__("Remittance Money send successfully")]]);
        }catch(Exception $e) {

            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

    }
    //sender transaction
    public function insertSender($trx_id,$userWallet,$recipient,$form_country,$to_country,$transaction_type,$receiver_recipient,$charges) {
        $trx_id = $trx_id;
        $authWallet = $userWallet;

        if($transaction_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
            $status = 1;
            $afterCharge = ($authWallet->balance - $charges->payable) + $charges->agent_total_commission;

        }else{
            $status = 2;
            $afterCharge = ($authWallet->balance - $charges->payable);
        }

        $details =[
            'recipient_amount' => $charges->will_get,
            'sender_recipient' => $recipient,
            'receiver_recipient' => $receiver_recipient,
            'form_country' => $form_country->name,
            'to_country' => $to_country,
            'remitance_type' => $transaction_type,
            'sender' => $userWallet->agent,
            'bank_account' => $recipient->account_number??'',
            'charges' => $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $userWallet->agent->id,
                'agent_wallet_id'               => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges->sender_amount,
                'payable'                       => $charges->payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::SENDREMITTANCE," ")) . " To " .$receiver_recipient->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => $status,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);
            if($transaction_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $this->agentProfitInsert($id,$authWallet,$charges);
            }

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return $id;
    }
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($id,$charges,$user,$receiver_recipient) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges->percent_charge,
                'fixed_charge'      => $charges->fixed_charge,
                'total_charge'      => $charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();


            //notification
            $notification_content = [
                'title'         =>__("Send Remittance"),
                'message'       => __("Send Remittance to")." ".$receiver_recipient->fullname.' ' .$charges->sender_amount.' '.$charges->sender_cur_code." ".__("Successful"),
                'image'         =>  get_image($user->image,'agent-profile'),
            ];


            AgentNotification::create([
                'type'      => NotificationConst::SEND_REMITTANCE,
                'agent_id'  => $user->id,
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
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$userWallet,$recipient,$form_country,$to_country,$transaction_type,$receiver_user,$receiver_wallet,$receiver_recipient,$charges) {
        $trx_id = $trx_id;
        $receiverWallet = $receiver_wallet;
        $recipient_amount = ($receiverWallet->balance + $charges->will_get);
        $details =[
            'recipient_amount' => $charges->will_get,
            'receiver' => $receiver_recipient,
            'form_country' => $form_country->name,
            'to_country' => $to_country,
            'remitance_type' => $transaction_type,
            'sender' => $userWallet->agent,
            'bank_account'      => $receiver_recipient->account_number??'',
            'charges' => $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $receiver_user,
                'user_wallet_id'                => $receiverWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges->sender_amount,
                'payable'                       => $charges->will_get,
                'available_balance'             => $recipient_amount,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::RECEIVEREMITTANCE," ")) . " From " . $userWallet->agent->username,
                'details'                       => json_encode($details),
                'attribute'                     => PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return $id;
    }
    public function updateReceiverWalletBalance($receiverWallet,$recipient_amount) {

        $receiverWallet->update([
            'balance'   => $recipient_amount,
        ]);
    }
    public function insertReceiverCharges( $id,$charges,$user,$recipient,$receiver_recipient,$receiver_user_info) {

        DB::beginTransaction();

        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges->percent_charge,
                'fixed_charge'      => $charges->fixed_charge,
                'total_charge'      => $charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Send Remittance"),
                'message'       => __("Send Remittance From")." ".$user->fullname.' ' .$charges->will_get.' '.$charges->receiver_cur_code." ".__("Successful"),
                'image'         =>  get_image($receiver_user_info->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::SEND_REMITTANCE,
                'user_id'  => $receiver_user_info->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            if( $this->basic_settings->push_notification == true){
                try{
                    (new PushNotificationHelper())->prepare([$receiver_user_info->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'user',
                    ])->send();
                }catch(Exception $e) {}
            }
        }catch(Exception $e) {

            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    //end transaction helpers

    public function agentProfitInsert($id,$authWallet,$charges) {
        DB::beginTransaction();
        try{
            DB::table('agent_profits')->insert([
                'agent_id'          => $authWallet->agent->id,
                'transaction_id'    => $id,
                'percent_charge'    => $charges->agent_percent_commission,
                'fixed_charge'      => $charges->agent_fixed_commission,
                'total_charge'      => $charges->agent_total_commission,
                'created_at'        => now(),
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    public function chargeCalculate($sender_currency,$receiver_currency,$amount,$charges) {
        $sPrecision = get_wallet_precision($sender_currency);
        $rPrecision = get_wallet_precision($receiver_currency);
        $exchange_rate = $receiver_currency->rate / $sender_currency->rate;

        $data['exchange_rate']              = $exchange_rate;
        $data['sender_amount']              = $amount;
        $data['sender_cur_code']            = $sender_currency->code;
        $data['sender_cur_rate']            = $sender_currency->rate;

        $data['receiver_cur_code']          = $receiver_currency->code;
        $data['receiver_cur_rate']          = $receiver_currency->rate;

        $data['percent_charge']             = ($amount / 100) * $charges->percent_charge;
        $data['fixed_charge']               = $sender_currency->rate * $charges->fixed_charge;
        $data['total_charge']               = $data['percent_charge'] + $data['fixed_charge'];

        $data['conversion_amount']          = $amount * $exchange_rate;
        $data['payable']                    = $amount + $data['total_charge'];
        $data['will_get']                   = $amount * $exchange_rate;

        $data['agent_percent_commission']   = ($amount / 100) * $charges->agent_percent_commissions ?? 0;
        $data['agent_fixed_commission']     = $sender_currency->rate * $charges->agent_fixed_commissions ?? 0;
        $data['agent_total_commission']     = $data['agent_percent_commission'] + $data['agent_fixed_commission'];
        $data['default_currency']           = get_default_currency_code();
        $data['precision_digit']            = $sPrecision;
        $data['r_precision_digit']      = $rPrecision;


        return (object) $data;

    }
    //end transaction helpers

    public function getTokenForSender() {
        $data = request()->all();
        $in['receiver_country'] = $data['receiver_country'];
        $in['transacion_type'] = $data['transacion_type'];
        $in['sender_recipient'] = $data['sender_recipient'];
        $in['receiver_recipient'] = $data['receiver_recipient'];
        $in['sender_amount'] = $data['sender_amount'];
        $in['receive_amount'] = $data['receive_amount'];
        Session::put('sender_remittance_token',$in);
        return response()->json($data);

    }
    public function getTokenForReceiver() {
        $data = request()->all();
        $in['receiver_country'] = $data['receiver_country'];
        $in['transacion_type'] = $data['transacion_type'];
        $in['sender_recipient'] = $data['sender_recipient'];
        $in['receiver_recipient'] = $data['receiver_recipient'];
        $in['sender_amount'] = $data['sender_amount'];
        $in['receive_amount'] = $data['receive_amount'];
        Session::put('receiver_remittance_token',$in);
        return response()->json($data);

    }
    //sender filters
    public function getRecipientByCountry(Request $request){
        $sender_country = $request->sender_country;
        $transacion_type = $request->transacion_type;
        $data['recipient'] =  AgentRecipient::auth()->sender()->where('type',$transacion_type)->where('country',$sender_country)->get();
        return response()->json($data);
    }
    public function getRecipientByTransType(Request $request){
        $sender_country = $request->sender_country;
        $transacion_type = $request->transacion_type;
        $data['recipient'] =  AgentRecipient::auth()->sender()->where('type',$transacion_type)->where('country',$sender_country)->get();
        return response()->json($data);
    }
    //Receiver filters
    public function getRecipientByCountryReceiver(Request $request){
        $receiver_country = $request->receiver_country;
        $transacion_type = $request->transacion_type;
        $data['recipient'] =  AgentRecipient::auth()->receiver()->where('country', $receiver_country)->where('type',$transacion_type)->get();
        return response()->json($data);
    }
    public function getRecipientByTransTypeReceiver(Request $request){
        $receiver_country = $request->receiver_country;
        $transacion_type = $request->transacion_type;
        $data['recipient'] =  AgentRecipient::auth()->receiver()->where('country', $receiver_country)->where('type',$transacion_type)->get();
        return response()->json($data);
    }
    //admin notification
    public function adminNotification($trx_id,$charges,$user,$recipient,$receiver_recipient,$to_country,$form_country,$transaction_type){
        $exchange_rate = get_amount(1,$charges->sender_cur_code).' = '.get_amount($charges->exchange_rate,$charges->receiver_cur_code,$charges->precision_digit);
        if($transaction_type == 'bank-transfer'){
            $input_field = "bank Name";
        }else{
            $input_field = "Pickup Point";
        }
        $notification_content = [
            //email notification
            'subject' =>__("Send Remittance to")." @" . $receiver_recipient->firstname.' '.@$receiver_recipient->lastname." (".@$receiver_recipient->email.")",
            'greeting' =>__("Send Remittance Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$recipient->email."<br>".__("Receiver").": @".$receiver_recipient->email."<br>".__("Sender Amount")." : ".get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit)."<br>".__("Exchange Rate")." : ".$exchange_rate."<br>".__("Fees & Charges")." : ".get_amount($charges->total_charge,$charges->sender_cur_code,$charges->precision_digit)."<br>".__("Total Payable Amount")." : ".get_amount($charges->payable,$charges->sender_cur_code,$charges->precision_digit)."<br>".__("Recipient Received")." : ".get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit)."<br>".__("Transaction Type")." : ".ucwords(str_replace('-', ' ', @$transaction_type))."<br>".__("sending Country")." : ".$form_country."<br>".__("Receiving Country")." : ".$to_country->country."<br>".__($input_field)." : ".ucwords(str_replace('-', ' ', @$recipient->alias)),

            //push notification
            'push_title' => __("Send Remittance to")." @". $receiver_recipient->firstname.' '.@$receiver_recipient->lastname." (".@$receiver_recipient->email.")"." ".__('Successful'),
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$recipient->email." ".__("Receiver").": @".$receiver_recipient->email." ".__("Sender Amount")." : ".get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit)." ".__("Receiver Amount")." : ".get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),

            //admin db notification
            'notification_type' =>  NotificationConst::SEND_REMITTANCE,
            'admin_db_title' => "Send Remittance"." ".get_amount($charges->sender_amount,$charges->sender_cur_code)." (".$trx_id.")",
            'admin_db_message' =>"Sender".": @".$recipient->email.","."Receiver".": @".$receiver_recipient->email.","."Sender Amount"." : ".get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->r_precision_digit).","."Receiver Amount"." : ".get_amount($charges->will_get,$to_country->code,$charges->precision_digit)
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.remitance.index','admin.remitance.pending','admin.remitance.complete','admin.remitance.canceled','admin.remitance.details','admin.remitance.approved','admin.remitance.rejected','admin.remitance.export.data'])
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
