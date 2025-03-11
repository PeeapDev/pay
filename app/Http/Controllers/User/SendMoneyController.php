<?php

namespace App\Http\Controllers\User;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\NotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\User\SendMoney\ReceiverMail;
use App\Notifications\User\SendMoney\SenderMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use App\Notifications\Admin\ActivityNotification;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Support\Facades\Validator;

class SendMoneyController extends Controller
{
    protected  $trx_id;
    protected $basic_settings;

    public function __construct()
    {
        $this->trx_id = 'SM'.getTrxNum();
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function index() {
        $page_title = __("Send Money");
        $sender_wallets = Currency::sender()->active()->get();
        $receiver_wallets = Currency::receiver()->active()->orderBy('id',"DESC")->get();
        $sendMoneyCharge = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $transactions = Transaction::auth()->senMoney()->latest()->take(10)->get();
        return view('user.sections.send-money.index',compact("page_title",'sendMoneyCharge','transactions','receiver_wallets','sender_wallets'));
    }
    public function checkUser(Request $request){
        $credentials = $request->credentials;
        $exist['data'] = User::where(function($query) use ($credentials) {
            $query->where('email', $credentials)
                  ->orWhere('mobile', (int)$credentials)
                  ->orWhere('full_mobile', $credentials);
        })->active()->first();

        $user = auth()->user();
        if(@$exist['data'] && $user->email == @$exist['data']->email || @$user->full_mobile == @$exist['data']->full_mobile){
            return response()->json(['own'=>__("Can't send money to your own")]);
        }
        return response($exist);
    }
    public function confirmed(Request $request){
        $validated = Validator::make($request->all(),[
            'sender_amount'     => "required|numeric|gt:0",
            'sender_wallet'     => "required|string|exists:currencies,code",
            'receiver_amount'   => "required|numeric|gt:0",
            'receiver_wallet'   => "required|string|exists:currencies,code",
            'credentials'       => "required",
            'remark'            => "nullable|string|max:300"
        ])->validate();

        $basic_setting = BasicSettings::first();
        $user = userGuard()['user'];
        $credentials = $validated['credentials'];

        $sender_wallet = UserWallet::auth()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['sender_wallet'])->active();
        })->active()->first();
        if(!$sender_wallet) return back()->with(['error' => [__("Your wallet isn't available with currency").' ('.$validated['sender_wallet'].')']]);

        if( $sender_wallet->user->email == $validated['credentials'] || $sender_wallet->user->mobile == (int)$validated['credentials'] || $sender_wallet->user->full_mobile == $validated['credentials']) return back()->with(['error' => [__("Can't send money to your own")]]);

        $receiver_currency = Currency::receiver()->active()->where('code',$validated['receiver_wallet'])->first();

        if(!$receiver_currency) return back()->with(['error' => [__('Receiver Currency Not Found')]]);

        $trx_charges = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $charges = $this->transferCharges($validated['sender_amount'],$trx_charges,$sender_wallet,$receiver_currency,$validated['sender_amount']);

        $sender_currency_rate = $sender_wallet->currency->rate;
        $min_amount = $trx_charges->min_limit * $sender_currency_rate;
        $max_amount = $trx_charges->max_limit * $sender_currency_rate;

        if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
            return back()->with(['error' => [__("Please follow the transaction limit")]]);
        }

        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('user_id',$sender_wallet->user->id,PaymentGatewayConst::TYPETRANSFERMONEY,$sender_wallet->currency,$validated['sender_amount'],$trx_charges,PaymentGatewayConst::SEND);
        }catch(Exception $e){
           $errorData = json_decode($e->getMessage(), true);
            return back()->with(['error' => [__($errorData['message'] ?? __("Something went wrong! Please try again."))]]);
        }

        $receiver = User::notAuth()->where(function($query) use ($credentials) {
            $query->where('email', $credentials)
                  ->orWhere('mobile', (int)$credentials)
                  ->orWhere('full_mobile', $credentials);
        })->active()->first();
        if(!$receiver) return back()->with(['error' => [__("Receiver doesn't exists or Receiver is temporary banned")]]);

        $receiver_wallet = UserWallet::where("user_id",$receiver->id)->whereHas("currency",function($q) use ($receiver_currency){
            $q->receiver()->where("code",$receiver_currency->code);
        })->first();

        if(!$receiver_wallet) return back()->with(['error' => [__("Receiver wallet not available")]]);

        if($charges['payable'] > $sender_wallet->balance) return back()->with(['error' => [__("Your Wallet Balance Is Insufficient")]]);

        try{
            $trx_id = $this->trx_id;
            $sender = $this->insertSender($trx_id,$user,$sender_wallet,$charges,$receiver_wallet,$validated['remark']);
            if($sender){
                 $this->insertSenderCharges($sender,$charges,$user,$receiver);
                try{
                    if( $basic_setting->email_notification == true){
                        $notifyDataSender = [
                            'trx_id'            => $trx_id,
                            'title'             => __("Send Money to")." @" . @$receiver->username." (".$receiver->email ?? $receiver->full_mobile.")",
                            'request_amount'    => get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']),
                            'payable'           => get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit']),
                            'charges'           => get_amount( $charges['total_charge'],$charges['sender_currency'],$charges['precision_digit']),
                            'exchange_rate'     => get_amount(1,$charges['sender_currency']).' = '. get_amount($charges['exchange_rate'],$charges['receiver_currency'],$charges['r_precision_digit']),
                            'received_amount'   => get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),
                            'status'            => __("success"),
                        ];
                        //sender notifications
                        $user->notify(new SenderMail($user,(object)$notifyDataSender));
                    }
                 }catch(Exception $e){}

                 //sms notification
                 try{
                    //sender sms
                    if( $basic_setting->sms_notification == true){
                        sendSms($user,'SEND_MONEY',[
                            'amount'        => get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']),
                            'charge'        => get_amount( $charges['total_charge'],$charges['sender_currency'],$charges['precision_digit']),
                            'to_user'       => $receiver->username.' ( '.$receiver->fullname.' )',
                            'trx'           => $trx_id,
                            'time'          => now()->format('Y-m-d h:i:s A'),
                            'balance'       => get_amount($sender_wallet->balance,$sender_wallet->currency->code,$charges['precision_digit']),
                        ]);
                    }
                 }catch(Exception $e){}
            }
            $receiverTrans = $this->insertReceiver($trx_id,$user,$sender_wallet,$charges,$receiver_wallet,$validated['remark']);
            if($receiverTrans){
                 $this->insertReceiverCharges($receiverTrans,$charges,$user,$receiver);
                 //Receiver notifications
                 try{
                    if( $basic_setting->email_notification == true){
                        $notifyDataReceiver = [
                            'trx_id'            => $trx_id,
                            'title'             => __("Received Money from")." @" .@$user->username." (".$user->fullname.")",
                            'received_amount'   => get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),
                            'status'            => __("success"),
                        ];
                        //send notifications
                        $receiver->notify(new ReceiverMail($receiver,(object)$notifyDataReceiver));
                    }
                 }catch(Exception $e){}
                 //sms notification
                 try{
                    //Receiver sms
                    if( $basic_setting->sms_notification == true){
                        sendSms($receiver,'SEND_MONEY_RECEIVE',[
                            'amount'        => get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),
                            'from_user'     => $user->username.' ( '.$user->email ?? $user->full_mobile .' )',
                            'trx'           => $trx_id,
                            'time'          =>  now()->format('Y-m-d h:i:s A'),
                            'balance'       => get_amount($receiver_wallet->balance,$receiver_wallet->currency->code,$charges['r_precision_digit']),
                        ]);
                    }
                 }catch(Exception $e){}
            }
            //admin notification
            $this->adminNotification($trx_id,$charges,$user,$receiver);
            return redirect()->route("user.send.money.index")->with(['success' => [__('Send Money successful to').' '.$receiver->fullname]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

    }
    //admin notification
    public function adminNotification($trx_id,$charges,$user,$receiver){

        $notification_content = [
            //email notification
            'subject' =>__("Send Money"),
            'greeting' =>__("Send Money Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$user->email."<br>".__("Receiver").": @".$receiver->email."<br>".__("request Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__('Exchange Rate')." : ".get_amount(1,$charges['sender_currency']).' = '. get_amount($charges['exchange_rate'],$charges['receiver_currency'],$charges['precision_digit'])."<br>".__("Recipient Received")." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit'])."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Send Money")." ".__('Successful'),
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$user->email." ".__("Receiver").": @".$receiver->email." ".__("Sender Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit'])." ".__("Receiver Amount")." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),

            //admin db notification
            'notification_type' =>  NotificationConst::TRANSFER_MONEY,
            'trx_id' =>  $trx_id,
            'admin_db_title' => "Send Money"." ".'Successful'." ".get_amount($charges['sender_amount'],$charges['sender_currency'])." (".$trx_id.")",
            'admin_db_message' =>"Sender".": @".$user->email.","."Receiver".": @".$receiver->email.","."Sender Amount"." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']).","."Receiver Amount"." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit'])
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.send.money.index','admin.send.money.export.data'])
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
    //sender transaction
    public function insertSender($trx_id,$user,$sender_wallet,$charges,$receiver_wallet,$remark) {
        $authWallet = $sender_wallet;
        $afterCharge = ($authWallet->balance - $charges['payable']);

        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => $remark??"",
                'details'                       => json_encode([
                                                                'receiver_email'    => $receiver_wallet->user->email,
                                                                'receiver_username' => $receiver_wallet->user->username,
                                                                'sender_email'      => $sender_wallet->user->email,
                                                                'sender_username'   => $sender_wallet->user->username,
                                                                'charges'           => $charges
                                                    ]),
                'attribute'                     => PaymentGatewayConst::SEND,
                'status'                        => true,
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
    public function insertSenderCharges($id,$charges,$user,$receiver) {
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

            //store notification
            $notification_content = [
                'title'         => __("Send Money"),
                'message'       => __('Transfer Money to')." ".$receiver->fullname.' ' .get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit'])." ".__('Successful'),
                'image'         =>  get_image($user->image,'user-profile'),
            ];
            UserNotification::create([
                'type'      =>  NotificationConst::TRANSFER_MONEY,
                'user_id'   =>  $user->id,
                'message'   =>  $notification_content,
            ]);

            //push notification
          if( $this->basic_settings->push_notification == true){
                try{
                        (new PushNotificationHelper())->prepare([$user->id],[
                            'title' => $notification_content['title'],
                            'desc'  => $notification_content['message'],
                            'user_type' => 'user',
                        ])->send();
                }catch(Exception $e) {}
            }

            DB::commit();

        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$user,$sender_wallet,$charges,$receiver_wallet,$remark) {
        $trx_id = $trx_id;
        $receiverWallet = $receiver_wallet;
        $recipient_amount = ($receiverWallet->balance + $charges['receiver_amount']);

        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $receiver_wallet->user->id,
                'user_wallet_id'                => $receiverWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $recipient_amount,
                'remark'                        => $remark??"",
                'details'                       => json_encode([
                                                                'receiver_email'    => $receiver_wallet->user->email,
                                                                'receiver_username' => $receiver_wallet->user->username,
                                                                'sender_email'      => $sender_wallet->user->email,
                                                                'sender_username'   => $sender_wallet->user->username,
                                                                'charges'           => $charges
                                                            ]),
                'attribute'                      => PaymentGatewayConst::RECEIVED,
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
    public function insertReceiverCharges($id,$charges,$user,$receiver) {
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
                'title'         => __("Send Money"),
                'message'       => __('Transfer Money from')." ".$user->fullname.' ' .get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit'])." ".__('Successful'),
                'image'         => get_image($receiver->image,'user-profile'),
            ];
            UserNotification::create([
                'type'          => NotificationConst::TRANSFER_MONEY,
                'user_id'       => $receiver->id,
                'message'       => $notification_content,
            ]);
            DB::commit();

            //push notification
            if( $this->basic_settings->push_notification == true){
                try{
                    (new PushNotificationHelper())->prepare([$receiver->id],[
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
    public function transferCharges($sender_amount,$charges,$sender_wallet,$receiver_currency) {
        $sPrecision = get_wallet_precision($sender_wallet->currency);
        $rPrecision = get_wallet_precision($receiver_currency);
        $exchange_rate =$receiver_currency->rate / $sender_wallet->currency->rate;

        $data['exchange_rate']          =   $exchange_rate;
        $data['sender_amount']          =   $sender_amount;
        $data['sender_currency']        =   $sender_wallet->currency->code;
        $data['receiver_amount']        =   $sender_amount * $exchange_rate;
        $data['receiver_currency']      =   $receiver_currency->code;
        $data['percent_charge']         =   ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']           =   $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']           =   $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']  =   $sender_wallet->balance;
        $data['payable']                =   $sender_amount + $data['total_charge'];
        $data['precision_digit']        =   $sPrecision;
        $data['r_precision_digit']      =   $rPrecision;

        return $data;
    }
}
