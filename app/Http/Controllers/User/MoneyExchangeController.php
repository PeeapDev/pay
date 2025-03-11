<?php

namespace App\Http\Controllers\User;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MoneyExchangeController extends Controller
{
    public function index(Request $request) {
        $page_title   = __("Money Exchange");
        $user_wallets = UserWallet::where('user_id', auth()->user()->id)->whereHas('currency',function($q){
            $q->where('status',GlobalConst::ACTIVE);
        })->with('currency:id,code,name,flag,rate,type,symbol,country')->get();
        $transactions = Transaction::auth()->where([ 'type' => PaymentGatewayConst::TYPEMONEYEXCHANGE])->latest()->take(10)->get();
        $charges      = TransactionSetting::where("slug",'money_exchange')->first();
        return view('user.sections.money-exchange.index',compact('page_title','user_wallets','charges','transactions'));
    }
    public function moneyExchangeSubmit(Request $request) {
        if(Auth::guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
        }
        $basic_setting = BasicSettings::first();
        $validator = Validator::make($request->all(),[
            'exchange_from_amount'   => 'required|numeric|gt:0',
            'exchange_from_currency' => 'required|string|exists:currencies,code',
            'exchange_to_currency'   => 'required|string|exists:currencies,code',
            'exchange_to_amount'     => 'required|numeric|gt:0',
        ]);
        $validated = $validator->validate();
        $user_from_wallet = UserWallet::where('user_id', auth()->user()->id)->whereHas("currency",function($q) use ($validated){
            $q->where("code",$validated['exchange_from_currency']);
        })->first();
        if(!$user_from_wallet) return back()->with(['error' => ['From wallet('.$validated['exchange_from_currency'].') doesn\'t exists']]);
        $user_to_wallet = UserWallet::where('user_id', auth()->user()->id)->whereHas("currency",function($q) use ($validated){
            $q->where("code",$validated['exchange_to_currency']);
        })->first();
        if(!$user_to_wallet) return back()->with(['error' => ['To exchange wallet('.$validated['exchange_to_currency'].') doesn\'t exists']]);
        $charges = TransactionSetting::where("slug",'money_exchange')->first();
        if(!$charges) return back()->with(['error' => [__("Exchange money isn't available right now")]]);
        if($user_from_wallet->id === $user_to_wallet->id) {
            return back()->with(['error' => [__("Can't exchange money with same currency")]]);
        }
        $chargeCalculate = $this->exchangeChargeCalc($validated['exchange_from_amount'],$charges,$user_from_wallet,$user_to_wallet);

        // Check transaction limit
        $sender_currency_rate = $user_from_wallet->currency->rate;
        $min_amount           = $charges->min_limit * $sender_currency_rate;
        $max_amount           = $charges->max_limit * $sender_currency_rate;


        if($validated['exchange_from_amount'] < $min_amount || $validated['exchange_from_amount'] > $max_amount) {
            return back()->with(['error' => [__("Please follow the transaction limit")]]);
        }

        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('user_id',$user_from_wallet->user->id,PaymentGatewayConst::TYPEMONEYEXCHANGE,$user_from_wallet->currency,$validated['exchange_from_amount'],$charges,PaymentGatewayConst::SEND);
        }catch(Exception $e){
           $errorData = json_decode($e->getMessage(), true);
            return back()->with(['error' => [__($errorData['message'] ?? __("Something went wrong! Please try again."))]]);
        }

        if($chargeCalculate->payable > $chargeCalculate->from_wallet_balance) return back()->with(['error' => [__("Your Wallet Balance Is Insufficient")]]);
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
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }
        return back()->with(['success' => [__('Money exchange successful')]]);
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
            'subject' => "Exchange Money From ". get_amount($data['requestData']['exchange_from_amount'],$data['requestData']['exchange_from_currency'],$data['chargeCalculate']->precision_digit).' '.' To '.get_amount($data['requestData']['exchange_to_amount'],$data['requestData']['exchange_to_currency'],$data['chargeCalculate']->r_precision_digit),
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
