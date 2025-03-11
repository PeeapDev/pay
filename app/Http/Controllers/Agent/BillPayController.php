<?php

namespace App\Http\Controllers\Agent;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use App\Http\Helpers\UtilityHelper;
use App\Jobs\ProcessBillPayment;
use App\Models\Admin\TransactionSetting;
use App\Models\BillPayCategory;
use App\Models\Transaction;
use App\Notifications\User\BillPay\BillPayMail;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\Currency;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\BillPay\BillPayMailAutomatic;
use Illuminate\Support\Facades\Validator;

class BillPayController extends Controller
{
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function index() {
        $page_title = __("Bill Pay");
        $billPayCharge = TransactionSetting::where('slug','bill_pay')->where('status',1)->first();
        $billType = BillPayCategory::active()->orderByDesc('id')->get();
        try{
            $billers =  (new UtilityHelper())->getBillers([
                'size' => 500,
                'page' =>0
            ], false);
        }catch(Exception $e){

        }
        $contentArray = $billers["content"]??[];
        $billTypeArray = $billType->toArray();
        foreach ($billTypeArray as &$item) {
            $item['item_type'] = 'MANUAL';
        }
        foreach ($contentArray as &$item) {
            $item['item_type'] = 'AUTOMATIC';
        }
        $billTypeCollection = collect($billTypeArray);
        $mergedCollection = collect($contentArray)->merge($billTypeCollection);
        $billType = $mergedCollection;
        $sender_wallets = Currency::sender()->active()->get();
        $transactions = Transaction::agentAuth()->billPay()->latest()->take(10)->get();
        return view('agent.sections.bill-pay.index',compact("page_title",'billPayCharge','transactions','billType','sender_wallets'));
    }

