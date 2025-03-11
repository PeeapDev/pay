<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\Merchants\Merchant;
use App\Models\Merchants\MerchantNotification;
use App\Models\Merchants\MerchantQrCode;
use App\Models\Merchants\MerchantWallet;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use App\Notifications\User\MakePayment\ReceiverMail;
use App\Notifications\User\MakePayment\SenderMail;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class MakePaymentController extends Controller
{
    protected  $trx_id;
    protected $basic_settings;

    public function __construct()
    {
        $this->trx_id = 'SM'.getTrxNum();
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function makePaymentInfo(){
        $makePaymentCharge = TransactionSetting::where('slug','make-payment')->where('status',1)->get()->map(function($data){
            return[
                'id'                        => $data->id,
                'slug'                      => $data->slug,
                'title'                     => $data->title,
                'fixed_charge'              => get_amount($data->fixed_charge,null,get_wallet_precision()),
                'percent_charge'            => get_amount($data->percent_charge,null,get_wallet_precision()),
                'min_limit'                 => get_amount($data->min_limit,null,get_wallet_precision()),
                'max_limit'                 => get_amount($data->max_limit,null,get_wallet_precision()),
                'daily_limit'               => get_amount($data->daily_limit,null,get_wallet_precision()),
                'monthly_limit'             => get_amount($data->monthly_limit,null,get_wallet_precision()),
            ];
        })->first();
        $transactions = Transaction::auth()->makePayment()->latest()->take(10)->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
                if($item->attribute == payment_gateway_const()::SEND){
                    $receiver = $item->details->receiver->email??$item->details->receiver_email;
                    return[
                        'id'                    => @$item->id,
                        'type'                  =>$item->attribute,
                        'trx'                   => @$item->trx_id,
                        'transaction_type'      => $item->type,
                        'transaction_heading'   => __("Make Payment to")." (@" . $receiver.")",
                        'request_amount'        => get_amount($item->details->charges->sender_amount??$item->request_amount,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'payable'               => get_amount($item->details->charges->payable??$item->payable,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'total_charge'          => get_amount($item->charge->total_charge,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'exchange_rate'         => get_amount(1,$item->details->charges->sender_currency??get_default_currency_code())." = ".get_amount($item->details->charges->exchange_rate??get_default_currency_rate(),$item->details->charges->receiver_currency??get_default_currency_code(),$item->details->charges->r_precision_digit??2),
                        'recipient_received'    => get_amount($item->details->charges->receiver_amount??$item->details->recipient_amount,$item->details->charges->receiver_currency??get_default_currency_code(),$item->details->charges->r_precision_digit??2),
                        'current_balance'       => get_amount($item->available_balance,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'status'                => @$item->stringStatus->value,
                        'status_value'          => @$item->status,
                        'date_time'             => @$item->created_at,
                        'status_info'           =>(object)@$statusInfo,
                    ];
                }

        });

        $get_remaining_fields = [
            'transaction_type'  =>  PaymentGatewayConst::TYPEMAKEPAYMENT,
            'attribute'         =>  PaymentGatewayConst::SEND,
        ];

        $data =[
            'base_curr'             => get_default_currency_code(),
            'base_curr_rate'        => get_amount(get_default_currency_rate(),null,get_wallet_precision()),
            'get_remaining_fields'  => (object) $get_remaining_fields,
            'makePaymentCharge'     => (object)$makePaymentCharge,
            'transactions'          => $transactions,
        ];
        $message =  ['success'=>[__('Make Payment Information')]];
        return Helpers::success($data,$message);
    }
    public function checkMerchant(Request $request){
        $validator = Validator::make(request()->all(), [
            'credentials'     => "required",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $credentials = $request->credentials;
        $exist = Merchant::where(function($query) use ($credentials) {
            $query->where('email', $credentials)
                  ->orWhere('mobile',(int)$credentials)
                  ->orWhere('full_mobile', $credentials);
        })->active()->first();
        if( !$exist){
            $error = ['error'=>[__('Merchant not found')]];
            return Helpers::error($error);
        }
        $user = auth()->user();
        if(@$exist && @$user->email == @$exist->email || @$user->full_mobile == @$exist->full_mobile){
             $error = ['error'=>[__("Can't transfer/request to your own")]];
            return Helpers::error($error);
        }
        $data =[
            'exist_merchant'   => $exist,
            ];
        $message =  ['success'=>[__('Valid merchant for transaction.')]];
        return Helpers::success($data,$message);
    }
    public function qrScan(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'qr_code'     => "required",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $qr_code = $request->qr_code;
        $qrCode = MerchantQrCode::where('qr_code',$qr_code)->first();
        if(!$qrCode){
            $error = ['error'=>[__('Invalid Request')]];
            return Helpers::error($error);
        }
        $user = Merchant::where('id',$qrCode->merchant_id)->active()->first();
        if(!$user){
            $error = ['error'=>[__('Merchant not found')]];
            return Helpers::error($error);
        }
        if( $user->email == auth()->user()->email){
            $error = ['error'=>[__("Can't transfer/request to your own")]];
            return Helpers::error($error);
        }
        $data =[
            'merchant_email' => $user->email??$user->full_mobile,
            ];
        $message =  ['success'=>[__('QR Scan Result.')]];
        return Helpers::success($data,$message);
    }
    public function confirmedPayment(Request $request){
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
        $user = auth()->user();
        $credentials = $validated['credentials'];

        $sender_wallet = UserWallet::where('user_id',$user->id)->active()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['sender_wallet'])->active();
        })->active()->first();
        if(!$sender_wallet){
            $error = ['error'=>[__('User wallet not found')]];
            return Helpers::error($error);
        }

        $receiver_currency = Currency::active()->where('code',$validated['receiver_wallet'])->first();
        if(!$receiver_currency){
            $error = ['error'=>[__('Receiver Currency Not Found')]];
            return Helpers::error($error);
        }

        $trx_charges = TransactionSetting::where('slug','make-payment')->where('status',1)->first();
        $charges = $this->transferCharges($validated['sender_amount'],$trx_charges,$sender_wallet,$receiver_currency);

        $sender_currency_rate = $sender_wallet->currency->rate;
        $min_amount = $trx_charges->min_limit * $sender_currency_rate;
        $max_amount = $trx_charges->max_limit * $sender_currency_rate;

        if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }

        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('user_id',$sender_wallet->user->id,PaymentGatewayConst::TYPEMAKEPAYMENT,$sender_wallet->currency,$validated['sender_amount'],$trx_charges,PaymentGatewayConst::SEND);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
            $error = ['error'=>[__($errorData['message'] ?? __("Something went wrong! Please try again."))]];
            return Helpers::error($error);
        }

        $receiver = Merchant::where(function($query) use ($credentials) {
            $query->where('email', $credentials)
                  ->orWhere('mobile',(int)$credentials)
                  ->orWhere('full_mobile', $credentials);
        })->active()->first();
        if(!$receiver){
            $error = ['error'=>[__("Receiver doesn't exists or Receiver is temporary banned")]];
            return Helpers::error($error);
        }

        $receiver_wallet = MerchantWallet::where("merchant_id",$receiver->id)->whereHas("currency",function($q) use ($receiver_currency){
            $q->receiver()->where("code",$receiver_currency->code);
        })->first();
        if(!$receiver_wallet){
            $error = ['error'=>[__("Receiver wallet not available")]];
            return Helpers::error($error);
        }

        if( $user->email == $receiver->email || $user->full_mobile == $receiver->full_mobile ){
            $error = ['error'=>[__("Can't payment to your own")]];
            return Helpers::error($error);
        }

        if($charges['payable'] > $sender_wallet->balance){
            $error = ['error'=>[__("Your Wallet Balance Is Insufficient")]];
            return Helpers::error($error);
        }


        try{
            $trx_id = $this->trx_id;
            $sender = $this->insertSender($trx_id,$user,$sender_wallet,$charges,$receiver_wallet,$validated['remark']);
            if($sender){
                 $this->insertSenderCharges($sender,$charges,$user,$receiver);
            }
            //Sender notifications
            try{
                if( $basic_setting->email_notification == true){
                    $notifyDataSender = [
                        'trx_id'            => $trx_id,
                        'title'             => __("Make Payment to")." @" . @$receiver->username." (".@$receiver->fullname.")",
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
             // setup sender sms
             if( $basic_setting->sms_notification == true){
                try{
                    sendSms($user,'MAKE_PAYMENT',[
                        'to_user'   => $receiver->username.'('.$receiver->fullname.')',
                        'amount'    => get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']),
                        'charge'    => get_amount( $charges['total_charge'],$charges['sender_currency'],$charges['precision_digit']),
                        'trx'       => $trx_id,
                        'time'      => now()->format('Y-m-d h:i:s A'),
                    ]);
                }catch(Exception $e){}
            }

            $receiverTrans = $this->insertReceiver($trx_id,$user,$sender_wallet,$charges,$receiver_wallet,$validated['remark']);
            if($receiverTrans){
                 $this->insertReceiverCharges($receiverTrans,$charges,$user,$receiver);
            }
            try{
                if( $basic_setting->email_notification == true){
                    //Receiver notifications
                    $notifyDataReceiver = [
                        'trx_id'            => $trx_id,
                        'title'             => __("Make Payment From")." @" .@$user->username." (".@$user->fullname.")",
                        'received_amount'   => get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),
                        'status'            => __("success"),
                    ];
                    //send notifications
                    $receiver->notify(new ReceiverMail($receiver,(object)$notifyDataReceiver));
                }
            }catch(Exception $e){}
             // setup receiver notifications sms
             if( $basic_setting->merchant_sms_notification == true){
                try{
                    sendSms($receiver,'MAKE_PAYMENT_MERCHANT',[
                        'from_user'     => $user->username.'('.$user->fullname.' )',
                        'amount'        => get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),
                        'trx'           => $trx_id,
                        'time'          => now()->format('Y-m-d h:i:s A'),
                    ]);
                }catch(Exception $e){}
            }
            $this->adminNotification($trx_id,$charges,$user,$receiver);
            $message = ['success'=>[__('Make Payment successful to').' '.$receiver->fullname]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }

    //sender transaction
    public function insertSender($trx_id,$user,$sender_wallet,$charges,$receiver_wallet,$remark) {
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge = ($authWallet->balance -  $charges['payable']);

        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPEMAKEPAYMENT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => $remark??"",
                'details'                       => json_encode([
                                                    'receiver_email'    => $receiver_wallet->merchant->email,
                                                    'receiver_username' => $receiver_wallet->merchant->username,
                                                    'sender_email'      => $sender_wallet->user->email,
                                                    'sender_username'   => $sender_wallet->user->username,
                                                    'charges'           => $charges
                                                ]),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => true,
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
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($id,$charges,$user,$receiver){
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
               'percent_charge'     =>  $charges['percent_charge'],
                'fixed_charge'      =>  $charges['fixed_charge'],
                'total_charge'      =>  $charges['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Make Payment"),
                'message'       => __("Payment To ")." ".$receiver->fullname.' ' .get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit'])." ".__('Successful'),
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::MAKE_PAYMENT,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            if( $this->basic_settings->push_notification == true){
                try{
                    (new PushNotificationHelper())->prepareApi([$user->id],[
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
    public function insertReceiver($trx_id,$user,$sender_wallet,$charges,$receiver_wallet,$remark) {
        $trx_id = $trx_id;
        $receiverWallet = $receiver_wallet;
        $recipient_amount = ($receiverWallet->balance + $charges['receiver_amount']);

        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'merchant_id'                   => $receiver_wallet->merchant->id,
                'merchant_wallet_id'            => $receiverWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPEMAKEPAYMENT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $recipient_amount,
                'remark'                        => $remark??"",
                'details'                       => json_encode([
                                                    'receiver_email'    => $receiver_wallet->merchant->email,
                                                    'receiver_username' => $receiver_wallet->merchant->username,
                                                    'sender_email'      => $sender_wallet->user->email,
                                                    'sender_username'   => $sender_wallet->user->username,
                                                    'charges'           => $charges
                                                ]),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);

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
    public function insertReceiverCharges($id,$charges,$user,$receiver) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    =>  0,
                'fixed_charge'      =>  0,
                'total_charge'      =>  0,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Make Payment"),
                'message'       => __("Payment From")." ".$user->fullname.' ' .get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit'])." ".__('Successful'),
                'image'         => get_image($receiver->image,'merchant-profile'),
            ];

            MerchantNotification::create([
                'type'      => NotificationConst::MAKE_PAYMENT,
                'merchant_id'  => $receiver->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            if( $this->basic_settings->merchant_push_notification == true){
                try{
                    (new PushNotificationHelper())->prepareApi([$receiver->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'merchant',
                    ])->send();
                }catch(Exception $e) {}
            }
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    //admin notification
    public function adminNotification($trx_id,$charges,$user,$receiver){
        $notification_content = [
            //email notification
            'subject' => __("Make Payment to")." @" . @$receiver->username." (".@$receiver->email.")",
            'greeting' =>__("Make Payment Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$user->email."<br>".__("Receiver").": @".$receiver->email."<br>".__("Sender Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__('Exchange Rate')." : ".get_amount(1,$charges['sender_currency']).' = '. get_amount($charges['exchange_rate'],$charges['receiver_currency'],$charges['precision_digit'])."<br>".__("Recipient Received")." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit'])."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Make Payment")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$user->email." ".__("Receiver").": @".$receiver->email." ".__("Sender Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit'])." ".__("Receiver Amount")." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit']),

            //admin db notification
            'notification_type' =>  NotificationConst::MAKE_PAYMENT,
            'admin_db_title' => "Make Payment"." (".userGuard()['type'].")",
            'admin_db_message' =>"Sender".": @".$user->email.","."Receiver".": @".$receiver->email.","."Sender Amount"." : ".get_amount($charges['sender_amount'],$charges['sender_currency'],$charges['precision_digit']).","."Receiver Amount"." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'],$charges['r_precision_digit'])
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.make.payment.index','admin.make.payment.export.data'])
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
    public function transferCharges($sender_amount,$charges,$sender_wallet,$receiver_currency) {
        $sPrecision = get_wallet_precision($sender_wallet->currency);
        $rPrecision = get_wallet_precision($receiver_currency);
        $exchange_rate =  $receiver_currency->rate / $sender_wallet->currency->rate;

        $data['exchange_rate']          = $exchange_rate;
        $data['sender_amount']          = $sender_amount;
        $data['sender_currency']        = $sender_wallet->currency->code;
        $data['receiver_amount']        = $sender_amount * $exchange_rate;
        $data['receiver_currency']      = $receiver_currency->code;
        $data['percent_charge']         = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']           = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']           = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']  = $sender_wallet->balance;
        $data['payable']                =  $sender_amount + $data['total_charge'];
        $data['precision_digit']        = $sPrecision;
        $data['r_precision_digit']      = $rPrecision;

        return $data;
    }
}
