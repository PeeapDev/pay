<?php

namespace App\Http\Controllers\User;

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
use App\Models\SudoVirtualCard;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Models\VirtualCardApi;
use App\Notifications\User\VirtualCard\CreateMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\VirtualCard\Fund;
use App\Providers\Admin\BasicSettingsProvider;

class SudoVirtualCardController extends Controller
{
    protected $api;
    protected $card_limit;
    protected $basic_settings;

    public function __construct()
    {
        $cardApi = VirtualCardApi::first();
        $this->api =  $cardApi;
        $this->card_limit =  $cardApi->card_limit;
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function index()
    {
        $page_title = __("Virtual Card");
        $myCards = SudoVirtualCard::where('user_id',auth()->user()->id)->latest()->limit($this->card_limit)->get();
        $totalCards = SudoVirtualCard::where('user_id',auth()->user()->id)->count();
        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        $cardReloadCharge = TransactionSetting::where('slug','reload_card')->where('status',1)->first();
        $transactions = Transaction::auth()->virtualCard()->latest()->take(5)->get();
        $supported_currency = support_currencies(['USD','NGN']);
        $from_wallets = UserWallet::auth()->whereHas('currency',function($q) {
            $q->where("status",GlobalConst::ACTIVE);
        })->active()->get();
        $cardApi = $this->api;
        return view('user.sections.virtual-card-sudo.index',compact(
            'page_title','myCards','cardApi','cardReloadCharge',
            'transactions','cardCharge','totalCards','from_wallets','supported_currency'
        ));
    }
    public function cardDetails($card_id)
    {
        $page_title = __("Card Details");
        $myCard = SudoVirtualCard::where('card_id',$card_id)->first();
        $cardToken = getCardToken($this->api->config->sudo_api_key,$this->api->config->sudo_url,$myCard->card_id);
        if($cardToken['statusCode'] == 200){
            $cardToken = $cardToken['data']['token'];
        }else{
            $cardToken = '';
        }
        $api_mode = $this->api->config->sudo_mode;
        $api_vault_id = $this->api->config->sudo_vault_id;
        return view('user.sections.virtual-card-sudo.details',compact('page_title','myCard','cardToken','api_mode','api_vault_id'));
    }
    public function makeDefaultOrRemove(Request $request) {
        $validated = Validator::make($request->all(),[
            'target'        => "required|numeric",
        ])->validate();
        $user = auth()->user();
        $targetCard =  SudoVirtualCard::where('id',$validated['target'])->where('user_id',$user->id)->first();
        $withOutTargetCards =  SudoVirtualCard::where('id','!=',$validated['target'])->where('user_id',$user->id)->get();

        try{
            $targetCard->update([
                'is_default'         => $targetCard->is_default ? 0 : 1,
            ]);
            if(isset(  $withOutTargetCards)){
                foreach(  $withOutTargetCards as $card){
                    $card->is_default = false;
                    $card->save();
                }
            }

        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return back()->with(['success' => [__('Status Updated Successfully!')]]);
    }
    public function cardTransaction($card_id) {
        $page_title = __("Virtual Card Transaction");
        $user = auth()->user();
        $card = SudoVirtualCard::where('user_id',$user->id)->where('card_id', $card_id)->first();
        $card_truns =  getCardTransactions($this->api->config->sudo_api_key,$this->api->config->sudo_url,$card->card_id);
        return view('user.sections.virtual-card-sudo.trx',compact('page_title','card','card_truns'));
    }
    public function cardBlockUnBlock(Request $request) {
        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        if ($validator->stopOnFirstFailure()->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }
        $validated = $validator->safe()->all();
        if($request->status == 1 ){
            $card = SudoVirtualCard::where('id',$request->data_target)->where('status',1)->first();
            $status = 'inactive';
            if(!$card){
                $error = ['error' => [__("Something is wrong in your card")]];
                return Response::error($error,null,404);
            }
            $result = cardUpdate($this->api->config->sudo_api_key,$this->api->config->sudo_url,$card->card_id,$status);
            if(isset($result['statusCode'])){
                if($result['statusCode'] == 200){
                    $card->status = false;
                    $card->save();
                    $success = ['success' => [__('Card block successfully')]];
                    return Response::success($success,null,200);
                }elseif($result['statusCode'] != 200){
                    $success = ['error' => [$result['message']??"Something is wrong"]];
                    return Response::success($success,null,200);
                }
            }
        }else{
        $card = SudoVirtualCard::where('id',$request->data_target)->where('status',0)->first();
        $status = 'active';
        if(!$card){
            $error = ['error' => [__("Something is wrong in your card")]];
            return Response::error($error,null,404);
        }
        $result = cardUpdate($this->api->config->sudo_api_key,$this->api->config->sudo_url,$card->card_id,$status);
        if(isset($result['statusCode'])){
            if($result['statusCode'] == 200){
                $card->status = true;
                $card->save();
                $success = ['success' => [__('Card unblock successfully')]];
                return Response::success($success,null,200);
            }elseif($result['statusCode'] != 200){
                $success = ['error' => [$result['message']??"Something is wrong"]];
                return Response::success($success,null,400);
            }
        }

        }
    }
    public function createPage(){
        $page_title         = __("Create Virtual Card");
        $supported_currency = support_currencies(['USD','NGN']);
        $from_wallets       = UserWallet::auth()->whereHas('currency',function($q) {
            $q->where("status",GlobalConst::ACTIVE);
        })->active()->get();
        $user               = userGuard()['user'];
        $cardCharge         = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();

        return view('user.sections.virtual-card-sudo.create',compact('page_title','user','cardCharge','supported_currency','from_wallets'));
    }
    public function cardBuy(Request $request)
    {
        $validated = Validator::make($request->all(),[
            'card_amount'       => 'required|numeric|gt:0',
            'currency'          => "required|string|exists:currencies,code",
            'from_currency'     => "required|string|exists:currencies,code",
            'date_of_birth'     => 'required|date',
            'identity_type'     => 'required|string',
            'identity_number'   => 'required|string|regex:/^[A-Z0-9]+$/i',
            'phone_number'      => 'required|string|regex:/^\+?[0-9]{10,15}$/',
        ])->validate();

        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        $currency = $validated['currency'];
        $amount = (float)$validated['card_amount'];

        $wallet = UserWallet::where('user_id',$user->id)->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['from_currency'])->active();
        })->active()->first();
        if(!$wallet) return back()->with(['error' => [__('User wallet not found')]]);

