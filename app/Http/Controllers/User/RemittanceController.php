<?php

namespace App\Http\Controllers\User;

use App\Models\Receipient;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\User\Remittance\BankTransferMail;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\Response;
use App\Http\Helpers\TransactionLimit;
use App\Notifications\Admin\ActivityNotification;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Support\Facades\Validator;

class RemittanceController extends Controller
{
    protected  $trx_id;
    protected $basic_settings;

    public function __construct()
    {
        $this->trx_id = 'RT'.getTrxNum();
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function index() {
        $page_title =__( "Remittance");
        $exchangeCharge = TransactionSetting::where('slug','remittance')->where('status',1)->first();
        $senderCountries = Currency::sender()->active()->get();
        $receiverCountries = Currency::receiver()->active()->orderBy('id',"DESC")->get();
        $receipients = Receipient::auth()->orderByDesc("id")->paginate(12);
        $transactions = Transaction::auth()->remitance()->latest()->take(5)->get();
        return view('user.sections.remittance.index',compact(
            "page_title",
            'exchangeCharge',
            'senderCountries',
            'receiverCountries',
            'receipients',
            'transactions'
        ));
    }
    public function confirmed(Request $request){
        $validator = Validator::make($request->all(),[
            'form_country'               =>'required',
            'to_country'                 =>'required',
            'transaction_type'           =>'required|string',
            'recipient'                  =>'required',
            'send_amount'                =>"required|numeric",
            'receive_amount'             =>'required|numeric',
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors());
        }

        $validated = $validator->validate();
        $basic_setting = BasicSettings::first();
        $user = userGuard()['user'];
        $exchangeCharge = TransactionSetting::where('slug','remittance')->where('status',1)->first();

        $sender_currency = Currency::where('id',$request->form_country)->active()->first();
        if(!$sender_currency) return Response::error([__('Sender Country Not Found')]);

        $userWallet = UserWallet::where('user_id',$user->id)->whereHas("currency",function($q) use ($sender_currency) {
            $q->where("code",$sender_currency->code)->active();
        })->active()->first();
        if(!$userWallet) return Response::error([__('User wallet not found')]);

        $to_country = Currency::where('id',$request->to_country)->active()->first();
        if(!$to_country) return Response::error([__('Receiver country not found')]);

        if($sender_currency->code == $to_country->code){
            return Response::error([ __("Remittances cannot be sent within the same country")]);
        }

        $recipient = Receipient::auth()->where("id",$request->recipient)->first();
        if(!$recipient) return Response::error([__('Recipient is invalid')]);

        $charges = $this->chargeCalculate($sender_currency,$to_country, $validated['send_amount'],$exchangeCharge);

        $sender_currency_rate   = $sender_currency->rate;
        $min_amount             = $exchangeCharge->min_limit * $sender_currency_rate;
        $max_amount             = $exchangeCharge->max_limit * $sender_currency_rate;

        if($charges->sender_amount < $min_amount || $charges->sender_amount > $max_amount) return Response::error([__('Please follow the transaction limit')]);

        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('user_id',$userWallet->user->id,PaymentGatewayConst::SENDREMITTANCE,$userWallet->currency,$validated['send_amount'],$exchangeCharge,PaymentGatewayConst::SEND);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
            return Response::error([__($errorData['message']??"")]);
        }

        if($charges->payable > $userWallet->balance) return Response::error([__('Sorry, insufficient balance')]);

        $transaction_type = $request->transaction_type;
        try{
            if($transaction_type === Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $receiver_user =  json_decode($recipient->details);
                $receiver_user =  $receiver_user->id;
                $receiver_wallet = UserWallet::where('user_id',$receiver_user)->active()->whereHas("currency",function($q) use ($to_country) {
                    $q->where("code",$to_country->code)->active();
                })->active()->first();
                if(!$receiver_wallet){
                    return Response::error([__('Receiver wallet not found')]);
                }
                $trx_id = $this->trx_id;
                $sender = $this->insertSender($trx_id,$user,$userWallet,$charges,$recipient,$sender_currency,$to_country,$transaction_type);
                if($sender){
                     $this->insertSenderCharges($sender,$charges,$user,$recipient);
                }
                $receiverTrans = $this->insertReceiver($trx_id,$user,$charges,$recipient,$sender_currency,$to_country,$transaction_type,$receiver_user,$receiver_wallet);
                if($receiverTrans){
                     $this->insertReceiverCharges($receiverTrans,$charges,$user,$receiver_wallet);
                }
                session()->forget('remittance_token');

            }else{
                $trx_id = $this->trx_id;
                $sender = $this->insertSender($trx_id,$user,$userWallet,$charges,$recipient,$sender_currency,$to_country,$transaction_type);
                if($sender){
                     $this->insertSenderCharges($sender,$charges,$user,$recipient);
                     session()->forget('remittance_token');
                }
                if( $basic_setting->email_notification == true){
                        $notifyData = [
                            'trx_id'            => $trx_id,
                            'title'             => __("Send Remittance to")." @" . $recipient->firstname.' '.@$recipient->lastname." (".@$recipient->email.")",
                            'request_amount'    => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
                            'exchange_rate'     => get_amount(1,$charges->sender_cur_code).' = '.get_amount($charges->exchange_rate,$charges->receiver_cur_code,$charges->r_precision_digit),
                            'charges'           => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
                            'payable'           => get_amount($charges->payable,$charges->sender_cur_code,$charges->precision_digit),
                            'sending_country'   => @$sender_currency->name,
                            'receiving_country' => @$to_country->name,
                            'receiver_name'     => @$recipient->firstname.' '.@$recipient->lastname,
                            'alias'             =>  ucwords(str_replace('-', ' ', @$recipient->alias)),
                            'transaction_type'  =>  @$transaction_type,
                            'receiver_get'      =>  get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),
                            'status'            => __("Pending"),
                        ];
                        //sender notifications
                        try{
                            $user->notify(new BankTransferMail($user,(object)$notifyData));
                        }catch(Exception $e){}
                }
                if( $basic_setting->sms_notification == true){
                    try{
                     sendSms($user,'SEND_REMITTANCE',[
                         'form_country'      => @$sender_currency->name,
                         'to_country'        => $to_country->country,
                         'transaction_type'  => ucwords(str_replace('-', ' ', @$transaction_type)),
                         'recipient'         => $recipient->fullname,
                         'send_amount'       => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
                         'recipient_amount'  => get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),
                         'trx'               => $trx_id,
                         'time'              => now()->format('Y-m-d h:i:s A'),
                         'balance'           => get_amount($userWallet->balance,$userWallet->currency->code,$charges->precision_digit),
                     ]);
                    }catch(Exception $e) {}
                 }
            }
            if($transaction_type != Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $this->adminNotification($trx_id,$charges,$user,$recipient,$to_country,$sender_currency,$transaction_type);
            }

