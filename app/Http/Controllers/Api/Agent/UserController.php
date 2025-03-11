<?php

namespace App\Http\Controllers\Api\Agent;

use App\Constants\GlobalConst;
use App\Constants\PaymentGatewayConst;
use Exception;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Helpers\Api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Models\Admin\TransactionSetting;
use App\Models\AgentNotification;
use App\Models\AgentProfit;
use App\Models\AgentWallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Providers\Admin\BasicSettingsProvider;
use App\Traits\AdminNotifications\AuthNotifications;


class UserController extends Controller
{
    use AuthNotifications;

    public function home(){
        $agent = authGuardApi()['user'];
        $totalAddMoney = amountOnBaseCurrency(Transaction::agentAuth()->addMoney()->where('status',1)->get());
        $totalWithdrawMoney = amountOnBaseCurrency(Transaction::agentAuth()->moneyOut()->where('status',1)->get());
        $totalSendMoney = amountOnBaseCurrency(Transaction::agentAuth()->senMoney()->where('status',1)->get());
        $totalMoneyIn = amountOnBaseCurrency(Transaction::agentAuth()->moneyIn()->where('status',1)->get());
        $totalReceiveMoney = amountOnBaseCurrency(Transaction::agentAuth()->agentMoneyOut()->where('status',1)->get());
        $totalSendRemittance =amountOnBaseCurrency(Transaction::agentAuth()->remitance()->where('attribute',"SEND")->get());
        $billPay =  amountOnBaseCurrency(Transaction::agentAuth()->billPay()->where('status',1)->get());
        $topUps =   amountOnBaseCurrency(Transaction::agentAuth()->where('status',1)->mobileTopup()->get());
        $total_transaction = Transaction::agentAuth()->where('status', 1)->count();
        $agent_profits = agentOnBaseCurrency(AgentProfit::agentAuth()->get());

        $transactions = Transaction::agentAuth()->latest()->take(10)->get()->map(function($item){
            if($item->type == payment_gateway_const()::TYPEADDMONEY){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_amount($item->request_amount,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'payable' => isCrypto($item->payable,$item->currency->currency_code??get_default_currency_code(),$item->currency->gateway->crypto),
                    'status' => $item->stringStatus->value ,
                    'remark' => $item->remark??"",
                    'date_time' => $item->created_at,
                ];
            }elseif($item->type == payment_gateway_const()::BILLPAY){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_amount($item->request_amount,billPayCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)) ,
                    'payable' => get_amount($item->payable,billPayCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                    'status' => $item->stringStatus->value ,
                    'remark' => $item->remark??"",
                    'date_time' => $item->created_at,

                ];

            }elseif($item->type == payment_gateway_const()::MOBILETOPUP){
                return[
                   'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_amount($item->request_amount,topUpCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                    'payable' => get_amount($item->payable,topUpCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)),
                    'status' => $item->stringStatus->value ,
                    'remark' => $item->remark??"",
                    'date_time' => $item->created_at ,

                ];

            }elseif($item->type == payment_gateway_const()::TYPEMONEYOUT){
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

            }elseif($item->type == payment_gateway_const()::SENDREMITTANCE){
                if($item->attribute == payment_gateway_const()::SEND){
                    if(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                        return[
                            'id' => @$item->id,
                            'type' =>$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => $item->type,
                            'request_amount' => get_amount(@$item->request_amount,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'payable' => get_amount(@$item->payable,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'status' => @$item->stringStatus->value ,
                            'remark' => $item->remark??"",
                            'date_time' => @$item->created_at ,
                        ];
                    }elseif(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_BANK_TRANSFER)){
                        return[
                            'id' => @$item->id,
                            'type' =>$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => $item->type,
                            'request_amount' => get_amount(@$item->request_amount,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'payable' => get_amount(@$item->payable,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'status' => @$item->stringStatus->value ,
                            'remark' => $item->remark??"",
                            'date_time' => @$item->created_at ,
                        ];
                    }elseif(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_CASH_PICKUP)){
                        return[
                            'id' => @$item->id,
                            'type' =>$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => $item->type,
                            'request_amount' => get_amount(@$item->request_amount,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'payable' => get_amount(@$item->payable,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'status' => @$item->stringStatus->value ,
                            'remark' => $item->remark??"",
                            'date_time' => @$item->created_at ,
                        ];
                    }

                }elseif($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'request_amount' => get_amount(@$item->request_amount,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'payable' => get_amount(@$item->payable,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'status' => @$item->stringStatus->value ,
                        'remark' => $item->remark??"",
                        'date_time' => @$item->created_at ,

                    ];

                }

            }elseif($item->type == payment_gateway_const()::TYPETRANSFERMONEY){
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
                        'request_amount' => get_amount($item->details->charges->sender_amount??$item->request_amount,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'payable' => get_amount($item->details->charges->payable??$item->payable,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'status' => @$item->stringStatus->value ,
                        'remark' => $item->remark??"",
                        'date_time' => @$item->created_at ,
                    ];

                }

            }elseif($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' =>  get_transaction_numeric_attribute($item->attribute).get_amount($item->request_amount,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'payable' => get_amount(@$item->payable,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)),
                    'remark' => $item->remark??"",
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at ,

                ];

            }elseif($item->type == payment_gateway_const()::AGENTMONEYOUT){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'request_amount' =>get_amount($item->details->charges->receiver_amount,$item->details->charges->receiver_currency,get_wallet_precision($item->creator_wallet->currency)) ,
                        'payable' => get_amount($item->details->charges->receiver_amount,$item->details->charges->receiver_currency,get_wallet_precision($item->creator_wallet->currency)),
                        'remark' => $item->remark??"",
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                    ];
            }elseif($item->type == payment_gateway_const()::MONEYIN){
                return[
                    'id' => @$item->id,
                    'type' =>$item->attribute,
                    'trx' => @$item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_amount($item->request_amount,$item->details->charges->receiver_currency,get_wallet_precision($item->creator_wallet->currency)),
                    'payable' =>  get_amount($item->payable,$item->details->charges->receiver_currency,get_wallet_precision($item->creator_wallet->currency)),
                    'remark' => $item->remark??"",
                    'status' => @$item->stringStatus->value ,
                    'date_time' => @$item->created_at ,
                ];
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
            'receive_money' => module_access_api('agent-receive-money'),
            'add_money' => module_access_api('agent-add-money'),
            'withdraw_money' => module_access_api('agent-withdraw-money'),
            'transfer_money' => module_access_api('agent-transfer-money'),
            'money_in' => module_access_api('agent-money-in'),
            'bill_pay' => module_access_api('agent-bill-pay'),
            'mobile_top_up' => module_access_api('agent-mobile-top-up'),
            'remittance_money' => module_access_api('agent-remittance-money'),
            'exchange_rate' => module_access_api('agent-money-exchange'),
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
            'base_url'                  => url("/"),
            'default_image'             => files_asset_path_basename("default"),
            "image_path"                => files_asset_path_basename('agent-profile'),
            'module_access'             => $module_access,
            'userWallets'                => user_wallets(authGuardApi(),'agent_id',8),
            'agent'                     => $agent,
            'totalAddMoney'             => get_amount($totalAddMoney,get_default_currency_code(),get_wallet_precision()),
            'totalWithdrawMoney'        => get_amount($totalWithdrawMoney,get_default_currency_code(),get_wallet_precision()),
            'totalSendMoney'            => get_amount($totalSendMoney,get_default_currency_code(),get_wallet_precision()),
            'totalMoneyIn'              => get_amount($totalMoneyIn,get_default_currency_code(),get_wallet_precision()),
            'totalReceiveMoney'         => get_amount($totalReceiveMoney,get_default_currency_code(),get_wallet_precision()),
            'totalSendRemittance'       => get_amount($totalSendRemittance,get_default_currency_code(),get_wallet_precision()),
            'billPay'                   => get_amount($billPay,get_default_currency_code(),get_wallet_precision()),
            'topUps'                    => get_amount($topUps,get_default_currency_code(),get_wallet_precision()),
            'total_transaction'         => $total_transaction,
            'agent_profits'             => get_amount($agent_profits,get_default_currency_code(),get_wallet_precision()),
            'transactions'              => $transactions,
        ];
        $message =  ['success'=>[__('Agent Dashboard')]];
        return Helpers::success($data,$message);
    }
    public function profile(){
        $user = authGuardApi()['user'];
        $data =[
            'base_url'          => url("/"),
            'default_image'     => files_asset_path_basename("default"),
            "image_path"        => files_asset_path_basename('agent-profile'),
            'agent'             => $user,
        ];
        $message =  ['success'=>[__('Agent Profile')]];
        return Helpers::success($data,$message);
    }
    public function profileUpdate(Request $request){
        $user =authGuardApi()['user'];
        $validator = Validator::make($request->all(), [
            'firstname'     => "required|string|max:60",
            'lastname'      => "required|string|max:60",
            'store_name'    => "required|string|max:60",
            'email'         =>  $user->registered_by == GlobalConst::EMAIL ? "nullable": "required|email|max:100",
            'country'       =>  $user->registered_by == GlobalConst::PHONE ? "nullable": "required|string|max:50",
            'phone_code'    =>  $user->registered_by == GlobalConst::PHONE ? "nullable": "required|string|max:20",
            'phone'         =>  $user->registered_by == GlobalConst::PHONE ? "nullable": "required|string|max:20|unique:agents,mobile,".$user->id,
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
        $validated['store_name']    = $data['store_name'];
        $validated['mobile']        = $user->registered_by == GlobalConst::PHONE ? $user->mobile : remove_special_char($data['phone']);
        $validated['mobile_code']   = $user->registered_by == GlobalConst::PHONE ? $user->mobile_code : remove_special_char($data['phone_code']);
        $complete_phone             = $mobileCode.$mobile;
        $validated['full_mobile']   = $complete_phone;
        $validated['email']         = $user->registered_by == GlobalConst::EMAIL ? $user->email : $data['email'];

        $validated['address']       = [
            'country'   => $user->registered_by == GlobalConst::PHONE ? $user->address->country ??"" : $data['country'],
            'state'     => $data['state'] ?? "",
            'city'      => $data['city'] ?? "",
            'zip'       => $data['zip_code'] ?? "",
            'address'   => $data['address'] ?? "",
        ];
        if($request->hasFile("image")) {
            $oldImage = $user->image;
            $image = upload_file($data['image'],'agent-profile', $oldImage);
            $upload_image = upload_files_from_path_dynamic([$image['dev_path']],'agent-profile');
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
        if($basic_settings->agent_secure_password) {
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
        $user = authGuardApi()['user'];
         //make unsubscribe
         try{
            (new PushNotificationHelper(['users' => [$user->id],'user_type' => 'agent']))->unsubscribe();
        }catch(Exception $e) {
            // handle exception
        }
        //admin notification
        $this->deleteUserNotificationToAdmin($user,"AGENT",'agent_api');
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
        $notifications = AgentNotification::auth()->latest()->get()->map(function($item){
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
        $message =  ['success'=>[__('Agent Notifications')]];
        return Helpers::success($data,$message);
    }
    public function getWallets(){
        $userWallets = user_wallets(authGuardApi(),'agent_id');
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
        $sender_wallet = AgentWallet::auth()->active()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['currency_code'])->active();
        })->active()->first();
        if(!$sender_wallet){
            $error = ['error'=>[__('User wallet not found')]];
            return Helpers::error($error);
        }

        if($validated['transaction_type'] == PaymentGatewayConst::TYPEMONEYOUT || $validated['transaction_type'] == PaymentGatewayConst::TYPEADDMONEY){
            $limits = PaymentGatewayCurrency::where('id',$validated['charge_id'])->first();
        }else{
            $limits = TransactionSetting::where('id',$validated['charge_id'])->first();
        }

        try{
            $result = (new TransactionLimit())->trxLimit('agent_id',$sender_wallet->agent->id,$validated['transaction_type'],$sender_wallet->currency,$validated['sender_amount'],$limits,$validated['attribute'],'json');
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