        $card_currency = Currency::active()->where('code',$validated['currency'])->first();
        if(!$card_currency) return back()->with(['error' => [__('Card Currency Not Found')]]);

        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        $charges = $this->cardCharges($validated['card_amount'],$cardCharge,$wallet,$card_currency);

        $minLimit =  $cardCharge->min_limit *  $charges['card_currency_rate'];
        $maxLimit =  $cardCharge->max_limit *  $charges['card_currency_rate'];

        if($amount < $minLimit || $amount > $maxLimit)  return back()->with(['error' => [__("Please follow the transaction limit")]]);
        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('user_id',$wallet->user->id,PaymentGatewayConst::VIRTUALCARD,$wallet->currency,$amount,$cardCharge,PaymentGatewayConst::RECEIVED);
        }catch(Exception $e){
           $errorData = json_decode($e->getMessage(), true);
            return back()->with(['error' => [__($errorData['message'] ?? __("Something went wrong! Please try again."))]]);
        }

        if($charges['payable'] > $wallet->balance) return back()->with(['error' => [__("Your Wallet Balance Is Insufficient")]]);

        $supported_currency = ['USD','NGN'];
        if( !in_array($currency,$supported_currency??[])) return back()->with(['error' => [$currency." ". __("Currency isn't supported for creating virtual card, Please contact")]]);

        $funding_sources =  get_funding_source( $this->api->config->sudo_api_key,$this->api->config->sudo_url);
        if(isset( $funding_sources['statusCode'])){
            if($funding_sources['statusCode'] == 403){
                return back()->with(['error' => [$funding_sources['message']]]);
            }elseif($funding_sources['statusCode'] == 404){
                return back()->with(['error' => [$funding_sources['message']]]);
            }
        }
        $account_type = 'default';
        $accountTypeArray = array_filter($funding_sources['data'], function($item) use ($account_type) {
            return $item['type'] === $account_type;
        });
        if( count($accountTypeArray) <=  0){
            $create_founding_source = funding_source_create( $this->api->config->sudo_api_key,$this->api->config->sudo_url);
            if($create_founding_source['status'] ===  false){
                return back()->with(['error' => [$create_founding_source['message']]]);
            }
            $bankCode = $create_founding_source['data']['_id']??'';
        }else{
            $funding_source_id =  array_values($accountTypeArray);
            $bankCode = $funding_source_id[0]['_id']??'';
        }

        $sudo_accounts =    get_sudo_accounts( $this->api->config->sudo_api_key,$this->api->config->sudo_url);
        $filteredArray = array_filter($sudo_accounts, function($item) use ($currency) {
            return $item['currency'] === $currency;
        });
        $matchingElements = array_values($filteredArray);
        $debitAccountId= $matchingElements[0]['_id']??"";

        if(  $debitAccountId == ""){
            //create debit account
            if( $user->sudo_account == null){
                //create account
                $store_account = create_sudo_account($this->api->config->sudo_api_key,$this->api->config->sudo_url,$currency);
                if( isset($store_account['status'])){
                    if($store_account['status'] == false){
                        return back()->with(['error' => [ $store_account['message']]]);
                    }
                }
                $user->sudo_account =   (object)$store_account['data'];
                $user->save();
            }
        }else{
            $user->sudo_account = (object)$matchingElements[0];
            $user->save();
        }
        $debitAccountId = $user->sudo_account->_id??'';

        $issuerCountry = '';
        if($currency == "NGN"){
            $issuerCountry = "NGA";
        }elseif($currency == "USD"){
            $issuerCountry = "USA";
        }

        //check sudo customer have or not
       if( $user->sudo_customer == null){
        //create customer
        $store_customer = create_sudo_customer($this->api->config->sudo_api_key,$this->api->config->sudo_url,$user,$validated);

        if( isset($store_customer['error'])){
            return back()->with(['error' => [__("The customer doesn't create properly,Contact with owner")]]);
        }
        $user->sudo_customer =   (object)$store_customer['data'];
        $user->save();
        $customerId = $user->sudo_customer->_id;

       }else{
        $customerId = $user->sudo_customer->_id;
       }
       //create card now
       $created_card = create_virtual_card($this->api->config->sudo_api_key,$this->api->config->sudo_url,
                            $customerId,$currency,$bankCode, $debitAccountId,$issuerCountry,$amount);
       if(isset($created_card['statusCode'])){
            if($created_card['statusCode'] == 400) return back()->with(['error' => [$created_card['message']]]);
       }
       if($created_card['statusCode']  = 200){
            $card_info = (object)$created_card['data'];
            $v_card = new SudoVirtualCard();
            $v_card->user_id = $user->id;
            $v_card->name = $user->fullname;
            $v_card->card_id = $card_info->_id;
            $v_card->business_id = $card_info->business;
            $v_card->customer = $card_info->customer;
            $v_card->account = $card_info->account;
            $v_card->fundingSource = $card_info->fundingSource;
            $v_card->type = $card_info->type;
            $v_card->brand = $card_info->brand;
            $v_card->currency = $charges['card_currency'];
            if($this->api->config->sudo_mode === GlobalConst::SANDBOX){
                $v_card->amount = $card_info->balance;
            }elseif($this->api->config->sudo_mode === GlobalConst::LIVE){
                $v_card->amount = $amount;
            }
            $v_card->charge = $charges['total_charge'];
            $v_card->maskedPan = $card_info->maskedPan;
            $v_card->last4 = $card_info->last4;
            $v_card->expiryMonth = $card_info->expiryMonth;
            $v_card->expiryYear = $card_info->expiryYear;
            $v_card->status = true;
            $v_card->isDeleted = $card_info->isDeleted;
            $v_card->billingAddress = $card_info->billingAddress;
            $v_card->save();

            $trx_id =  'CB'.getTrxNum();
            try{
                $sender = $this->insertCardBuy($trx_id,$user,$wallet,$amount,$v_card,$charges);
                $this->insertBuyCardCharge($charges,$user,$sender,$v_card->maskedPan);
                if( $basic_setting->email_notification == true){
                    $notifyDataSender = [
                        'trx_id'  => $trx_id,
                        'title'  => __("Virtual Card (Buy Card)"),
                        'request_amount'  => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                        'payable'   =>  get_amount($charges['payable'],$charges['from_currency'],$charges['precision_digit']),
                        'charges'   => get_amount( $charges['total_charge'],$charges['from_currency'],$charges['precision_digit']),
                        'card_amount'  => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                        'card_pan'  => $v_card->maskedPan,
                        'status'  => __("success"),
                    ];
                    try{
                        $user->notify(new CreateMail($user,(object)$notifyDataSender));
                    }catch(Exception $e){}
                }
                if( $basic_setting->sms_notification == true){
                    try{
                        sendSms($user,'VIRTUAL_CARD_BUY',[
                            'request_amount'    => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                            'card_amount'       => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                            'card_pan'          => $v_card->maskedPan,
                            'trx'               => $trx_id,
                            'time'              =>  now()->format('Y-m-d h:i:s A')
                        ]);
                    }catch(Exception $e) {}
                }
                //admin notification
                $this->adminNotification($trx_id,$charges,$amount,$user,$v_card);
                return redirect()->route("user.sudo.virtual.card.index")->with(['success' => [__('Virtual Card Buy Successfully')]]);
            }catch(Exception $e){
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

       }

    }

    //card buy helper
    public function insertCardBuy($trx_id,$user,$wallet,$amount,$v_card,$charges) {
        $trx_id = $trx_id;
        $authWallet = $wallet;
        $afterCharge = ($authWallet->balance - $charges['payable']);
        $details =[
            'card_info' =>   $v_card??'',
            'charges' =>   $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::VIRTUALCARD,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => PaymentGatewayConst::CARDBUY,
                'details'                       => json_encode($details),
                'attribute'                     => PaymentGatewayConst::RECEIVED,
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
    public function insertBuyCardCharge($charges,$user,$id,$masked_card) {
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
                'title'         =>__('buy Card'),
                'message'       => __('Buy card successful')." ".$masked_card,
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::CARD_BUY,
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
    //card fund info
    public function cardFundPage($id){
        $page_title = __("Fund Virtual Card");
        $from_wallets = UserWallet::auth()->whereHas('currency',function($q) {
            $q->where("status",GlobalConst::ACTIVE);
        })->active()->get();
        $user       = userGuard()['user'];
        $cardReloadCharge = TransactionSetting::where('slug','reload_card')->where('status',1)->first();
        $myCard = SudoVirtualCard::where('user_id', auth()->user()->id)->where('id',$id)->first();
        if(!$myCard) return back()->with(['error' => [__("Something is wrong in your card")]]);

        $supported_currency = support_currencies([$myCard->currency ?? "USD"]);

        return view('user.sections.virtual-card-sudo.fund',compact('page_title','user','cardReloadCharge','supported_currency','from_wallets','myCard'));
    }
    public function cardFundConfirm(Request $request){

        $validated = Validator::make($request->all(),[
            'id'                => 'required|integer',
            'fund_amount'       => 'required|numeric|gt:0',
            'currency'          => "required|string|exists:currencies,code",
            'from_currency'     => "required|string|exists:currencies,code",
        ])->validate();

        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        $myCard =  SudoVirtualCard::where('user_id',auth()->user()->id)->where('id',$request->id)->first();
        if(!$myCard){
            return back()->with(['error' => [__("Something is wrong in your card")]]);
        }
        $amount = $request->fund_amount;
        $wallet = UserWallet::where('user_id',$user->id)->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['from_currency'])->active();
        })->active()->first();
        if(!$wallet) return back()->with(['error' => [__('User wallet not found')]]);

        $cardCharge = TransactionSetting::where('slug','reload_card')->where('status',1)->first();
        $card_currency = Currency::active()->where('code',$validated['currency'])->first();
        if(!$card_currency) return back()->with(['error' => [__('Card Currency Not Found')]]);

        $charges = $this->cardCharges($validated['fund_amount'],$cardCharge,$wallet,$card_currency);

        $minLimit =  $cardCharge->min_limit *  $charges['card_currency_rate'];
        $maxLimit =  $cardCharge->max_limit *  $charges['card_currency_rate'];
        if($amount < $minLimit || $amount > $maxLimit)  return back()->with(['error' => [__("Please follow the transaction limit")]]);
        //daily and monthly
         try{
            (new TransactionLimit())->trxLimit('user_id',$wallet->user->id,PaymentGatewayConst::VIRTUALCARD,$wallet->currency,$amount,$cardCharge,PaymentGatewayConst::RECEIVED);
        }catch(Exception $e){
           $errorData = json_decode($e->getMessage(), true);
            return back()->with(['error' => [__($errorData['message'] ?? __("Something went wrong! Please try again."))]]);
        }
        if($charges['payable'] > $wallet->balance) return back()->with(['error' => [__("Your Wallet Balance Is Insufficient")]]);

        $get_card_details =  getSudoCard($myCard->card_id);
        if($get_card_details['status'] === false) return back()->with(['error' => [__("Something is wrong in your card")]]);
        $card_account_number =  $get_card_details['data']['account']['_id'];

        $card_fund_response = sudoFundCard( $card_account_number,(float)$amount);
        if(!empty($card_fund_response['status'])  && $card_fund_response['status'] === true){
            //added fund amount to card
            $myCard->amount += $amount;
            $myCard->save();
            $trx_id = 'CF'.getTrxNum();
            $sender = $this->insertCardFund($trx_id,$user,$wallet,$amount,$myCard,$charges);
            $this->insertFundCardCharge($charges,$user,$sender,$myCard->maskedPan,$amount);
            if($basic_setting->email_notification == true){
                $notifyDataSender = [
                    'trx_id'        => $trx_id,
                    'title'         => __("Virtual Card (Fund Amount)"),
                    'request_amount'=> get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                    'payable'       => get_amount($charges['payable'],$charges['from_currency'],$charges['precision_digit']),
                    'charges'       => get_amount( $charges['total_charge'],$charges['from_currency'],$charges['precision_digit']),
                    'card_amount'   => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                    'card_pan'      => $myCard->maskedPan,
                    'status'        => __("success"),
                ];
                try{
                    $user->notify(new Fund($user,(object)$notifyDataSender));
                }catch(Exception $e){}
            }
            if( $basic_setting->sms_notification == true){
                try{
                    sendSms($user,'VIRTUAL_CARD_FUND',[
                        'request_amount'    => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                        'card_amount'       => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                        'card_pan'          => $myCard->maskedPan,
                        'trx'               => $trx_id,
                        'time'              =>  now()->format('Y-m-d h:i:s A')
                    ]);
                }catch(Exception $e) {}
            }

             //admin notification
             $this->adminNotificationFund($trx_id,$charges,$amount,$user,$myCard);
            return redirect()->route("user.sudo.virtual.card.index")->with(['success' => [__('Card Funded Successfully')]]);

        }else{
            return redirect()->back()->with(['error' => [@$card_fund_response['message'].' ,'.__('Please Contact With Administration.')]]);
        }

    }
     //card fund helper
    public function insertCardFund($trx_id,$user,$wallet,$amount,$myCard,$charges) {
        $trx_id = $trx_id;
        $authWallet = $wallet;
        $afterCharge = ($authWallet->balance - $charges['payable']);
        $details =[
            'card_info' =>   $myCard??'',
            'charges'   =>   $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::VIRTUALCARD,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(PaymentGatewayConst::CARDFUND),
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
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
    public function insertFundCardCharge($charges,$user,$id,$masked_card,$amount) {
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
                'title'         =>__("Card Fund"),
                'message'       => __("Card fund successful card")." : ".$masked_card.' '.get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::CARD_FUND,
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
    //update user balance
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    //admin notification
    public function adminNotification($trx_id,$charges,$amount,$user,$v_card){
        $notification_content = [
            //email notification
            'subject' => __("Virtual Card (Buy Card)"),
            'greeting' => __("Virtual Card Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit'])."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['from_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['from_currency'],$charges['precision_digit'])."<br>".__("card Masked")." : ".@$v_card->maskedPan."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Virtual Card (Buy Card)")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id." ".__("request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit'])." ".__("card Masked")." : ".$v_card->maskedPan??"",

            //admin db notification
            'notification_type' =>  NotificationConst::CARD_BUY,
            'admin_db_title' => "Virtual Card Buy"." (".userGuard()['type'].")",
            'admin_db_message' => "Transaction ID"." : ".$trx_id.",".__("Request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit']).","."Card Masked"." : ".@$v_card->maskedPan." (".$user->email.")",
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
    public function adminNotificationFund($trx_id,$charges,$amount,$user,$myCard){
        $notification_content = [
            //email notification
            'subject' => __("Virtual Card (Fund Amount)"),
            'greeting' => __("Virtual Card Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit'])."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['from_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['from_currency'],$charges['precision_digit'])."<br>".__("card Masked")." : ".$myCard->maskedPan??""."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Virtual Card (Fund Amount)")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id." ".__("request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit'])." ".__("card Masked")." : ".$myCard->maskedPan??"",

            //admin db notification
            'notification_type' =>  NotificationConst::CARD_FUND,
            'admin_db_title' => "Virtual Card Funded"." (".userGuard()['type'].")",
            'admin_db_message' => "Transaction ID"." : ".$trx_id.",".__("Request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit']).","."Card Masked"." : ".$myCard->maskedPan." (".$user->email.")",
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
    //card buy charges function
     public function cardCharges($amount,$charges,$wallet,$card_currency){
        $sPrecision = get_wallet_precision($wallet->currency);
        $exchange_rate = $wallet->currency->rate/$card_currency->rate;

        $data['exchange_rate']         = $exchange_rate;
        $data['card_amount']           = $amount;
        $data['card_currency']         = $card_currency->code;
        $data['card_currency_rate']    = $card_currency->rate;

        $data['from_amount']           = $amount * $exchange_rate;
        $data['from_currency']         = $wallet->currency->code;
        $data['from_currency_rate']    = $wallet->currency->rate;

        $data['percent_charge']        = ($data['from_amount'] / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']          = $exchange_rate * $charges->fixed_charge ?? 0;
        $data['total_charge']          = $data['percent_charge'] + $data['fixed_charge'];
        $data['from_wallet_balance']   = $wallet->balance;
        $data['payable']               = $data['from_amount'] + $data['total_charge'];
        $data['card_platform']         = "Sudo Africa";
        $data['precision_digit']       = $sPrecision;

        return $data;

     }
}