    public function billPayConfirmed(Request $request){
        $validated = Validator::make($request->all(),[
            'bill_type'         => 'required|string',
            'bill_month'        => 'required|string',
            'bill_number'        => 'required|min:8',
            'amount'            => 'required|numeric|gt:0',
            'currency'          => 'required|exists:currencies,code',
            'biller_item_type'  => 'nullable',
        ])->validate();

        //start automatic functionaries
        if($validated['biller_item_type'] === "AUTOMATIC"){
            return $this->automaticBillPay($validated);
        }
        $user = userGuard()['user'];
        $sender_wallet = AgentWallet::auth()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['currency'])->active();
        })->active()->first();
        if(!$sender_wallet){
            return back()->with(['error' => [__('Agent wallet not found')]]);
        }
        $trx_charges = TransactionSetting::where('slug','bill_pay')->where('status',1)->first();;
        $charges = $this->billPayCharge($validated['amount'],$trx_charges,$sender_wallet);

        $bill_type = BillPayCategory::where('id', $validated['bill_type'])->first();
        if(!$bill_type){
            return back()->with(['error' => [__('Invalid bill type')]]);
        }
         // Check transaction limit
         $min_amount =  $trx_charges->min_limit / $charges['exchange_rate'];
         $max_amount =  $trx_charges->max_limit / $charges['exchange_rate'];
         if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
            return back()->with(['error' => [__("Please follow the transaction limit")]]);
         }

        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('agent_id',$sender_wallet->agent->id,PaymentGatewayConst::BILLPAY,$sender_wallet->currency,$validated['amount'],$trx_charges,PaymentGatewayConst::SEND);
        }catch(Exception $e){
           $errorData = json_decode($e->getMessage(), true);
            return back()->with(['error' => [__($errorData['message'] ?? __("Something went wrong! Please try again."))]]);
        }

         if($charges['payable'] > $sender_wallet->balance) {
            return back()->with(['error' => [__("Sorry, insufficient balance")]]);
         }

        try{
            $trx_id = 'BP'.getTrxNum();
            $sender = $this->insertSender($trx_id,$sender_wallet, $charges, $bill_type,$validated['bill_number'],$validated['biller_item_type'],$validated['bill_month']);
            $this->insertSenderCharges($sender,$charges,$sender_wallet);
            try{
                if( $this->basic_settings->agent_email_notification == true){
                    $notifyData = [
                        'trx_id'  => $trx_id,
                        'bill_type'  => @$bill_type->name,
                        'bill_number'  => @$validated['bill_number'],
                        'request_amount'   => $charges['sender_amount'],
                        'charges'   => $charges['total_charge'],
                        'payable'  => $charges['payable'],
                        'current_balance'  => get_amount($sender_wallet->balance,null,$charges['precision_digit']),
                        'status'  => __("Pending"),
                    ];
                    //send notifications
                    $user->notify(new BillPayMail($user,(object)$notifyData,$charges));
                }
            }catch(Exception $e){}
            //sms notification
            if( $this->basic_settings->agent_sms_notification == true){
                try{
                    sendSms($user,'BILL_PAY',[
                        'amount'        => get_amount($charges['conversion_amount'],$charges['sender_currency'],$charges['precision_digit']),
                        'type'          => $request->biller_item_type??'',
                        'bill_type'     => $bill_type->name??'',
                        'bill_number'   => $request->bill_number,
                        'month'         => $request->bill_month,
                        'trx'           => $trx_id,
                        'time'          =>  now()->format('Y-m-d h:i:s A')
                    ]);
                }catch(Exception $e) {}
            }
            //admin notification
            $this->adminNotificationManual($trx_id,$charges,$bill_type,$user,$request->all());
            return back()->with(['success' => [__("Bill pay request sent to admin successful")]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

    }
    public function insertSender($trx_id,$sender_wallet,$charges,$bill_type,$bill_number,$biller_item_type,$bill_month) {
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge = ($authWallet->balance -  $charges['payable']);
        $details =[
            'bill_type_id'      => $bill_type->id??'',
            'bill_type_name'    => $bill_type->name??'',
            'bill_number'       => $bill_number,
            'sender_amount'     => $charges['sender_amount']??0,
            'bill_month'        => $bill_month??'',
            'bill_type'         => $biller_item_type??'',
            'biller_info'       => [],
            'api_response'      => [],
            'charges'           => $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $sender_wallet->agent->id,
                'agent_wallet_id'               => $sender_wallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::BILLPAY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::BILLPAY," ")) . " Request To Admin",
                'details'                       => json_encode($details),
                'attribute'                     => PaymentGatewayConst::SEND,
                'status'                        => 2,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

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
    public function insertSenderCharges($id,$charges,$sender_wallet) {
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
                'title'         =>__("Bill Pay"),
                'message'       => __("Bill pay request send to admin")." " .$charges['sender_amount'].' '.$charges['wallet_currency']." ".__("Successful"),
                'image'         => get_image($sender_wallet->agent->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::BILL_PAY,
                'agent_id'  => $sender_wallet->agent->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            if( $this->basic_settings->agent_push_notification == true){
                try{
                    (new PushNotificationHelper())->prepare([$sender_wallet->agent->id],[
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
    public function billPayCharge($sender_amount,$charges,$sender_wallet) {
        $sPrecision = get_wallet_precision($sender_wallet->currency);
        $exchange_rate = get_default_currency_rate()/$sender_wallet->currency->rate;

        $data['exchange_rate' ]                     = $exchange_rate;
        $data['sender_amount']                      = $sender_amount;

        $data['sender_currency']                    = get_default_currency_code();
        $data['sender_currency_rate']               = get_default_currency_rate();

        $data['wallet_currency']                    = $sender_wallet->currency->code;
        $data['wallet_currency_rate']               = $sender_wallet->currency->rate;

        $data['percent_charge']                     = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']                       = ($charges->fixed_charge*$sender_wallet->currency->rate) ?? 0;
        $data['total_charge']                       = $data['percent_charge'] + $data['fixed_charge'];

        $data['sender_wallet_balance']              = $sender_wallet->balance;
        $data['conversion_amount']                  = $sender_amount * $exchange_rate;
        $data['payable']                            = $data['sender_amount'] +  $data['total_charge'];

        $data['agent_percent_commission']           = ($data['conversion_amount'] / 100) * $charges->agent_percent_commissions ?? 0;
        $data['agent_fixed_commission']             = $sender_wallet->currency->rate * $charges->agent_fixed_commissions ?? 0;
        $data['agent_total_commission']             = $data['agent_percent_commission'] + $data['agent_fixed_commission'];
        $data['precision_digit']                    = $sPrecision;

        return $data;
    }

    //start automatic bill pay
    public function automaticBillPay($request_data){
        $user = userGuard()['user'];
        try{
        $biller = (new UtilityHelper())->getSingleBiller($request_data['bill_type']);
       }catch(Exception $e){
          $biller = [
            'status' => false,
            'message' => $e->getMessage()
          ];
       }

       if( isset($biller['status']) &&  $biller['status'] == false ){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
       }elseif( isset($biller['content']) && empty( $biller['content'])){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
       }
       $biller =  $biller['content'][0];

       $referenceId =  remove_special_char($user->username.getFirstChar($biller['name']).$request_data['bill_month']).rand(1323,5666);
       $bill_amount = $request_data['amount'];
       $bill_number = $request_data['bill_number'];
       $billPayCharge = TransactionSetting::where('slug','bill_pay')->where('status',1)->first();
       $sender_wallet = AgentWallet::auth()->whereHas("currency",function($q) use ($request_data) {
            $q->where("code",$request_data['currency'])->active();
        })->active()->first();
       if(!$sender_wallet){
            return back()->with(['error' => [__('Agent wallet not found')]]);
       }
       $baseCurrency = Currency::default();
       if(!$baseCurrency){
           return back()->with(['error' => [__('Default currency not found')]]);
       }
       $charges = $this->automaticBillPayCharge($sender_wallet,$biller,$bill_amount,$billPayCharge);

       $minLimit =  $biller['minLocalTransactionAmount'] / $charges['exchange_rate'];
       $maxLimit =  $biller['maxLocalTransactionAmount'] / $charges['exchange_rate'];

       if($charges['sender_amount'] < $minLimit || $charges['sender_amount'] > $maxLimit) {
            return back()->with(['error' => [__("Please follow the transaction limit")]]);
       }

        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('agent_id',$sender_wallet->agent->id,PaymentGatewayConst::BILLPAY,$sender_wallet->currency,$bill_amount,$billPayCharge,PaymentGatewayConst::SEND);
        }catch(Exception $e){
           $errorData = json_decode($e->getMessage(), true);
            return back()->with(['error' => [__($errorData['message'] ?? __("Something went wrong! Please try again."))]]);
        }

       if( $charges['payable'] > $sender_wallet->balance ){
           return back()->with(['error' => [__('Sorry, insufficient balance')]]);
       }

       $payBillData = [
            'subscriberAccountNumber'=> $bill_number,
            'amount'                 => getAmount($charges['conversion_amount'],2),
            'amountId'               => null,
            'billerId'               => $biller['id'],
            'useLocalAmount'         => $biller['localAmountSupported'],
            'referenceId'            => $referenceId,
            'additionalInfo' => [
                'invoiceId'       => null,
            ],

        ];
        $payBill = (new UtilityHelper())->payUtilityBill($payBillData);
        if( $payBill['status'] === false){
            if($payBill['message'] === "The provided reference ID has already been used. Please provide another one."){
                $errorMessage = __("Bill payment already taken for")." ".$biller['name']." ".$request_data['bill_month'];
            }else{
                $errorMessage = $payBill['message'];
            }
            return back()->with(['error' => [$errorMessage]]);
        }
        try{
            $trx_id = 'BP'.getTrxNum();
            $transaction = $this->insertTransactionAutomatic($trx_id,$user,$sender_wallet,$charges,$request_data,$payBill??[],$biller);
            $this->insertAutomaticCharges($transaction,$charges,$biller,$request_data,$user);
            try{
                if( $this->basic_settings->email_notification == true){
                    $notifyData = [
                        'trx_id'            => $trx_id,
                        'biller_name'       => $biller['name'],
                        'bill_month'        => $request_data['bill_month'],
                        'bill_number'       => $bill_number,
                        'request_amount'    => get_amount($charges['sender_amount'],$charges['wallet_currency'],$charges['precision_digit']),
                        'exchange_rate'     => get_amount(1,$charges['wallet_currency'])." = ".get_amount($charges['exchange_rate'],$charges['sender_currency'],$charges['precision_digit']),
                        'bill_amount'       => get_amount($charges['conversion_amount'],$charges['sender_currency'],$charges['precision_digit']),
                        'charges'           => get_amount($charges['total_charge'],$charges['wallet_currency'],$charges['precision_digit']),
                        'payable'           => get_amount($charges['payable'],$charges['wallet_currency'],$charges['precision_digit']),
                        'current_balance'   => getAmount($sender_wallet->balance,$charges['precision_digit']),
                        'status'            => $payBill['status']??__("Successful"),
                    ];
                    //send notifications
                    $user->notify(new BillPayMailAutomatic($user,(object)$notifyData));
                }

            }catch(Exception $e){}
            if( $this->basic_settings->agent_sms_notification == true){
                try{
                    sendSms($user,'BILL_PAY',[
                        'amount'        => get_amount($charges['conversion_amount'],$charges['sender_currency'],$charges['precision_digit']),
                        'type'          => $request_data['biller_item_type']??'',
                        'bill_type'     => $biller['name']??'',
                        'bill_number'   => $request_data['bill_number'],
                        'month'         => $request_data['bill_month'],
                        'trx'           => $trx_id,
                        'time'          => now()->format('Y-m-d h:i:s A')
                    ]);
                }catch(Exception $e) {}
            }
              //admin notification
              $this->adminNotificationAutomatic($trx_id,$charges,$biller,$request_data,$user,$payBill);
             // Dispatch the job to process the payment status
            ProcessBillPayment::dispatch($transaction)->delay(now()->addSeconds(scheduleBillPayApiCall($payBill)));
            // ProcessBillPayment::dispatch($transaction)->delay(now()->addSeconds(10));
            return redirect()->route("agent.bill.pay.index")->with(['success' => [__('Bill Pay Request Successful')]]);
        }catch(Exception $e){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    public function insertTransactionAutomatic($trx_id,$user,$sender_wallet,$charges,$request_data,$payBill,$biller){
        if($payBill['status'] === "PROCESSING"){
            $status = PaymentGatewayConst::STATUSPROCESSING;
        }elseif($payBill['status'] === "SUCCESSFUL"){
            $status = PaymentGatewayConst::STATUSSUCCESS;
        }else{
            $status = PaymentGatewayConst::STATUSFAILD;
        }
        $authWallet = $sender_wallet;
        $afterCharge = ($authWallet->balance - $charges['payable']);
        $details =[
            'bill_type_id'      => $request_data['bill_type']??'',
            'bill_type_name'    => $biller['name']??'',
            'bill_number'       => $request_data['bill_number']??'',
            'sender_amount'     => $request_data['amount']??0,
            'bill_month'        => $request_data['bill_month']??'',
            'bill_type'         => $request_data['biller_item_type']??'',
            'biller_info'       => $biller??[],
            'api_response'      => $payBill??[],
            'charges'           => $charges??[],
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $user->id,
                'agent_wallet_id'               => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::BILLPAY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::BILLPAY," ")) . " Request Successful",
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
    public function insertAutomaticCharges($id,$charges,$biller,$request_data,$user) {
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

            //notification
            $notification_content = [
                'title'         =>__("Bill Pay"),
                'message'       => __("Bill Pay For")." (".$biller['name']." ".$request_data['bill_month'].") " .$charges['sender_amount'].' '.$charges['wallet_currency']." ".__("Successful"),
                'image'         => get_image($user->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::BILL_PAY,
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
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function automaticBillPayCharge($sender_wallet,$biller,$amount,$charges){
        $sPrecision = get_wallet_precision($sender_wallet->currency);
        $sender_currency = Currency::where(['code' => $biller['localTransactionCurrencyCode']])->first();
        $exchange_rate = $sender_currency->rate/$sender_wallet->currency->rate;

        $data['exchange_rate' ]             = $exchange_rate;
        $data['sender_amount']              = $amount;

        $data['sender_currency']            = $sender_currency->code;
        $data['sender_currency_rate']       = $sender_currency->rate;

        $data['wallet_currency']            = $sender_wallet->currency->code;
        $data['wallet_currency_rate']       = $sender_wallet->currency->rate;

        $data['percent_charge']             = ($amount / 100) *  $charges->percent_charge ?? 0;
        $data['fixed_charge']               = ($charges->fixed_charge*$sender_wallet->currency->rate) ?? 0;
        $data['total_charge']               = $data['percent_charge'] + $data['fixed_charge'];

        $data['sender_wallet_balance']      = $sender_wallet->balance;
        $data['conversion_amount']          = $amount * $exchange_rate;
        $data['payable']                    = $data['sender_amount'] + $data['total_charge'];

        $data['agent_percent_commission']   = ($data['payable'] / 100) * $charges->agent_percent_commissions ?? 0;
        $data['agent_fixed_commission']     = $sender_wallet->currency->rate * $charges->agent_fixed_commissions ?? 0;
        $data['agent_total_commission']     = $data['agent_percent_commission'] + $data['agent_fixed_commission'];
        $data['precision_digit']            = $sPrecision;

        return $data;

    }
    //admin notification
    public function adminNotificationManual($trx_id,$charges,$bill_type,$user,$request_data){
        $exchange_rate = get_amount(1,$charges['wallet_currency'])." = ".get_amount($charges['exchange_rate'],$charges['sender_currency'],$charges['precision_digit']);
        $notification_content = [
            //email notification
            'subject' => __("Bill Pay For")." ". $bill_type->name.' ('.$request_data['bill_number'].' )',
            'greeting' =>__("Bill pay request sent to admin successful")." (".$request_data['bill_month'].")",
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("Bill Number")." : ".$request_data['bill_number']."<br>".__("bill Type")." : ".$bill_type->name."<br>".__("Bill Amount")." : ".get_amount($charges['sender_amount'],$charges['wallet_currency'],$charges['precision_digit'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Conversion Amount")." : ".get_amount($charges['conversion_amount'],$charges['sender_currency'],$charges['precision_digit'])."<br>".__("Fees & Charges")." : ". get_amount($charges['total_charge'],$charges['wallet_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['wallet_currency'],$charges['precision_digit'])."<br>".__("Status")." : ".__("Pending"),

            //push notification
            'push_title' => __("Bill pay request sent to admin successful")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id.",".__("Bill Amount")." : ".get_amount($charges['sender_amount'],$charges['wallet_currency'],$charges['precision_digit']).",".__("Bill Month")." : ".$request_data['bill_month'].",".__("Bill Number")." : ".$request_data['bill_number'],

            //admin db notification
            'notification_type' =>  NotificationConst::BILL_PAY,
            'admin_db_title' => "Bill pay request sent to admin successful"." (".userGuard()['type'].")",
            'admin_db_message' =>"Transaction ID"." : ".$trx_id.","."Bill Amount"." : ".get_amount($charges['sender_amount'],$charges['wallet_currency'],$charges['precision_digit']).",".__("Bill Month")." : ".$request_data['bill_month'].",".__("Bill Number")." : ".$request_data['bill_number'].","."Total Payable Amount"." : ".get_amount($charges['payable'],$charges['wallet_currency'],$charges['precision_digit'])." (".$user->email.")"
        ];
        try{
            //notification
            (new NotificationHelper())->admin(['admin.bill.pay.index','admin.bill.pay.pending','admin.bill.pay.processing','admin.bill.pay.complete','admin.bill.pay.canceled','admin.bill.pay.details','admin.bill.pay.approved','admin.bill.pay.rejected','admin.bill.pay.export.data'])
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
    public function adminNotificationAutomatic($trx_id,$charges,$biller,$request_data,$user,$payBill){
        $exchange_rate = get_amount(1,$charges['wallet_currency'])." = ".get_amount($charges['exchange_rate'],$charges['sender_currency'],$charges['precision_digit']);
        if($payBill['status'] === "PROCESSING"){
            $status ="Processing";
        }elseif($payBill['status'] === "SUCCESSFUL"){
            $status ="success";
        }else{
            $status ="Failed";
        }
        $notification_content = [
            //email notification
            'subject' => __("Bill Pay For")." ". $biller['name'].' ('.$request_data['bill_number'].' )',
            'greeting' =>__("Bill pay successful")." (".$request_data['bill_month'].")",
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("Bill Number")." : ".$request_data['bill_number']."<br>".__("bill Type")." : ".$biller['name']."<br>".__("Bill Amount")." : ".get_amount($charges['sender_amount'],$charges['wallet_currency'],$charges['precision_digit'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ". get_amount($charges['total_charge'],$charges['wallet_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['wallet_currency'],$charges['precision_digit'])."<br>".__("Status")." : ".__($status),

            //push notification
            'push_title' => __("Bill pay successful")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id.",".__("Bill Amount")." : ".get_amount($charges['sender_amount'],$charges['wallet_currency'],$charges['precision_digit']).",".__("Bill Month")." : ".$request_data['bill_month'].",".__("Bill Number")." : ".$request_data['bill_number'].",".__("Biller Name")." : ".$biller['name'],

            //admin db notification
            'notification_type' =>  NotificationConst::BILL_PAY,
            'admin_db_title' => "Bill pay successful"." (".userGuard()['type'].")",
            'admin_db_message' =>"Transaction ID"." : ".$trx_id.","."Bill Amount"." : ".get_amount($charges['sender_amount'],$charges['wallet_currency'],$charges['precision_digit']).",".__("Bill Month")." : ".$request_data['bill_month'].",".__("Bill Number")." : ".$request_data['bill_number'].","."Total Payable Amount"." : ".get_amount($charges['payable'],$charges['wallet_currency'],$charges['precision_digit']).",".__("Biller Name")." : ".$biller['name']." (".$user->email.")"
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

}
