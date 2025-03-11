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
use App\Models\Admin\TransactionSetting;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\ExchangeMoney\ExchangeMoney;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MoneyExchangeController extends Controller
{
    public function index(Request $request) {
        $transactions = Transaction::auth()->where([ 'type' => PaymentGatewayConst::TYPEMONEYEXCHANGE])->latest()->take(10)->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
                return[
                    'id' => @$item->id,
                    'type' =>$item->attribute,
                    'trx' => @$item->trx_id,
                    'transaction_type' => $item->type,
                    'transaction_heading' => $item->details->charges->from_wallet_country .' '.__("to").' '.$item->details->charges->to_wallet_country,
                    'request_amount' => get_amount($item->request_amount,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'payable' => get_amount($item->payable,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'exchange_rate' => get_amount(1, $item->creator_wallet->currency->code ) .' = ' . get_amount($item->details->charges->exchange_rate,$item->details->charges->exchange_currency,$item->details->charges->r_precision_digit??2) ,
                    'total_charge' => get_amount($item->charge->total_charge,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'exchangeable_amount' => get_amount($item->details->charges->exchange_amount,$item->details->charges->exchange_currency,$item->details->charges->r_precision_digit??2),
                    'current_balance' => get_amount($item->available_balance,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'status' => @$item->stringStatus->value,
                    'status_value' => @$item->status,
                    'date_time' => @$item->created_at,
                    'status_info' =>(object)@$statusInfo,
                ];

        });
        $charges   = TransactionSetting::where("slug",'money_exchange')->get()->map(function($data){
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
            ];
        })->first();
        $get_remaining_fields = [
            'transaction_type'  =>  PaymentGatewayConst::TYPEMONEYEXCHANGE,
            'attribute'         =>  PaymentGatewayConst::SEND,
        ];

        $data =[
            'base_curr'             => get_default_currency_code(),
            'base_curr_rate'        => get_amount(get_default_currency_rate(),null,get_wallet_precision()),
            'get_remaining_fields'  => (object) $get_remaining_fields,
            'charges'               => (object)$charges,
            'transactions'          => $transactions
        ];
        $message =  ['success'=>[__('Money Exchange')]];
        return Helpers::success($data,$message);
    }
    public function moneyExchangeSubmit(Request $request) {

        $user = authGuardApi()['user'];
        $basic_setting = BasicSettings::first();
        $validator = Validator::make($request->all(),[
            'exchange_from_amount'   => 'required|numeric|gt:0',
            'exchange_from_currency' => 'required|string|exists:currencies,code',
            'exchange_to_currency'   => 'required|string|exists:currencies,code',
            'exchange_to_amount'     => 'required|numeric|gt:0',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();


        $user_from_wallet = UserWallet::where('user_id', auth()->user()->id)->whereHas("currency",function($q) use ($validated){
            $q->where("code",$validated['exchange_from_currency']);
        })->first();
        if(!$user_from_wallet){
            $error = ['error'=>['From wallet('.$validated['exchange_from_currency'].') doesn\'t exists']];
            return Helpers::error($error);
        }
        $user_to_wallet = UserWallet::where('user_id', auth()->user()->id)->whereHas("currency",function($q) use ($validated){
            $q->where("code",$validated['exchange_to_currency']);
        })->first();
        if(!$user_to_wallet){
            $error = ['error'=>['To exchange wallet('.$validated['exchange_to_currency'].') doesn\'t exists']];
            return Helpers::error($error);
        }
        $charges = TransactionSetting::where("slug",'money_exchange')->first();
        if(!$charges){
            $error = ['error'=>[__("Exchange money isn't available right now")]];
            return Helpers::error($error);
        }
        if($user_from_wallet->id === $user_to_wallet->id){
            $error = ['error'=>[__("Can't exchange money with same currency")]];
            return Helpers::error($error);
        }
        $chargeCalculate = $this->exchangeChargeCalc($validated['exchange_from_amount'],$charges,$user_from_wallet,$user_to_wallet);

        // Check transaction limit
        $sender_currency_rate = $user_from_wallet->currency->rate;
        $min_amount           = $charges->min_limit * $sender_currency_rate;
        $max_amount           = $charges->max_limit * $sender_currency_rate;

        if($validated['exchange_from_amount'] < $min_amount || $validated['exchange_from_amount'] > $max_amount) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }

        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('user_id',$user_from_wallet->user->id,PaymentGatewayConst::TYPEMONEYEXCHANGE,$user_from_wallet->currency,$validated['exchange_from_amount'],$charges,PaymentGatewayConst::SEND);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
            $error = ['error'=>[__($errorData['message'] ?? __("Something went wrong! Please try again."))]];
            return Helpers::error($error);
        }
        if($chargeCalculate->payable > $chargeCalculate->from_wallet_balance){
            $error = ['error'=>[__("Your Wallet Balance Is Insufficient")]];
            return Helpers::error($error);
        }
        $trx_id = 'ME'.getTrxNum();
        // Transaction Start
        DB::beginTransaction();
        try{
            $inserted_id = DB::table("transactions")->insertGetId([
                'user_id'               => auth()->user()->id,
                'user_wallet_id'        => $user_from_wallet->id,
                'type'                  => PaymentGatewayConst::TYPEMONEYEXCHANGE,
                'trx_id'                => $trx_id,
                'request_amount'        => $chargeCalculate->request_amount,
                'payable'               => $chargeCalculate->payable,
                'available_balance'     => $user_from_wallet->balance - $chargeCalculate->payable,
                'details'               => json_encode([
                                                'charges' => $chargeCalculate,
                                            ]),
                'attribute'             => PaymentGatewayConst::SEND,
                'status'                => true,
                'created_at'            => now(),
            ]);
            DB::table('transaction_charges')->insert([
                'transaction_id' => $inserted_id,
                'percent_charge' => $chargeCalculate->percent_charge,
                'fixed_charge'   => $chargeCalculate->fixed_charge,
                'total_charge'   => $chargeCalculate->total_charge,
                'created_at'     => now(),
            ]);
                 // notification
                 $notification_content = [
                    'title'   => "Exchange Money",
                    'message' => "Exchange Money From ".get_amount($chargeCalculate->request_amount,$validated['exchange_from_currency'],$chargeCalculate->precision_digit)." to ".get_amount($chargeCalculate->exchange_amount,$validated['exchange_to_currency'],$chargeCalculate->r_precision_digit),
                    'time'    => now(),
                    'image'  =>  get_image($user->image,'user-profile'),
                ];

                UserNotification::create([
                    'type'    => NotificationConst::EXCHANGE_MONEY,
                    'user_id' => auth()->user()->id,
                    'message' => $notification_content,
                ]);
                $output = [
                    'chargeCalculate' => $chargeCalculate,
                    'requestData' => $validated,
                ];
                if( $basic_setting->email_notification == true){
                    try{
                        $user->notify(new ExchangeMoney($user,$output,$trx_id));
                    }catch(Exception $e){}

                }
                if($basic_setting->sms_notification == true){
                    try{
                        sendSms($user,'MONEY_EXCHANGE',[
                            'from_amount'   => get_amount($chargeCalculate->request_amount,$validated['exchange_from_currency'],$chargeCalculate->precision_digit),
                            'to_amount'     => get_amount($chargeCalculate->exchange_amount,$validated['exchange_to_currency'],$chargeCalculate->r_precision_digit),
                            'trx'           => $trx_id,
                            'time'          => now()->format('Y-m-d h:i:s A')
                        ]);
                    }catch(Exception $e){}

                }
                //Push Notifications
                try {
                    if($basic_setting->push_notification == true){
                        //push notification
                        (new PushNotificationHelper())->prepare([$user->id],[
                            'title' => $notification_content['title'],
                            'desc'  => $notification_content['message'],
                            'user_type' => 'user',
                        ])->send();
                    }
                } catch (Exception $e) {}

            $user_from_wallet->balance -= $chargeCalculate->payable;
            $user_from_wallet->save();
            $user_to_wallet->balance += $chargeCalculate->exchange_amount;
            $user_to_wallet->save();
            //admin notification
            $this->adminNotification($output,$trx_id,$user);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Money exchange successful')]];
        return Helpers::onlysuccess($message);
    }
    public function exchangeChargeCalc($enter_amount,$charges,$from_wallet,$to_wallet) {
        $sPrecision = get_wallet_precision($from_wallet->currency);
        $rPrecision = get_wallet_precision($to_wallet->currency);

        $exchange_rate                  = $to_wallet->currency->rate / $from_wallet->currency->rate;
        $data['exchange_rate']          = $exchange_rate;
        //request amount
        $data['request_amount']         = $enter_amount;
        $data['request_currency']       = $from_wallet->currency->code;
        $data['exchange_currency']      = $to_wallet->currency->code;
        //exchange charge calculate
        $percent_charge                 = $charges->percent_charge ?? 0;
        $data['percent_charge']         = ($enter_amount / 100) * $percent_charge;
        $fixed_charge                   = $charges->fixed_charge ?? 0;
        $data['fixed_charge']           = $from_wallet->currency->rate * $fixed_charge;
        $data['total_charge']           = $data['percent_charge'] + $data['fixed_charge'];
        //user wallet check
        $data['from_wallet_balance']    = $from_wallet->balance;
        $data['from_wallet_country']    = $from_wallet->currency->name;
        $data['to_wallet_balance']      = $to_wallet->balance;
        $data['to_wallet_country']      = $to_wallet->currency->name;
        //exchange amount
        $data['payable']                = $enter_amount + $data['total_charge'];
        $data['exchange_amount']        =  $enter_amount * $data['exchange_rate'];
        $data['default_currency_amount']= ($enter_amount / $from_wallet->currency->rate);
        $data['sender_currency_rate']   = $from_wallet->currency->rate;
        $data['precision_digit']        = $sPrecision;
        $data['r_precision_digit']      = $rPrecision;

        return (object) $data;
    }
    //admin notification
    public function adminNotification($data,$trx_id,$user){
        $notification_content = [
            //email notification
            'subject' => "Exchange Money From ". get_amount($data['requestData']['exchange_from_amount'],$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit).' '.' To '.get_amount($data['requestData']['exchange_to_amount'],$data['requestData']['exchange_to_currency'],$data['chargeCalculate']->precision_digit),
            'greeting' => __("Exchange Money")." (".userGuard()['type'].")",
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("request Amount")." : ".get_amount($data['requestData']['exchange_from_amount'],$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit)."<br>".__("Fees & Charges")." : ".get_amount($data['chargeCalculate']->total_charge,$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit)."<br>".__("Total Payable Amount")." : ".get_amount($data['chargeCalculate']->payable,$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit)."<br>".__("Will Get")." : ".get_amount($data['requestData']['exchange_to_amount'],$data['requestData']['exchange_to_currency'],$data['chargeCalculate']->r_precision_digit)."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Exchange Money")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id." ".__("request Amount")." : ".get_amount($data['requestData']['exchange_from_amount'],$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit)." ".__("Total Payable Amount")." : ".get_amount($data['chargeCalculate']->payable,$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit),

            //admin db notification
            'notification_type' =>  NotificationConst::EXCHANGE_MONEY,
            'admin_db_title' => "Virtual Card Buy"." (".userGuard()['type'].")",
            'admin_db_message' => "Transaction ID"." : ".$trx_id.",".__("Request Amount")." : ".get_amount($data['requestData']['exchange_from_amount'],$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit).","."Total Payable Amount"." : ".get_amount($data['chargeCalculate']->payable,$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit)." (".$user->email.")",
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.virtual.card.logs','admin.virtual.card.export.data'])
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