            Session::flash('success', [__('Remittance Money send successfully')]);
            return Response::success(['success' => [__('Remittance Money send successfully')]],['url' => route('user.remittance.index')]);
        }catch(Exception $e) {
            return Response::error([__("Something went wrong! Please try again.")]);
        }

    }

    //sender transaction
    public function insertSender($trx_id,$user,$userWallet,$charges,$recipient,$sender_currency,$to_country,$transaction_type) {
            $trx_id = $trx_id;
            $authWallet = $userWallet;
            $afterCharge = ($authWallet->balance - $charges->payable);
            $details =[
                'recipient_amount' => $charges->will_get,
                'receiver' => $recipient,
                'form_country' => $sender_currency->name,
                'to_country' => $to_country,
                'remitance_type' => $transaction_type,
                'sender' => $user,
                'bank_account' => $recipient->account_number??'',
                'charges' => $charges,
            ];
            if($transaction_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $status = 1;
            }else{
                $status = 2;
            }
            DB::beginTransaction();
            try{
                $id = DB::table("transactions")->insertGetId([
                    'user_id'                       => $user->id,
                    'user_wallet_id'                => $authWallet->id,
                    'payment_gateway_currency_id'   => null,
                    'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                    'trx_id'                        => $trx_id,
                    'request_amount'                => $charges->sender_amount,
                    'payable'                       => $charges->payable,
                    'available_balance'             => $afterCharge,
                    'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::SENDREMITTANCE," ")) . " To " .$recipient->fullname,
                    'details'                       => json_encode($details),
                    'attribute'                      =>PaymentGatewayConst::SEND,
                    'status'                        => $status,
                    'created_at'                    => now(),
                ]);
                $this->updateSenderWalletBalance($authWallet,$afterCharge);

                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                throw new Exception(__("Something went wrong! Please try again."));
            }
            return $id;
    }
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($id,$charges,$user,$recipient){
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    =>  $id,
                'percent_charge'    =>  $charges->percent_charge,
                'fixed_charge'      =>  $charges->fixed_charge,
                'total_charge'      =>  $charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Send Remittance"),
                'message'       => __("Send Remittance Request to")." ".$recipient->fullname.' ' .get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit)." ".__("Successful"),
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::SEND_REMITTANCE,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);

            //Push Notifications
            if( $this->basic_settings->push_notification == true){
                try{
                    (new PushNotificationHelper())->prepare([$user->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'user',
                    ])->send();
                }catch(Exception $e) {}
            }

        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$user,$charges,$recipient,$sender_currency,$to_country,$transaction_type,$receiver_user,$receiver_wallet) {

        $trx_id = $trx_id;
        $receiverWallet = $receiver_wallet;
        $recipient_amount = ($receiverWallet->balance + $charges->will_get);
        $details =[
            'recipient_amount'  => $charges->will_get,
            'receiver'          => $recipient,
            'form_country'      => $sender_currency->name,
            'to_country'        => $to_country,
            'remitance_type'    => $transaction_type,
            'sender'            => $user,
            'bank_account'      => $recipient->account_number??'',
            'charges'           => $charges,
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
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::RECEIVEREMITTANCE," ")) . " From " .$user->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function updateReceiverWalletBalance($receiverWallet,$recipient_amount) {
        $receiverWallet->update([
            'balance'   => $recipient_amount,
        ]);
    }
    public function insertReceiverCharges($id,$charges,$user,$receiver_wallet) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    =>  $id,
                'percent_charge'    =>  0,
                'fixed_charge'      =>  0,
                'total_charge'      =>  0,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Send Remittance"),
                'message'       => __("Send Remittance From")." ".$user->fullname.' ' .get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit)." ".__('Successful'),
                'image'         => get_image($receiver_wallet->user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::SEND_REMITTANCE,
                'user_id'  => $receiver_wallet->user->id,
                'message'   => $notification_content,
            ]);

            //Push Notifications
            if( $this->basic_settings->push_notification == true){
                try{
                    (new PushNotificationHelper())->prepare([$receiver_wallet->user->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'user',
                    ])->send();
                }catch(Exception $e) {}
            }
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    //end transaction helpers
    public function getToken() {
        $data = request()->all();
        $in['receiver_country'] = $data['receiver_country'];
        $in['transacion_type'] = $data['transacion_type'];
        $in['recipient'] = $data['recipient'];
        $in['sender_amount'] = $data['sender_amount'];
        $in['receive_amount'] = $data['receive_amount'];
        Session::put('remittance_token',$in);
        return response()->json($data);

    }
    public function getRecipientByCountry(Request $request){
        $receiver_country = $request->receiver_country;
        $transacion_type = $request->transacion_type;
        if( $transacion_type != null || $transacion_type != ''){
            $data['recipient'] = Receipient::auth()->where('country', $receiver_country)->where('type',$transacion_type)->get();

        }else{
            $data['recipient'] = Receipient::auth()->where('country', $receiver_country)->get();
        }
        return response()->json($data);
    }
    public function getRecipientByTransType(Request $request){
        $receiver_country = $request->receiver_country;
        $transacion_type = $request->transacion_type;
          $data['recipient'] = Receipient::auth()->where('country', $receiver_country)->where('type',$transacion_type)->get();
        return response()->json($data);
    }
    //admin notification
    public function adminNotification($trx_id,$charges,$user,$recipient,$to_country,$sender_currency,$transaction_type){
        $exchange_rate = get_amount(1,$charges->sender_cur_code).' = '.get_amount($charges->exchange_rate,$charges->receiver_cur_code,$charges->precision_digit);
        if($transaction_type == 'bank-transfer'){
            $input_field = "bank Name";
        }else{
            $input_field = "Pickup Point";
        }
        $notification_content = [
            //email notification
            'subject' =>__("Send Remittance to")." @" . $recipient->firstname.' '.@$recipient->lastname." (".@$recipient->email.")",
            'greeting' =>__("Send Remittance Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$user->email."<br>".__("Receiver").": @".$recipient->email."<br>".__("Sender Amount")." : ".get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit)."<br>".__("Exchange Rate")." : ".$exchange_rate."<br>".__("Fees & Charges")." : ".get_amount($charges->total_charge,$charges->sender_cur_code,$charges->precision_digit)."<br>".__("Total Payable Amount")." : ".get_amount($charges->payable,$charges->sender_cur_code,$charges->precision_digit)."<br>".__("Recipient Received")." : ".get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit)."<br>".__("Transaction Type")." : ".ucwords(str_replace('-', ' ', @$transaction_type))."<br>".__("sending Country")." : ".$sender_currency->name."<br>".__("Receiving Country")." : ".$to_country->name."<br>".__($input_field)." : ".ucwords(str_replace('-', ' ', @$recipient->alias)),

            //push notification
            'push_title' => __("Send Remittance to")." @". $recipient->firstname.' '.@$recipient->lastname." (".@$recipient->email.")"." ".__('Successful'),
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$user->email." ".__("Receiver").": @".$recipient->email." ".__("Sender Amount")." : ".get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit)." ".__("Receiver Amount")." : ".get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),

            //admin db notification
            'notification_type' =>  NotificationConst::SEND_REMITTANCE,
            'admin_db_title' => "Send Remittance"." ".get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit)." (".$trx_id.")",
            'admin_db_message' =>"Sender".": @".$user->email.","."Receiver".": @".$recipient->email.","."Sender Amount"." : ".get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit).","."Receiver Amount"." : ".get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit)
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
    public function chargeCalculate($sender_currency,$receiver_currency,$amount,$charges){

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

        $data['default_currency']           = get_default_currency_code();
        $data['precision_digit']            = $sPrecision;
        $data['r_precision_digit']      = $rPrecision;

        return (object) $data;
    }
}

