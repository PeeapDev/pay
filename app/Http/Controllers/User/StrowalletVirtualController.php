<?php

namespace App\Http\Controllers\User;

use App\Constants\GlobalConst;
use App\Notifications\User\VirtualCard\CreateMail;
use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Models\VirtualCardApi;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use App\Models\StrowalletVirtualCard;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use App\Models\Admin\TransactionSetting;
use App\Models\StrowalletCustomerKyc;
use App\Models\StrowalletWebhookData;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\VirtualCard\Fund;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StrowalletVirtualController extends Controller
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
        $page_title     = __("Virtual Card");
        $myCards = StrowalletVirtualCard::where('user_id', auth()->user()->id)->latest()->limit($this->card_limit)->get();
        $user           = auth()->user();
        $customer_email = $user->strowallet_customer->customerEmail??false;
        if($customer_email === false){
            $customer_card  = 0;
        }else{
            $customer_card  = StrowalletVirtualCard::where('customer_email',$customer_email)->count();
        }
        $cardCharge     = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        $cardReloadCharge = TransactionSetting::where('slug','reload_card')->where('status',1)->first();
        $transactions   = Transaction::auth()->virtualCard()->latest()->take(5)->get();
        $supported_currency = support_currencies(['USD']);
        $from_wallets = UserWallet::auth()->whereHas('currency',function($q) {
            $q->where("status",GlobalConst::ACTIVE);
        })->active()->get();
        $cardApi = $this->api;
        return view('user.sections.virtual-card-strowallet.index',compact(
            'page_title',
            'cardApi',
            'myCards',
            'transactions',
            'cardCharge',
            'customer_card',
            'cardReloadCharge',
            'from_wallets',
            'supported_currency'

        ));
    }
    /**
     * Method for card details
     * @param $card_id
     * @param \Illuminate\Http\Request $request
     */
    public function cardDetails($card_id){
        $page_title = __("Card Details");
        $myCard = StrowalletVirtualCard::where('card_id',$card_id)->first();
        if(!$myCard) return back()->with(['error' => [__("Something is wrong in your card")]]);
        if($myCard->card_status == 'pending'){
            $card_details   = card_details($card_id,$this->api->config->strowallet_public_key,$this->api->config->strowallet_url);

            if($card_details['status'] == false){
                return back()->with(['error' => [__("Your Card Is Pending! Please Contact With Admin")]]);
            }

            $myCard->user_id                   = Auth::user()->id;
            $myCard->card_status               = $card_details['data']['card_detail']['card_status'];
            $myCard->card_number               = $card_details['data']['card_detail']['card_number'];
            $myCard->last4                     = $card_details['data']['card_detail']['last4'];
            $myCard->cvv                       = $card_details['data']['card_detail']['cvv'];
            $myCard->expiry                    = $card_details['data']['card_detail']['expiry'];
            $myCard->save();
        }
        $cardApi = $this->api;
        return view('user.sections.virtual-card-strowallet.details',compact(
            'page_title',
            'myCard',
            'cardApi'
        ));
    }

    /**
     * Method for strowallet card buy page
     */
    public function createPage(){
        $page_title = __("Create Virtual Card");
        $supported_currency = support_currencies(['USD']);
        $from_wallets = UserWallet::auth()->whereHas('currency',function($q) {
            $q->where("status",GlobalConst::ACTIVE);
        })->active()->get();
        $user       = userGuard()['user'];
        $cardCharge     = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        if($user->strowallet_customer != null){
            //get customer api response
            $customer = $user->strowallet_customer;
            $customerEmail = $customer->customerEmail??"";
            $customerId = $customer->customerId??"";

            $getCustomerInfo = get_customer($this->api->config->strowallet_public_key,$this->api->config->strowallet_url,$customerId,$customerEmail);
            if( $getCustomerInfo['status'] == false){
                return back()->with(['error' => [$getCustomerInfo['message'] ?? __("Something went wrong! Please try again.")]]);
            }
            $customer               = (array) $customer;
            $customer_status_info   =  $getCustomerInfo['data'];

            foreach ($customer_status_info as $key => $value) {
                $customer[$key] = $value;
            }
            $user->strowallet_customer = (object) $customer;
            $user->save();
        }

        return view('user.sections.virtual-card-strowallet.create',compact('page_title','user','cardCharge','supported_currency','from_wallets'));
    }
    /**
     * Method for strowallet create customer
     */
    public function createCustomer(Request $request){
        $validated = Validator::make($request->all(),[
            'first_name'        => ['required', 'string', 'regex:/^[^0-9\W]+$/'], // First name validation
            'last_name'         => ['required', 'string', 'regex:/^[^0-9\W]+$/'],  // Last name validation
            'customer_email'    => 'required|email',
            'date_of_birth'     => 'required|string',
            'house_number'      => 'required|string',
            'address'           => 'required|string',
            'zip_code'          => 'required|string',
            'id_image_font'     => "required|image|mimes:jpg,png,svg,webp",
            'user_image'        => "required|image|mimes:jpg,png,svg,webp",
        ], [
            'first_name.regex'  => __('The First Name field should only contain letters and cannot start with a number or special character.'),
            'last_name.regex'   => __('The Last Name field should only contain letters and cannot start with a number or special character.'),
        ])->validate();
        $user       = userGuard()['user'];
        $validated['phone'] = $user->full_mobile;

        try{
            if($user->strowallet_customer == null){
                if($request->hasFile("id_image_font")) {
                    $image = upload_file($validated['id_image_font'],'card-kyc-images');
                    $upload_image = upload_files_from_path_dynamic([$image['dev_path']],'card-kyc-images');
                    $validated['id_image_font']     = $upload_image;
                }

                //user image
                if($request->hasFile("user_image")) {
                    $image = upload_file($validated['user_image'],'card-kyc-images');
                    $upload_image = upload_files_from_path_dynamic([$image['dev_path']],'card-kyc-images');
                    $validated['user_image']     = $upload_image;
                }
                $exist_kyc = StrowalletCustomerKyc::where('user_id',$user->id)->first();
                if($exist_kyc){
                    $exist_kyc->update([
                        'user_id'         =>  $user->id,
                        'face_image'      =>  $validated['user_image'],
                        'id_image'        =>  $validated['id_image_font']
                    ]);
                    $kyc_info = StrowalletCustomerKyc::where('user_id',$user->id)->first();
                }else{
                    //store kyc images
                    $kyc_info = StrowalletCustomerKyc::create([
                        'user_id'         =>  $user->id,
                        'face_image'      =>  $validated['user_image'],
                        'id_image'        =>  $validated['id_image_font']
                    ]);
                }
                $idImage = $kyc_info->idImageData;
                $userPhoto = $kyc_info->faceImageData;

                $validated = Arr::except($validated,['id_image_font','user_image']);
                $createCustomer     = stro_wallet_create_user($validated,$this->api->config->strowallet_public_key,$this->api->config->strowallet_url,$idImage,$userPhoto);
                if( $createCustomer['status'] == false){
                    $kyc_info->delete();
                    return $this->apiErrorHandle($createCustomer["message"]);

                }
                $user->strowallet_customer =   (object)$createCustomer['data'];
                $user->save();
            }
            return redirect()->route("user.strowallet.virtual.card.create")->with(['success' => [__('Customer has been created successfully.')]]);

        }catch(Exception $e){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

    }
    /**
     * Method for strowallet edit customer
     */
    public function editCustomer(){
        $user = userGuard()['user'];
        if($user->strowallet_customer == null){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        $page_title = __("Update Customer Kyc");
        $customer_kyc = StrowalletCustomerKyc::where('user_id',$user->id)->first();
        return view('user.sections.virtual-card-strowallet.edit',compact('page_title','user','customer_kyc'));
    }
    /**
     * Method for strowallet update customer
     */
    public function updateCustomer(Request $request){
        $validated = Validator::make($request->all(),[
            'first_name'        => ['required', 'string', 'regex:/^[^0-9\W]+$/'],
            'last_name'         => ['required', 'string', 'regex:/^[^0-9\W]+$/'],
            'id_image_font'     => "nullable|image|mimes:jpg,png,svg,webp",
            'user_image'        => "nullable|image|mimes:jpg,png,svg,webp",
        ], [
            'first_name.regex'  => __('The First Name field should only contain letters and cannot start with a number or special character.'),
            'last_name.regex'   => __('The Last Name field should only contain letters and cannot start with a number or special character.'),
        ])->validate();
        $user       = userGuard()['user'];

        try{
            if($user->strowallet_customer != null){
                $customer_kyc = StrowalletCustomerKyc::where('user_id',$user->id)->first();
                if($request->hasFile("id_image_font")) {
                    $id_image = upload_file($validated['id_image_font'],'card-kyc-images',);
                    $upload_image = upload_files_from_path_dynamic([$id_image['dev_path']],'card-kyc-images',$customer_kyc->id_image??null);
                    // delete_file($id_image['dev_path']);
                    $validated['id_image_font']     = $upload_image;
                }

                //user image
                if($request->hasFile("user_image")) {
                    $user_image = upload_file($validated['user_image'],'card-kyc-images',$customer_kyc->face_image??null);
                    $upload_image = upload_files_from_path_dynamic([$user_image['dev_path']],'card-kyc-images');
                    // delete_file($user_image['dev_path']);
                    $validated['user_image']     = $upload_image;
                }
                //store kyc images
                if( $customer_kyc){
                    $customer_kyc->update([
                        'user_id'         =>  $user->id,
                        'id_image'        =>  $validated['id_image_font'] ?? $customer_kyc->id_image,
                        'face_image'      =>  $validated['user_image'] ??$customer_kyc->face_image
                    ]);
                }else{
                    $customer_kyc = StrowalletCustomerKyc::create([
                        'user_id'         =>  $user->id,
                        'id_image'        =>  $validated['id_image_font'],
                        'face_image'      =>  $validated['user_image']
                    ]);
                }

                $idImage = $customer_kyc->idImageData;
                $userPhoto = $customer_kyc->faceImageData;

                $validated = Arr::except($validated,['id_image_font','user_image']);
                $updateCustomer     = update_customer($validated,$this->api->config->strowallet_public_key,$this->api->config->strowallet_url,$idImage,$userPhoto,$user->strowallet_customer);
                if( $updateCustomer['status'] == false){
                    $customer_kyc->delete();
                    return $this->apiErrorHandle($updateCustomer["message"]);
                }

                 //get customer api response
                $customer = $user->strowallet_customer;
                $getCustomerInfo = get_customer($this->api->config->strowallet_public_key,$this->api->config->strowallet_url,$updateCustomer['data']['customerId']??"",$updateCustomer['data']['customerEmail']??"");
                if( $getCustomerInfo['status'] == false){
                    $customer_kyc->delete();
                    return back()->with(['error' => [$getCustomerInfo['message'] ?? __("Something went wrong! Please try again.")]]);
                }
                $customer               = (array) $customer;
                $customer_status_info   =  $getCustomerInfo['data'];

                foreach ($customer_status_info as $key => $value) {
                    $customer[$key] = $value;
                }
                $user->strowallet_customer = (object) $customer;
                $user->save();

            }else{
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
            return redirect()->back()->with(['success' => [__('Customer has been updated successfully.')]]);

        }catch(Exception $e){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

    }
    /**
     * Method for strowallet card buy
     */
    public function cardBuy(Request $request){
        $user = auth()->user();
        $request->validate([
            'card_amount'       => 'required|numeric|gt:0',
            'name_on_card'      => 'required|string|min:4|max:50',
            'currency'          => "required|string|exists:currencies,code",
            'from_currency'     => "required|string|exists:currencies,code",
        ]);


        $formData   = $request->all();
        $amount = $request->card_amount;
        $currency = $formData['currency'];
        $basic_setting = BasicSettings::first();

        $supported_currency = ['USD'];
        if( !in_array($currency,$supported_currency??[])) return back()->with(['error' => [$currency." ". __("Currency isn't supported for creating virtual card, Please contact")]]);

        $wallet = UserWallet::where('user_id',$user->id)->whereHas("currency",function($q) use ($formData) {
            $q->where("code",$formData['from_currency'])->active();
        })->active()->first();
        if(!$wallet) return back()->with(['error' => [__('User wallet not found')]]);

        $card_currency = Currency::active()->where('code',$formData['currency'])->first();
        if(!$card_currency) return back()->with(['error' => [__('Card Currency Not Found')]]);

        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        $charges = $this->cardCharges($formData['card_amount'],$cardCharge,$wallet,$card_currency);

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

        $customer = $user->strowallet_customer;
        if(!$customer){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        $customer_email = $user->strowallet_customer->customerEmail??false;
        if($customer_email === false){
            $customer_card  = 0;
        }else{
            $customer_card  = StrowalletVirtualCard::where('user_id',$user->id)->count();
        }

        if($customer_card >= $this->card_limit){
            return back()->with(['error' => [__("Sorry! You can not create more than")." ".$this->card_limit ." ".__("card using the same email address.")]]);
        }
        // for live code
        $created_card = create_strowallet_virtual_card($user,$request->card_amount,$customer,$this->api->config->strowallet_public_key,$this->api->config->strowallet_url,$formData);

        if($created_card['status'] == false){
            return back()->with(['error' => [$created_card['message']]]);
        }

        $strowallet_card                            = new StrowalletVirtualCard();
        $strowallet_card->user_id                   = $user->id;
        $strowallet_card->name_on_card              = $created_card['data']['name_on_card'];
        $strowallet_card->card_id                   = $created_card['data']['card_id'];
        $strowallet_card->card_created_date         = $created_card['data']['card_created_date'];
        $strowallet_card->card_type                 = $created_card['data']['card_type'];
        $strowallet_card->card_brand                = "visa";
        $strowallet_card->card_user_id              = $created_card['data']['card_user_id'];
        $strowallet_card->reference                 = $created_card['data']['reference'];
        $strowallet_card->card_status               = $created_card['data']['card_status'];
        $strowallet_card->customer_id               = $created_card['data']['customer_id'];
        $strowallet_card->customer_email            = $request->customer_email??$customer->customerEmail;
        $strowallet_card->balance                   = $amount;
        $strowallet_card->currency                  = $currency;
        $strowallet_card->save();

        $trx_id =  'CB'.getTrxNum();
        try{
            $sender = $this->insertCardBuy($trx_id,$user,$wallet,$amount,$strowallet_card,$charges);
            $this->insertBuyCardCharge($charges,$user,$sender,$strowallet_card->card_number);
            if($basic_setting->email_notification == true){
                $notifyDataSender = [
                    'trx_id'            => $trx_id,
                    'title'             => "Virtual Card (Buy Card)",
                    'request_amount'    => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                    'payable'           => get_amount($charges['payable'],$charges['from_currency'],$charges['precision_digit']),
                    'charges'           => get_amount( $charges['total_charge'],$charges['from_currency'],$charges['precision_digit']),
                    'card_amount'       => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                    'card_pan'          =>  "---- ----- ---- ----",
                    'status'            =>  $strowallet_card->card_status??"",
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
                        'card_pan'          => "---- ----- ---- ----",
                        'trx'               => $trx_id,
                        'time'              =>  now()->format('Y-m-d h:i:s A')
                    ]);
                }catch(Exception $e) {}
            }
            //admin notification
            $this->adminNotification($trx_id,$charges,$amount,$user,$strowallet_card);
            return redirect()->route("user.strowallet.virtual.card.index")->with(['success' => [__('Virtual Card Buy Successfully')]]);
        }catch(Exception $e){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }



    }
    public function insertCardBuy($trx_id,$user,$wallet,$amount,$strowallet_card,$charges) {
        $trx_id = $trx_id;
        $authWallet = $wallet;
        $afterCharge = ($authWallet->balance - $charges['payable']);
        $details =[
            'card_info' =>   $strowallet_card??'',
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
    public function insertBuyCardCharge($charges,$user,$id,$card_number) {
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
                'message'       => __('Buy card successful')." ".$card_number??"---- ---- ---- ----",
                'image'         => files_asset_path('profile-default'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::CARD_BUY,
                'user_id'   => $user->id,
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
    /**
     * card freeze unfreeze
     */
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
        if($request->status == 1){

            $card   = StrowalletVirtualCard::where('id',$request->data_target)->where('is_active',1)->first();
            $client = new \GuzzleHttp\Client();
            $public_key     = $this->api->config->strowallet_public_key;
            $base_url       = $this->api->config->strowallet_url;

            $response = $client->request('POST', $base_url.'action/status/?action=freeze&card_id='.$card->card_id.'&public_key='.$public_key, [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ]);

            $result = $response->getBody();
            $data  = json_decode($result, true);

            if( isset($data['status']) ){
                $card->is_active = 0;
                $card->save();
                $success = ['success' => [__('Card Freeze successfully')]];
                return Response::success($success,null,200);
            }else{
                $error = ['error' =>  [$data['message']??__("Something went wrong! Please try again.")]];
                return Response::error($error,null,400);
            }
        }else{

            $card   = StrowalletVirtualCard::where('id',$request->data_target)->where('is_active',0)->first();
            $client = new \GuzzleHttp\Client();
            $public_key     = $this->api->config->strowallet_public_key;
            $base_url       = $this->api->config->strowallet_url;

            $response = $client->request('POST', $base_url.'action/status/?action=unfreeze&card_id='.$card->card_id.'&public_key='.$public_key, [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ]);
            $result = $response->getBody();
            $data  = json_decode($result, true);
            if(isset($data['status'])){
                $card->is_active = 1;
                $card->save();
                $success = ['success' => [__('Card UnFreeze successfully')]];
                return Response::success($success,null,200);
            }else{
                $error = ['error' =>  [$data['message']??__("Something went wrong! Please try again.")]];
                return Response::error($error,null,400);
            }
        }

    }
    public function makeDefaultOrRemove(Request $request) {
        $validated = Validator::make($request->all(),[
            'target'        => "required|numeric",
        ])->validate();
        $user = auth()->user();
        $targetCard =  StrowalletVirtualCard::where('id',$validated['target'])->where('user_id',$user->id)->first();
        $withOutTargetCards =  StrowalletVirtualCard::where('id','!=',$validated['target'])->where('user_id',$user->id)->get();

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
    /**
     * Card Fund
     */
    public function cardFundPage($id){
        $page_title = __("Fund Virtual Card");
        $from_wallets = UserWallet::auth()->whereHas('currency',function($q) {
            $q->where("status",GlobalConst::ACTIVE);
        })->active()->get();
        $user       = userGuard()['user'];
        $cardReloadCharge = TransactionSetting::where('slug','reload_card')->where('status',1)->first();
        $myCard = StrowalletVirtualCard::where('user_id', auth()->user()->id)->where('id',$id)->first();
        if(!$myCard) return back()->with(['error' => [__("Something is wrong in your card")]]);

        $supported_currency = support_currencies([$myCard->currency ?? "USD"]);

        return view('user.sections.virtual-card-strowallet.fund',compact('page_title','user','cardReloadCharge','supported_currency','from_wallets','myCard'));
    }
    public function cardFundConfirm(Request $request){
        $validated = Validator::make($request->all(),[
            'id'            => 'required|integer',
            'fund_amount'   => 'required|numeric|gt:0',
            'currency'      => "required|string|exists:currencies,code",
            'from_currency' => "required|string|exists:currencies,code",
        ])->validate();

        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        $amount = $request->fund_amount;

        $myCard =  StrowalletVirtualCard::where('user_id',$user->id)->where('id',$request->id)->first();

        if(!$myCard) return back()->with(['error' => [__("Something is wrong in your card")]]);

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

        $public_key     = $this->api->config->strowallet_public_key;
        $base_url       = $this->api->config->strowallet_url;
        $mode           = $this->api->config->strowallet_mode??GlobalConst::SANDBOX;
        $form_params    = [
            'card_id'       => $myCard->card_id,
            'amount'        => $amount,
            'public_key'    => $public_key
        ];
        if ($mode === GlobalConst::SANDBOX) {
            $form_params['mode'] = "sandbox";
        }

        $client = new \GuzzleHttp\Client();

        $response               = $client->request('POST', $base_url.'fund-card/', [
            'headers'           => [
                'accept'        => 'application/json',
            ],
            'form_params'       => $form_params,
        ]);

        $result         = $response->getBody();
        $decodedResult  = json_decode($result, true);


        if(!empty($decodedResult['success'])  && $decodedResult['success'] == "success"){
            //added fund amount to card
            $myCard->balance += $amount;
            $myCard->save();
            $trx_id = 'CF'.getTrxNum();
            $sender = $this->insertCardFund($trx_id,$user,$wallet,$amount,$myCard,$charges);
            $this->insertFundCardCharge($charges,$user,$sender,$myCard->card_number,$amount);
            if($basic_setting->email_notification == true){
                $notifyDataSender = [
                    'trx_id'        => $trx_id,
                    'request_amount'=> get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                    'payable'       => get_amount($charges['payable'],$charges['from_currency'],$charges['precision_digit']),
                    'charges'       => get_amount( $charges['total_charge'],$charges['from_currency'],$charges['precision_digit']),
                    'card_amount'   => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                    'card_pan'      => $myCard->card_number??"---- ----- ---- ----",
                    'status'        => "Success",
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
                        'card_pan'          =>  $myCard->card_number??"---- ----- ---- ----",
                        'trx'               => $trx_id,
                        'time'              =>  now()->format('Y-m-d h:i:s A')
                    ]);
                }catch(Exception $e) {}
            }
            //admin notification
            $this->adminNotificationFund($trx_id,$charges,$amount,$user,$myCard);
            return redirect()->route("user.strowallet.virtual.card.index")->with(['success' => [__('Card Funded Successfully')]]);

        }else{
            return redirect()->back()->with(['error' => [@$decodedResult['message'].' ,'.__('Please Contact With Administration.')]]);
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
    public function insertFundCardCharge($charges,$user,$id,$card_number,$amount) {
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
                'message'       => __("Card fund successful card")." : ".$card_number.' '.get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                'image'         => files_asset_path('profile-default'),
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
           DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    /**
     * Transactions
     */
    public function cardTransaction($card_id) {
        $user = auth()->user();
        $card = StrowalletVirtualCard::where('user_id',$user->id)->where('card_id', $card_id)->first();
        $page_title = __("Virtual Card Transaction");
        $id = $card->card_id;
        $emptyMessage  = 'No Transaction Found!';
        $start_date = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-12 month" ) );
        $end_date = date('Y-m-d');
        $curl = curl_init();
        $public_key     = $this->api->config->strowallet_public_key;
        $base_url       = $this->api->config->strowallet_url;

        curl_setopt_array($curl, [
        CURLOPT_URL => $base_url . "card-transactions/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'public_key' => $public_key,
            'card_id' => $card->card_id,
        ]),
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "content-type: application/json"
        ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $result  = json_decode($response, true);

        if( isset($result['success']) == true && $result['success'] == true ){
            $data =[
                'status'        => true,
                'message'       => "Card Details Retrieved Successfully.",
                'data'          => $result['response'],
            ];
        }else{
            $data =[
                'status'        => false,
                'message'       => $result['message'] ?? 'Something is wrong! Contact With Admin',
                'data'          => null,
            ];
        }


        return view('user.sections.virtual-card-strowallet.trx',compact('page_title','card','data'));


    }

    //admin notification
    public function adminNotification($trx_id,$charges,$amount,$user,$v_card){
        $notification_content = [
            //email notification
            'subject' => __("Virtual Card (Buy Card)"),
            'greeting' => __("Virtual Card Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit'])."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['from_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['from_currency'],$charges['precision_digit'])."<br>".__("card Masked")." : ".$v_card->card_number??"---- ----- ---- ----"."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Virtual Card (Buy Card)")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id." ".__("request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit'])." ".__("card Masked")." : ".$v_card->card_number??"---- ----- ---- ----",

            //admin db notification
            'notification_type' =>  NotificationConst::CARD_BUY,
            'admin_db_title' => "Virtual Card Buy"." (".userGuard()['type'].")",
            'admin_db_message' => "Transaction ID"." : ".$trx_id.",".__("Request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit']).","."Card Masked"." : ".$v_card->card_number??"---- ----- ---- ----"." (".$user->email.")",
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
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit'])."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['from_currency'],$charges['precision_digit'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['from_currency'],$charges['precision_digit'])."<br>".__("card Masked")." : ".$myCard->masked_card??"---- ----- ---- ----"."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Virtual Card (Fund Amount)")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id." ".__("request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit'])." ".__("card Masked")." : ".$myCard->masked_card??"---- ----- ---- ----",

            //admin db notification
            'notification_type' =>  NotificationConst::CARD_FUND,
            'admin_db_title' => "Virtual Card Funded"." (".userGuard()['type'].")",
            'admin_db_message' => "Transaction ID"." : ".$trx_id.",".__("Request Amount")." : ".get_amount($amount,$charges['card_currency'],$charges['precision_digit']).","."Card Masked"." : ".$myCard->card_number??"---- ----- ---- ----"." (".$user->email.")",
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
        $data['card_platform']         = "StroWallet";
        $data['precision_digit']       = $sPrecision;


        return $data;

    }
    public function apiErrorHandle($apiErrors){
        $error = ['error' => []];
        if (isset($apiErrors)) {
            if (is_array($apiErrors)) {
                foreach ($apiErrors as $field => $messages) {
                    if (is_array($messages)) {
                        foreach ($messages as $message) {
                            $error['error'][] = $message;
                        }
                    } else {
                        $error['error'][] = $messages;
                    }
                }
            } else {
                $error['error'][] = $apiErrors;
            }
        }
        $errorMessages = array_map(function($message) {
            return rtrim($message, '.');
        }, $error['error']);

        $errorString = implode(', ', $errorMessages);
        $errorString .= '.';
        return back()->with(['error' => [$errorString ?? __("Something went wrong! Please try again.")]]);

    }
    public function webhookTransaction($card_id){
        $page_title = __("Webhook Logs");
        $user = userGuard()['user'];
        $data = StrowalletWebhookData::where('user_id',$user->id)->where('cardId',$card_id)->latest()->get();
        return view('user.sections.virtual-card-strowallet.webhook-trx',compact('page_title','data'));
    }
    public function getWebhookData(Request $request){
        $response_data = $request->all();
        $card = StrowalletVirtualCard::where('card_id',$response_data['cardId'])->first();
        if($card){
            //store data
            $st['parent_id']        =  $card->id;
            $st['user_id']          =  $card->user_id;
            $st['transaction_id']   =  $response_data['id'];
            $st['event']            =  $response_data['event'];
            $st['cardId']           =  $response_data['cardId'];
            $st['card_currency']    =  $card->currency ?? "USD";
            $st['data']             = $response_data ?? [];

            StrowalletWebhookData::create($st);

            logger("Webhook Data Created Successfully! Status: " . $response_data['event']);
        }else{
            logger("Webhook Data Created Failed!");
        }

    }

}
