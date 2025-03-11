<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Constants\GlobalConst;
use App\Constants\PaymentGatewayConst;
use Exception;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Helpers\Api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Models\Admin\TransactionSetting;
use App\Models\Merchants\MerchantNotification;
use App\Models\Merchants\MerchantWallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Providers\Admin\BasicSettingsProvider;
use App\Traits\AdminNotifications\AuthNotifications;




class UserController extends Controller
{
    use AuthNotifications;

    public function home(){
        $user = auth()->user();
        $money_out_amount = amountOnBaseCurrency(Transaction::merchantAuth()->where('type', PaymentGatewayConst::TYPEMONEYOUT)->where('status', 1)->get());
        $receive_money = amountOnBaseCurrency(Transaction::merchantAuth()->where('type', PaymentGatewayConst::TYPEMAKEPAYMENT)->where('status', 1)->where('attribute','RECEIVED')->get());
        $gateway_amount = Transaction::merchantAuth()->where('type', PaymentGatewayConst::MERCHANTPAYMENT)->where('status', 1)->where('attribute','RECEIVED')->sum('request_amount');
        $total_transaction = Transaction::merchantAuth()->where('status', 1)->count();
        $transactions = Transaction::merchantAuth()->latest()->take(5)->get()->map(function($item){
            if($item->type == payment_gateway_const()::TYPEMONEYOUT){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' =>"WITHDRAW",
                    'request_amount' => get_amount($item->request_amount,withdrawCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                    'payable' =>  get_amount($item->details->charges->payable??$item->request_amount,withdrawCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                    'status' => $item->stringStatus->value ,
                    'remark' => $item->remark??"",
                    'date_time' => $item->created_at,
                ];

            }elseif($item->type == payment_gateway_const()::MERCHANTPAYMENT){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_amount($item->request_amount,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency??null)),
                    'payable' => get_amount($item->details->charges->receiver_amount,$item->details->charges->receiver_currency,get_wallet_precision($item->creator_wallet->currency??null)),
                    'status' => $item->stringStatus->value ,
                    'remark' => $item->remark??"",
                    'date_time' => $item->created_at ,

                ];

            }elseif($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' =>  get_transaction_numeric_attribute($item->attribute).get_amount($item->request_amount,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'payable' => get_amount(@$item->payable,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'remark' => $item->remark??"",
                    'status' => $item->stringStatus->value,
                    'date_time' => $item->created_at,

                ];

            }elseif($item->type == payment_gateway_const()::TYPEMAKEPAYMENT){
                if($item->attribute == payment_gateway_const()::SEND){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'request_amount' => get_amount($item->details->charges->sender_amount??$item->request_amount,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'payable' => get_amount($item->details->charges->payable??$item->payable,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'remark' => $item->remark??"",
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                    ];
                }elseif($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'request_amount' => get_amount(@$item->details->charges->receiver_amount,$item->details->charges->receiver_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)) ,
                        'payable' => get_amount($item->details->charges->payable??$item->payable,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'remark' => $item->remark??"",
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                    ];

                }

            }elseif($item->type == payment_gateway_const()::TYPEPAYLINK){
                if($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => $item->id,
                        'type' =>$item->attribute,
                        'trx' => $item->trx_id,
                        'transaction_type' => $item->type,
                        'request_amount' => get_amount($item->request_amount,@$item->details->charge_calculation->receiver_currency_code,get_wallet_precision($item->creator_wallet->currency)),
                        'payable' => get_amount($item->details->charge_calculation->conversion_payable??$item->details->charge_calculation->receiver_amount,@$item->details->charge_calculation->receiver_currency_code,get_wallet_precision($item->creator_wallet->currency)),
                        'remark' => $item->remark??"",
                        'status' => $item->stringStatus->value ,
                        'date_time' => $item->created_at ,
                    ];
                }elseif($item->attribute == payment_gateway_const()::SEND){
                    return[
                        'id' => $item->id,
                        'type' =>$item->attribute,
                        'trx' => $item->trx_id,
                        'transaction_type' => $item->type,
                        'request_amount' => get_amount($item->request_amount,@$item->details->charge_calculation->receiver_currency_code,get_wallet_precision($item->creator_wallet->currency)),
                        'payable' => get_amount($item->details->charge_calculation->sender_payable,@$item->details->charge_calculation->sender_cur_code,get_wallet_precision($item->creator_wallet->currency)),
                        'remark' => $item->remark??"",
                        'status' => $item->stringStatus->value ,
                        'date_time' => $item->created_at ,
                    ];
                }
            }elseif($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_amount($item->request_amount,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'payable' => get_amount($item->payable,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'remark' => $item->remark??"",
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at ,
                ];
            }
        });

        $module_access =[
            'receive_money'     => module_access_merchant_api('merchant-receive-money'),
            'withdraw_money'    => module_access_merchant_api('merchant-withdraw-money'),
            'developer_api_key' => module_access_merchant_api('merchant-api-key'),
            'gateway_setting'   => module_access_merchant_api('merchant-gateway-settings'),
            'pay_link'          => module_access_merchant_api('merchant-pay-link'),
            'money-exchange'    => module_access_merchant_api('merchant-money-exchange')
        ];
        $basic_settings = BasicSettingsProvider::get();
        if(!$basic_settings) {
            $message = ['error'=>[__("Basic setting not found!")]];
            return Helpers::error($message);
        }
        $notification_config = $basic_settings->push_notification_config;

        if(!$notification_config) {
            $message = ['error'=>[__("Notification configuration not found!")]];
            return Helpers::error($message);
        }

        $pusher_credentials = [
            "instanceId" => $notification_config->instance_id ?? '',
            "secretKey" => $notification_config->primary_key ?? '',
        ];

        $data =[
            'base_curr'                 => get_default_currency_code(),
            'pusher_credentials'        => (object)$pusher_credentials,
            'module_access'             => (object)$module_access,
            'userWallets'               => user_wallets(authGuardApi(),'merchant_id',8),
            'default_image'             => "public/backend/images/default/profile-default.webp",
            "image_path"                => "public/frontend/merchant",
            'merchant'                  => $user,
            'totalMoneyOut'             => get_amount($money_out_amount,get_default_currency_code(),get_wallet_precision()),
            'receiveMoney'              => get_amount($receive_money,get_default_currency_code(),get_wallet_precision()),
            'gateway_amount'            => get_amount($gateway_amount,get_default_currency_code(),get_wallet_precision()),
            'total_transaction'         => $total_transaction,
            'transactions'              => $transactions,
        ];
        $message =  ['success'=>[__('Merchant Dashboard')]];
        return Helpers::success($data,$message);
    }
    public function profile(){
        $user = auth()->user();
        $data =[
            'default_image'    => "public/backend/images/default/profile-default.webp",
            "image_path"  =>  "public/frontend/merchant",
            'merchant'         =>   $user,
        ];
        $message =  ['success'=>[__('Merchant Profile')]];
        return Helpers::success($data,$message);
    }
    public function profileUpdate(Request $request){
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'firstname'     => "required|string|max:60",
            'lastname'      => "required|string|max:60",
            'business_name' => "required|string|max:60",
            'email'         =>  $user->registered_by == GlobalConst::EMAIL ? "nullable": "required|email|max:100",
            'country'       =>  $user->registered_by == GlobalConst::PHONE ? "nullable": "required|string|max:50",
            'phone_code'    =>  $user->registered_by == GlobalConst::PHONE ? "nullable": "required|string|max:20",
            'phone'         =>  $user->registered_by == GlobalConst::PHONE ? "nullable": "required|string|max:20|unique:merchants,mobile,".$user->id,
            'state'         => "nullable|string|max:50",
            'city'          => "nullable|string|max:50",
            'zip_code'      => "nullable|string",
            'address'       => "nullable|string|max:250",
            'image'         => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $data = $request->all();
        $mobileCode = remove_speacial_char($data['phone_code']);
        $mobile = remove_speacial_char($data['phone']);

        $validated['firstname']     = $data['firstname'];
        $validated['lastname']      = $data['lastname'];
        $validated['business_name'] = $data['business_name'];
        $validated['mobile']        = $user->registered_by == GlobalConst::PHONE ? $user->mobile : remove_special_char($data['phone']);
        $validated['mobile_code']   = $user->registered_by == GlobalConst::PHONE ? $user->mobile_code : remove_special_char($data['phone_code']);
        $complete_phone             = $mobileCode.$mobile;
        $validated['full_mobile']   = $complete_phone;
        $validated['email']         = $user->registered_by == GlobalConst::EMAIL ? $user->email : $data['email'];

        $validated['address']       = [
           'country'    => $user->registered_by == GlobalConst::PHONE ? $user->address->country ??"" : $data['country'],
            'state'     => $data['state'] ?? "",
            'city'      => $data['city'] ?? "",
            'zip'       => $data['zip_code'] ?? "",
            'address'   => $data['address'] ?? "",
        ];


        if($request->hasFile("image")) {
            if($user->image == 'default.png'){
                $oldImage = null;
            }else{
                $oldImage = $user->image;
            }
            $image = upload_file($data['image'],'merchant-profile', $oldImage);
            $upload_image = upload_files_from_path_dynamic([$image['dev_path']],'merchant-profile');
            delete_file($image['dev_path']);
            $validated['image']     = $upload_image;
        }

        try{
            $user->update($validated);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Profile successfully updated!')]];
        return Helpers::onlysuccess($message);
    }
    public function passwordUpdate(Request $request) {

        $basic_settings = BasicSettingsProvider::get();
        $password_rule = "required|string|min:6|confirmed";
        if($basic_settings->merchant_secure_password) {
            $password_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
        }
        $validator = Validator::make($request->all(), [
            'current_password'      => "required|string",
            'password'              => $password_rule,
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        if(!Hash::check($request->current_password,auth()->user()->password)) {
            $error = ['error'=>[__("Current password didn't match")]];
            return Helpers::error($error);
        }

        try{
            auth()->user()->update([
                'password'  => Hash::make($request->password),
            ]);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Password successfully updated!')]];
        return Helpers::onlysuccess($message);

    }
    public function deleteAccount(Request $request) {
        $user = auth()->user();
        //make unsubscribe
         try{
            (new PushNotificationHelper(['users' => [$user->id],'user_type' => 'merchant']))->unsubscribe();
        }catch(Exception $e) {
            // handle exception
        }
        //admin notification
        $this->deleteUserNotificationToAdmin($user,"MERCHANT",'merchant_api');
        $user->status = false;
        $user->email_verified = false;
        $user->kyc_verified = false;
        $user->deleted_at = now();
        $user->save();

        try{
            $user->token()->revoke();
            $message =  ['success'=>[__('Your profile deleted successfully!')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }

    public function notifications(){
        $notifications = MerchantNotification::auth()->latest()->get()->map(function($item){
            return[
                'id' => $item->id,
                'type' => $item->type,
                'title' => $item->message->title??"",
                'message' => $item->message->message??"",
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,

            ];
        });
        $data =[
            'notifications'  => $notifications
        ];
        $message =  ['success'=>[__('Merchant Notifications')]];
        return Helpers::success($data,$message);
    }
    public function getWallets(){
        $userWallets = user_wallets(authGuardApi(),'merchant_id');
        $data =[
            'userWallets'  => $userWallets
        ];
        $message =  ['success'=>[__('All Wallets')]];
        return Helpers::success($data,$message);
    }
    public function getRemainingBalance(){
        $validator = Validator::make(request()->all(), [
            'transaction_type'      => "required|string",
            'attribute'              => "required|string",
            'sender_amount'         => "sometimes|required",
            'currency_code'         => "required|string|exists:currencies,code",
            'charge_id'             => "required|integer",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $sender_wallet = MerchantWallet::auth()->active()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['currency_code'])->active();
        })->active()->first();
        if(!$sender_wallet){
            $error = ['error'=>[__('Merchant wallet not found')]];
            return Helpers::error($error);
        }

        if($validated['transaction_type'] == PaymentGatewayConst::TYPEMONEYOUT || $validated['transaction_type'] == PaymentGatewayConst::TYPEADDMONEY){
            $limits = PaymentGatewayCurrency::where('id',$validated['charge_id'])->first();
        }else{
            $limits = TransactionSetting::where('id',$validated['charge_id'])->first();
        }

        try{
            $result = (new TransactionLimit())->trxLimit('merchant_id',$sender_wallet->merchant->id,$validated['transaction_type'],$sender_wallet->currency,$validated['sender_amount'],$limits,$validated['attribute'],'json');

            $data = [
                'status'            =>  $result['status'],
                'transaction_type'  =>  $result['transaction_type'],
                'remainingDaily'    =>  $result['data']['remainingDailyTxnSelected'],
                'remainingMonthly'  =>  $result['data']['remainingMonthlyTxnSelected'],
                'currency'          =>  $sender_wallet->currency->code ?? get_default_currency_code(),
            ];

            if($data['status'] == true){
                $message =  ['success'=>[__($result['message']) ?? ""]];
                return Helpers::success($data,$message);
            }else{
                $error = ['error'=>[__($result['message']) ?? ""]];
            return Helpers::error($error,$data);
            }
        }catch(Exception $e){

            $errorData = json_decode($e->getMessage(), true);
            $error = ['error'=>[__($errorData['message'] ?? __("Something went wrong! Please try again."))]];
            return Helpers::error($error);
            return Helpers::error($error);
        }
    }
}
