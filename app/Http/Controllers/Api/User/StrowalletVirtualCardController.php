<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\GlobalConst;
use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Admin\Currency;
use App\Models\VirtualCardApi;
use App\Models\UserNotification;
use App\Http\Helpers\Api\Helpers;
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
use App\Notifications\User\VirtualCard\CreateMail;
use App\Notifications\User\VirtualCard\Fund;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class  StrowalletVirtualCardController extends Controller
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
        $user = auth()->user();
        $basic_settings = BasicSettings::first();
        $card_basic_info = [
            'card_create_limit' => @$this->api->card_limit,
            'card_back_details' => @$this->api->card_details,
            'card_bg' => get_image(@$this->api->image,'card-api'),
            'site_title' =>@$basic_settings->site_name,
            'site_logo' =>get_logo(@$basic_settings,'dark'),
            'site_fav' =>get_fav($basic_settings,'dark'),
        ];
        $myCards = StrowalletVirtualCard::where('user_id',$user->id)->latest()->limit($this->card_limit)->get()->map(function($data){
             $live_card_data = card_details($data->card_id,$this->api->config->strowallet_public_key,$this->api->config->strowallet_url);

            $basic_settings = BasicSettings::first();
            $statusInfo = [
                "block" =>      0,
                "unblock" =>     1,
                ];
            return[
                'id' => $data->id,
                'name' => $data->name_on_card,
                'card_number'       => $data->card_number ?? '',
                'card_id'           => $data->card_id,
                'expiry'            => $data->expiry ?? '',
                'cvv'               => $data->cvv ?? '',
                'card_status'       => $data->card_status,
                'balance'           => getAmount(updateStroWalletCardBalance(auth()->user(),$data->card_id,$live_card_data),2),
                'currency'          => $data->currency,
                'card_back_details' => @$this->api->card_details,
                'site_title'        => @$basic_settings->site_name,
                'site_logo'         => get_logo(@$basic_settings,'dark'),
                'site_fav'          => get_fav($basic_settings,'dark'),
                'status'            => $data->is_active,
                'is_default'        => $data->is_default,
                'status_info'       => (object)$statusInfo ,
            ];
        });
        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->get()->map(function($data){
            return [
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
        $transactions = Transaction::auth()->virtualCard()->latest()->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];

            return[
                'id' => $item->id,
                'trx' => $item->trx_id,
                'transaction_type' => "Virtual Card".'('. @$item->remark.')',
                'request_amount' => get_amount($item->request_amount,$item->details->charges->card_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                'payable' => get_amount($item->payable,$item->details->charges->from_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                'exchange_rate' => get_amount(1,$item->details->charges->card_currency??get_default_currency_code()) ." = ". get_amount($item->details->charges->exchange_rate??1,$item->details->charges->from_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                'total_charge' => get_amount($item->charge->total_charge,$item->details->charges->from_currency??get_default_currency_code()),
                'card_amount' => get_amount(@$item->details->card_info->amount??@$item->details->card_info->balance,$item->details->charges->card_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                'card_number' => $item->details->card_info->card_pan??$item->details->card_info->maskedPan??$item->details->card_info->card_number??"---- ---- ---- ----",
                'current_balance' => get_amount($item->available_balance,$item->details->charges->from_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                'status' => $item->stringStatus->value,
                'status_value' => $item->status,
                'date_time' => $item->created_at ,
                'status_info' =>(object)$statusInfo ,

            ];
        });

        $customer_email = $user->strowallet_customer->customerEmail??false;
        if($customer_email === false){
            $customer_card  = 0;
        }else{
            $customer_card  = StrowalletVirtualCard::where('user_id',$user->id)->count();
        }
        $supported_currency = support_currencies(['USD']);
        $get_remaining_fields = [
            'transaction_type'  =>  PaymentGatewayConst::VIRTUALCARD,
            'attribute'         =>  PaymentGatewayConst::RECEIVED,
        ];

        $data =[
            'base_curr'                 => get_default_currency_code(),
            'base_curr_rate'            => get_amount(get_default_currency_rate(),null,get_wallet_precision()),
            'get_remaining_fields'      => (object) $get_remaining_fields,
            'supported_currency'        => $supported_currency,
            'card_create_action'        => $customer_card <  $this->card_limit ? true : false,
            'strowallet_customer_info'  => $user->strowallet_customer === null ? true : false,
            'card_basic_info'           => (object) $card_basic_info,
            'myCards'                   => $myCards,
            'user'                      => $user,
            'cardCharge'                => (object)$cardCharge,
            'transactions'              => $transactions,
        ];
        $message =  ['success'=>[__('Virtual Card')]];
        return Helpers::success($data,$message);
    }
    //charge
    public function charges(){
        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->get()->map(function($data){
            return [
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

        $data =[
            'base_curr' => get_default_currency_code(),
            'cardCharge'=>(object)$cardCharge
            ];
            $message =  ['success'=>[__('Fess & Charges')]];
            return Helpers::success($data,$message);

    }
    //card details
    public function cardDetails(){
        $validator = Validator::make(request()->all(), [
            'card_id'     => "required|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $card_id = request()->card_id;
        $user = auth()->user();
        $myCard = StrowalletVirtualCard::where('user_id',$user->id)->where('card_id',$card_id)->first();
        if(!$myCard){
            $error = ['error'=>[__("Something is wrong in your card")]];
            return Helpers::error($error);
        }
        if($myCard->card_status == 'pending'){
            $card_details   = card_details($card_id,$this->api->config->strowallet_public_key,$this->api->config->strowallet_url);

            if($card_details['status'] == false){
                $error = ['error'=>[__("Your Card Is Pending! Please Contact With Admin")]];
                return Helpers::error($error);
            }

            $myCard->user_id                   = authGuardApi()['user']->id;
            $myCard->card_status               = $card_details['data']['card_detail']['card_status'];
            $myCard->card_number               = $card_details['data']['card_detail']['card_number'];
            $myCard->last4                     = $card_details['data']['card_detail']['last4'];
            $myCard->cvv                       = $card_details['data']['card_detail']['cvv'];
            $myCard->expiry                    = $card_details['data']['card_detail']['expiry'];
            $myCard->save();
        }
        $myCards = StrowalletVirtualCard::where('card_id',$card_id)->where('user_id',$user->id)->get()->map(function($data){
            $basic_settings = BasicSettings::first();
            return[
                'id' => $data->id,
                'name' => $data->name_on_card,
                'card_id' => $data->card_id,
                'card_number'       => $data->card_number ?? '',
                'card_brand' => $data->card_brand,
                'card_user_id' => $data->card_user_id,
                'expiry' => $data->expiry,
                'cvv' => $data->cvv,
                'card_type' => ucwords($data->card_type),
                'city' => $data->user->strowallet_customer->city??"",
                'state' => $data->user->strowallet_customer->state??"",
                'zip_code' => $data->user->strowallet_customer->zipCode??"",
                'amount' => getAmount($data->balance,2),
                'currency' => $data->currency,
                'card_back_details' => @$this->api->card_details,
                'card_bg' => get_image(@$this->api->image,'card-api'),
                'site_title' =>@$basic_settings->site_name,
                'site_logo' =>get_logo(@$basic_settings,'dark'),
                'status' => $data->is_active,
                'is_default' => $data->is_default,
            ];
        })->first();
        $business_address =[
            [
                'id' => 1,
                'label_name' => __("Billing Country"),
                'value' => "United State",
            ],
            [
                'id' => 2,
                'label_name' => __("Billing City"),
                'value' => "Miami",
            ],
            [
                'id' => 3,
                'label_name' => __("Billing State"),
                'value' => "3401 N. Miami, Ave. Ste 230",
            ],
            [
                'id' => 4,
                'label_name' => __("Billing Zip Code"),
                'value' => "33127",
            ],

        ];
        $data =[
            'base_curr' => get_default_currency_code(),
            'base_curr_rate' => get_default_currency_rate(),
            'myCards'=> $myCards,
            'business_address'=> $business_address,
        ];
        $message =  ['success'=>[__('card Details')]];
        return Helpers::success($data,$message);
    }
    public function makeDefaultOrRemove(Request $request) {
        $validator = Validator::make($request->all(), [
            'card_id'     => "required|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $user = auth()->user();
        $targetCard =  StrowalletVirtualCard::where('card_id',$validated['card_id'])->where('user_id',$user->id)->first();
        if(!$targetCard){
            $error = ['error'=>[__("Something is wrong in your card")]];
            return Helpers::error($error);
        };
        $withOutTargetCards =  StrowalletVirtualCard::where('id','!=',$targetCard->id)->where('user_id',$user->id)->get();
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
            $message =  ['success'=>[__('Status Updated Successfully')]];
            return Helpers::onlysuccess($message);

        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    // card transactions
    public function cardTransaction() {
        $validator = Validator::make(request()->all(), [
            'card_id'     => "required|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $card_id = request()->card_id;
        $user = auth()->user();
        $card = StrowalletVirtualCard::where('user_id',$user->id)->where('card_id',$card_id)->first();
        if(!$card){
            $error = ['error'=>[__("Something is wrong in your card")]];
            return Helpers::error($error);
        }

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
            $message = ['success' => [__('Virtual Card Transaction')]];
            return Helpers::success($result['response'], $message);
        }else{
            $result['response']  = [
                'card_transactions' => []
            ];
            $message = ['success' => [__('Virtual Card Transaction')]];
            return Helpers::success($result['response'], $message);
        }

    }
    //card block
    public function cardBlock(Request $request){
        $validator = Validator::make($request->all(), [
            'card_id'     => "required|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $card_id = $request->card_id;
        $user = auth()->user();
        $status = 'freeze';
        $card = StrowalletVirtualCard::where('user_id',$user->id)->where('card_id',$card_id)->first();
        if(!$card){
            $error = ['error'=>[__("Something is wrong in your card")]];
            return Helpers::error($error);
        }
        if($card->is_active == false){
            $error = ['error'=>[__('Sorry,This Card Is Already Freeze')]];
            return Helpers::error($error);
        }

        $client = new \GuzzleHttp\Client();
        $public_key     = $this->api->config->strowallet_public_key;
        $base_url       = $this->api->config->strowallet_url;

        $response = $client->request('POST', $base_url.'action/status/?action='.$status.'&card_id='.$card->card_id.'&public_key='.$public_key, [
        'headers' => [
            'accept' => 'application/json',
        ],
        ]);

        $result = $response->getBody();
        $data  = json_decode($result, true);

        if (isset($data)) {
            if (isset($data['status']) && $data['status'] == 'true') {
                $card->is_active = 0;
                $card->save();
                $message =  ['success'=>[__('Card Freeze successfully')]];
                return Helpers::onlysuccess($message);
            }else{
                return Helpers::error($data['error']??__("Something went wrong! Please try again."));
            }
        }else{
            return Helpers::error($data['error']??__("Something went wrong! Please try again."));
        }

    }
    //unblock card
    public function cardUnBlock(Request $request){
        $validator  = Validator::make($request->all(), [
            'card_id'     => "required|string",
        ]);
        if($validator->fails()){
            $error  =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $card_id    = $request->card_id;
        $user       = auth()->user();
        $status     = 'unfreeze';
        $card       = StrowalletVirtualCard::where('user_id',$user->id)->where('card_id',$card_id)->first();
        if(!$card){
            $error  = ['error'=>[__("Something is wrong in your card")]];
            return Helpers::error($error);
        }
        if($card->is_active == true){
            $error = ['error'=>[__('Sorry,This Card Is Already Unfreeze')]];
            return Helpers::error($error);
        }
        $client         = new \GuzzleHttp\Client();
        $public_key     = $this->api->config->strowallet_public_key;
        $base_url       = $this->api->config->strowallet_url;

        $response = $client->request('POST', $base_url.'action/status/?action='.$status.'&card_id='.$card->card_id.'&public_key='.$public_key, [
        'headers' => [
            'accept' => 'application/json',
        ],
        ]);

        $result = $response->getBody();
        $data  = json_decode($result, true);

        if (isset($data['status'])) {
            $card->is_active = 1;
            $card->save();
            $message =  ['success'=>[__('Card UnFreeze successfully')]];
            return Helpers::onlysuccess($message);
        }else{
            $error = ['error' => $data['message']];
            return Helpers::error(['error' => [$data['message']]]);
        }

    }

    public function createPage(){
        $user       = authGuardApi()['user'];
        $customer_exist_status  = $user->strowallet_customer != null ? true : false;
        $customer_create_fields =  [
            [
                'id'    => 1,
                'field_name' => "first_name",
                'label_name' => __("first Name"),
                'site_label' => __("Should match with your ID"),
                'type'       => 'text',
                'required'  => true
            ],
            [
                'id'    => 2,
                'field_name' => "last_name",
                'label_name' => __("last Name"),
                'site_label' => __("Should match with your ID"),
                'type'       => 'text',
                'required'  => true
            ],

            [
                'id'    => 3,
                'field_name' => "phone_code",
                'label_name' => __("Phone Code"),
                'site_label' => "",
                'type'       => 'number',
                'required'  => true
            ],
            [
                'id'    => 4,
                'field_name' => "phone",
                'label_name' => __("Phone"),
                'site_label' => "",
                'type'       => 'number',
                'required'  => true
            ],
            [
                'id'    => 5,
                'field_name' => "customer_email",
                'label_name' => __("Email"),
                'site_label' => "",
                'type'       => 'email',
                'required'  => true
            ],

            [
                'id'    => 6,
                'field_name' => "date_of_birth",
                'label_name' => __("date Of Birth"),
                'site_label' =>__("Should match with your ID"),
                'type'       => 'date',
                'required'  => true
            ],
            [
                'id'    => 7,
                'field_name' => "house_number",
                'label_name' => __("house Number"),
                'site_label' => "",
                'type'       => 'text',
                'required'  => true
            ],
            [
                'id'    => 8,
                'field_name' => "address",
                'label_name' => __("address"),
                'site_label' => "",
                'type'       => 'text',
                'required'  => true
            ],
            [
                'id'    => 9,
                'field_name' => "zip_code",
                'label_name' => __("zip Code"),
                'site_label' => "",
                'type'       => 'text',
                'required'  => true
            ],
            [
                'id'    => 10,
                'field_name' => "id_image_font",
                'label_name' => __("ID Card Image (Font Side)"),
                'site_label' => __("NID/Passport"),
                'type'       => 'file',
                'required'  => true
            ],
            [
                'id'    => 11,
                'field_name' => "user_image",
                'label_name' => __("Your Photo"),
                'site_label' => __("Should show your face and must be match with your ID"),
                'type'       => 'file',
                'required'  => true
            ],
        ];
        if($user->strowallet_customer){
            $customer_exist =  $user->strowallet_customer;
        }else{
            $customer_exist =[
                "customerEmail"     => "",
                "firstName"         => "",
                "lastName"          => "",
                "phoneNumber"       => "",
                "city"              => "",
                "state"             => "",
                "country"           => "",
                "line1"             => "",
                "zipCode"           => "",
                "houseNumber"       => "",
                "idNumber"          => "",
                "idType"            => "",
                "idImage"           => "",
                "userPhoto"         => "",
                "customerId"        => "",
                "dateOfBirth"       => "",
                "status"            => ""
            ];
            $customer_exist = (object) $customer_exist;
        }

        $customer_kyc_status_can_be    = [
            'low kyc',
            'unreview kyc',
            'high kyc',
        ];
        $customer_kyc_status    = $customer_exist->status ?? "";
        $customer_low_kyc_text  = __("Thank you for submitting your KYC information. Your details are currently under review. We will notify you once the verification is complete. Please note that the creation of your virtual card will proceed after your KYC is approved.");
        $card_create_fields =  [
            [
                'id'    => 1,
                'field_name' => "name_on_card",
                'label_name' => __("Card Holder's Name"),
                'site_label' => "",
                'type'       => 'text',
                'required'  => true
            ],
            [
                'id'    => 2,
                'field_name' => "card_amount",
                'label_name' =>__("Amount"),
                'site_label' => "",
                'type'       => 'number',
                'required'  => true
            ],
            [
                'id'    => 3,
                'field_name' => "currency",
                'label_name' =>__("Select Currency"),
                'site_label' => "",
                'type'       => 'select',
                'required'  => true
            ],
            [
                'id'    => 4,
                'field_name' => "from_currency",
                'label_name' =>__("From Wallet"),
                'site_label' => "",
                'type'       => 'select',
                'required'  => true
            ]
        ];
        $data =[
            'customer_exist_status'     => $customer_exist_status,
            'customer_create_fields'    => (array)$customer_create_fields,
            'customer_exist'            => $customer_exist,
            'customer_kyc_status_can_be'=> $customer_kyc_status_can_be,
            'customer_kyc_status'       => $customer_kyc_status,
            'customer_low_kyc_text'     => $customer_low_kyc_text,
            'card_create_fields'        => $card_create_fields,
        ];
        $message =  ['success'=>[__('Data Fetch Successful')]];
        return Helpers::success($data,$message);

    }
    public function updateCustomerStatus(){
        $user       = authGuardApi()['user'];
        if($user->strowallet_customer != null){
            //get customer api response
            $customer = $user->strowallet_customer;
            $customerEmail = $customer->customerEmail??"";
            $customerId = $customer->customerId??"";

            $getCustomerInfo = get_customer($this->api->config->strowallet_public_key,$this->api->config->strowallet_url,$customerId,$customerEmail);
            if( $getCustomerInfo['status'] == false){
                $error  = ['error'=>[$getCustomerInfo['message'] ?? __("Something went wrong! Please try again.")]];
                return Helpers::error($error);
            }
            $customer               = (array) $customer;
            $customer_status_info   =  $getCustomerInfo['data'];

            foreach ($customer_status_info as $key => $value) {
                $customer[$key] = $value;
            }
            $user->strowallet_customer = (object) $customer;
            $user->save();
        }
        $message =  ['success'=>[__('Customer Status Updated Successfully.')]];
        return Helpers::onlysuccess($message);

    }
    public function createCustomer(Request $request){
        $validator = Validator::make($request->all(), [
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
        ]);

        if ($validator->fails()) {
            $error  =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $user       = authGuardApi()['user'];
        $validated['phone'] = $user->full_mobile;
        try{
            if($user->strowallet_customer == null){

                if($request->hasFile("id_image_font")) {
                    $image = upload_file($validated['id_image_font'],'card-kyc-images');
                    $upload_image = upload_files_from_path_dynamic([$image['dev_path']],'card-kyc-images');
                    // delete_file($image['dev_path']);
                    $validated['id_image_font']     = $upload_image;
                }

                //user image
                if($request->hasFile("user_image")) {
                    $image = upload_file($validated['user_image'],'card-kyc-images');
                    $upload_image = upload_files_from_path_dynamic([$image['dev_path']],'card-kyc-images');
                    // delete_file($image['dev_path']);
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

            $message =  ['success'=>[__('Customer has been created successfully.')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e){
            $error  = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }


    }
    public function updateCustomer(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name'        => ['required', 'string', 'regex:/^[^0-9\W]+$/'], // First name validation
            'last_name'         => ['required', 'string', 'regex:/^[^0-9\W]+$/'],  // Last name validation
            'id_image_font'     => "nullable|image|mimes:jpg,png,svg,webp",
            'user_image'        => "nullable|image|mimes:jpg,png,svg,webp",
        ], [
            'first_name.regex'  => __('The First Name field should only contain letters and cannot start with a number or special character.'),
            'last_name.regex'   => __('The Last Name field should only contain letters and cannot start with a number or special character.'),
        ]);

        if ($validator->fails()) {
            $error  =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $user      = authGuardApi()['user'];
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
                if ($updateCustomer['status'] == false) {
                    $customer_kyc->delete();
                    return $this->apiErrorHandle($updateCustomer["message"]);
                }

                //get customer api response
                $customer = $user->strowallet_customer;
                $getCustomerInfo = get_customer($this->api->config->strowallet_public_key,$this->api->config->strowallet_url,$updateCustomer['data']['customerId']??"",$updateCustomer['data']['customerEmail']??"");
                if( $getCustomerInfo['status'] == false){
                    $customer_kyc->delete();
                    $error  = ['error'=>[$getCustomerInfo['message'] ?? __("Something went wrong! Please try again.")]];
                    return Helpers::error($error);
                }
                $customer               = (array) $customer;
                $customer_status_info   =  $getCustomerInfo['data'];

                foreach ($customer_status_info as $key => $value) {
                    $customer[$key] = $value;
                }
                $user->strowallet_customer = (object) $customer;
                $user->save();

            }else{
                $error  = ['error'=>[__("Something went wrong! Please try again.")]];
                return Helpers::error($error);
            }
            $message =  ['success'=>[__('Customer has been updated successfully.')]];
            return Helpers::onlysuccess($message);

        }catch(Exception $e){
            $error  = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }

    //card buy
    public function cardBuy(Request $request){
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'name_on_card'      => 'required|string|min:4|max:50',
            'card_amount'       => 'required|numeric|gt:0',
            'currency'          => "required|string|exists:currencies,code",
            'from_currency'     => "required|string|exists:currencies,code",
        ]);

        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }

        $formData   = $request->all();
        $amount = $request->card_amount;
        $currency = $formData['currency'];
        $basic_setting = BasicSettings::first();

        $supported_currency = ['USD'];
        if( !in_array($currency,$supported_currency??[])){
            $error = ['error'=>[$currency." ". __("Currency isn't supported for creating virtual card, Please contact")]];
            return Helpers::error($error);
        }

        $wallet = UserWallet::where('user_id',$user->id)->whereHas("currency",function($q) use ($formData) {
            $q->where("code",$formData['from_currency'])->active();
        })->active()->first();
        if(!$wallet){
            $error = ['error'=>[__('User wallet not found')]];
            return Helpers::error($error);
        }

        $card_currency = Currency::active()->where('code',$formData['currency'])->first();
        if(!$card_currency){
            $error = ['error'=>[__('Card Currency Not Found')]];
            return Helpers::error($error);
        }

        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        $charges = $this->cardCharges($formData['card_amount'],$cardCharge,$wallet,$card_currency);

        $minLimit =  $cardCharge->min_limit *  $charges['card_currency_rate'];
        $maxLimit =  $cardCharge->max_limit *  $charges['card_currency_rate'];

        if($amount < $minLimit || $amount > $maxLimit){
            $error = ['error'=>[__('Please follow the transaction limit')]];
            return Helpers::error($error);
        }
        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('user_id',$wallet->user->id,PaymentGatewayConst::VIRTUALCARD,$wallet->currency,$amount,$cardCharge,PaymentGatewayConst::RECEIVED);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
           $error = ['error'=>[__($errorData['message']??"")]];
            return Helpers::error($error);
        }
        if($charges['payable'] > $wallet->balance){
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
        }

        $customer = $user->strowallet_customer;
        if(!$customer){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        if($customer->status != GlobalConst::CARD_HIGH_KYC_STATUS){
            $error = ['error'=>[__("Your virtual card will proceed after your KYC is approved")]];
            return Helpers::error($error);
        }

        $customer_email = $user->strowallet_customer->customerEmail??false;
        if($customer_email === false){
            $customer_card  = 0;
        }else{
            $customer_card  = StrowalletVirtualCard::where('user_id',$user->id)->count();
        }

        if($customer_card >= $this->card_limit){
            $error = ['error'=>[__("Sorry! You can not create more than")." ".$this->card_limit ." ".__("card using the same email address.")]];
            return Helpers::error($error);
        }

        // for live code
        $created_card = create_strowallet_virtual_card($user,$request->card_amount,$customer,$this->api->config->strowallet_public_key,$this->api->config->strowallet_url,$formData);
        if($created_card['status'] == false){
            $error = ['error'=>[$created_card['message']]];
            return Helpers::error($error);
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
            $this->insertBuyCardCharge($charges,$user,$sender,$strowallet_card->card_number??"---- ----- ---- ----");

            if($basic_setting->email_notification == true){
                $notifyDataSender = [
                    'trx_id'            => $trx_id,
                    'title'             => "Virtual Card (Buy Card)",
                    'request_amount'    => get_amount($amount,$charges['card_currency'],$charges['precision_digit']),
                    'payable'           =>  get_amount($charges['payable'],$charges['from_currency'],$charges['precision_digit']),
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
            $message =  ['success'=>[__('Virtual Card Buy Successfully')]];
            return Helpers::onlysuccess($message);

        }catch(Exception $e){

            $error =  ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
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
               'transaction_id'     => $id,
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
                    (new PushNotificationHelper())->prepareApi([$user->id],[
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
     * Card Fund
     */
    public function cardFundConfirm(Request $request){
        $validator = Validator::make($request->all(), [
            'card_id' => 'required',
            'fund_amount' => 'required|numeric|gt:0',
            'currency'      => "required|string|exists:currencies,code",
            'from_currency' => "required|string|exists:currencies,code",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        $amount = $request->fund_amount;

        $myCard =  StrowalletVirtualCard::where('user_id',$user->id)->where('card_id',$request->card_id)->first();
        if(!$myCard){
            $error = ['error'=>[__("Something is wrong in your card")]];
            return Helpers::error($error);
        }

        $wallet = UserWallet::where('user_id',$user->id)->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['from_currency'])->active();
        })->active()->first();
        if(!$wallet){
            $error = ['error'=>[__('User wallet not found')]];
            return Helpers::error($error);
        }
        $cardCharge = TransactionSetting::where('slug','reload_card')->where('status',1)->first();
        $card_currency = Currency::active()->where('code',$validated['currency'])->first();
        if(!$card_currency){
            $error = ['error'=>[__('Card Currency Not Found')]];
            return Helpers::error($error);
        }
        $charges = $this->cardCharges($validated['fund_amount'],$cardCharge,$wallet,$card_currency);
        $minLimit =  $cardCharge->min_limit *  $charges['card_currency_rate'];
        $maxLimit =  $cardCharge->max_limit *  $charges['card_currency_rate'];

        if($amount < $minLimit || $amount > $maxLimit){
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }
        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('user_id',$wallet->user->id,PaymentGatewayConst::VIRTUALCARD,$wallet->currency,$amount,$cardCharge,PaymentGatewayConst::RECEIVED);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
            $error = ['error'=>[__($errorData['message'] ?? __("Something went wrong! Please try again."))]];
            return Helpers::error($error);
        }

        if($charges['payable'] > $wallet->balance){
            $error = ['error'=>[__("Your Wallet Balance Is Insufficient")]];
            return Helpers::error($error);
        }

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
            $message =  ['success'=>[__('Card Funded Successfully')]];
            return Helpers::onlysuccess($message);

        }else{
            $error = ['error'=>[@$decodedResult['message'].' ,'.__('Please Contact With Administration.')]];
            return Helpers::error($error);
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
                    (new PushNotificationHelper())->prepareApi([$user->id],[
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

        $data['percent_charge']        = ($amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']          = $wallet->currency->rate * $charges->fixed_charge ?? 0;
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

        $error = ['error' => [$errorString ?? __("Something went wrong! Please try again.")]];
        return Helpers::error($error);

    }
    public function webhookTransaction(){
        $validator = Validator::make(request()->all(), [
            'card_id'     => "required|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $card_id = request()->card_id;
        $user = authGuardApi()['user'];
        $myCard = StrowalletVirtualCard::where('user_id',$user->id)->where('card_id',$card_id)->first();
        if(!$myCard){
            $error = ['error'=>[__("Something is wrong in your card")]];
            return Helpers::error($error);
        }
        $logs = StrowalletWebhookData::where('user_id',$user->id)->where('cardId',$card_id)->latest()->get()->map(function($item){
           if($item->event === global_const()::TERMINATED){
                return[
                    'event_type'                => $item->event,
                    'event'                     => str_replace(' ', ' ', ucwords(str_replace('.', ' ', $item->event))),
                    'amount'                    => get_amount($item->data->amount ?? 0,$item->card_currency,get_wallet_precision()),
                    'transition_id'             => $item->transaction_id ?? "",
                    'card_id'                   => $item->cardId ?? "",
                    'reference'                 => $item->data->reference ?? "",
                    'status'                    => $item->data->status ?? "",
                    'balance_before_termination'  => get_amount($item->data->balanceBeforeTermination ?? 0,$item->card_currency,get_wallet_precision())
                ];
           }elseif($item->event === global_const()::CROSSBORDER){
                return[
                    'event_type'                => $item->event,
                    'event'                     => str_replace(' ', ' ', ucwords(str_replace('.', ' ', $item->event))),
                    'amount'                    => get_amount($item->data->amount ?? 0,$item->card_currency,get_wallet_precision()),
                    'transition_id'             => $item->transaction_id ?? "",
                    'card_id'                   => $item->cardId ?? "",
                    'reference'                 => $item->data->reference ?? "",
                    'charge_amount'             => get_amount($item->data->chargedAmount ?? 0,$item->card_currency,get_wallet_precision()),
                    'narrative'                 => $item->data->narrative ?? "",
                    'status'                    => $item->data->status ?? "",
                    'created_at'                => $item->data->createdAt ?? "",
                ];
           }elseif($item->event === global_const()::DECLINED){
                return[
                    'event_type'                => $item->event,
                    'event'                     => str_replace(' ', ' ', ucwords(str_replace('.', ' ', $item->event))),
                    'amount'                    => get_amount($item->data->amount ?? 0,$item->card_currency,get_wallet_precision()),
                    'transition_id'             => $item->transaction_id ?? "",
                    'card_id'                   => $item->cardId ?? "",
                    'reference'                 => $item->data->reference ?? "",
                    'narrative'                 => $item->data->narrative ?? "",
                    'reason'                    => $item->data->reason ?? "",
                    'status'                    => $item->data->status ?? "",
                    'created_at'                => $item->data->createdAt ?? "",
                ];
           }

        });
        $data =[
            'transactions'=> $logs,
        ];
        $message =  ['success'=>[__('Webhook Logs')]];
        return Helpers::success($data,$message);

    }
}
