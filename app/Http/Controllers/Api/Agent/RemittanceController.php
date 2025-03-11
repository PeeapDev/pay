<?php

namespace App\Http\Controllers\Api\Agent;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\TransactionLimit;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\AgentNotification;
use App\Models\AgentRecipient;
use App\Models\AgentWallet;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\Agent\Remittance\SenderEmail;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RemittanceController extends Controller
{
    protected  $trx_id;
    protected $basic_settings;
    public function __construct()
    {
        $this->trx_id = 'RT'.getTrxNum();
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function remittanceInfo(){
        $basic_settings = BasicSettings::first();
        $transactionType = [
            [
                'id'    => 1,
                'field_name' => Str::slug(GlobalConst::TRX_BANK_TRANSFER),
                'label_name' => "Bank Transfer",
            ],
            [
                'id'    => 2,
                'field_name' =>Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER),
                'label_name' => $basic_settings->site_name.' Wallet',
            ],

            [
                'id'    => 3,
                'field_name' => Str::slug(GlobalConst::TRX_CASH_PICKUP),
                'label_name' => "Cash Pickup",
            ]
        ];
        $transaction_type = (array) $transactionType;
        $remittanceCharge = TransactionSetting::where('slug','remittance')->where('status',1)->get()->map(function($data){
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
                'agent_fixed_commissions'   => get_amount($data->agent_fixed_commissions,null,get_wallet_precision()),
                'agent_percent_commissions' => get_amount($data->agent_percent_commissions,null,get_wallet_precision()),
                'agent_profit'              => $data->agent_profit,
            ];
        })->first();
        $fromCountries = Currency::sender()->active()->orderBy('id',"ASC")->get();
        $fromCountries->makeHidden(['updated_at','admin_id','editData','sender','both','receiver','senderCurrency','receiverCurrency']);

        $toCountries = Currency::receiver()->active()->orderBy('id',"DESC")->get();
        $toCountries->makeHidden(['updated_at','admin_id','editData','sender','both','receiver','senderCurrency','receiverCurrency']);
        $sender_recipients = AgentRecipient::auth()->sender()->orderByDesc("id")->get()->map(function($data){
            $basic_settings = BasicSettings::first();
            if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country??"",
                    'trx_type' => $data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => $basic_settings->site_name.' Wallet',
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];
            }else{
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country??"",
                    'trx_type' => @$data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];

            }

        });
        $receiver_recipients = AgentRecipient::auth()->receiver()->orderByDesc("id")->get()->map(function($data){
            $basic_settings = BasicSettings::first();
            if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country??"",
                    'trx_type' => $data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => $basic_settings->site_name.' Wallet',
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];
            }else{
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country??"",
                    'trx_type' => @$data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];

            }

        });
        $transactions = Transaction::agentAuth()->remitance()->latest()->take(5)->get()->map(function($item){
            $basic_settings = BasicSettings::first();
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
                if( @$item->details->remitance_type == "wallet-to-wallet-transfer"){
                    $transactionType = @$basic_settings->site_name." Wallet";

                }else{
                    $transactionType = ucwords(str_replace('-', ' ', @$item->details->remitance_type));
                }
                if(@$item->attribute == payment_gateway_const()::SEND){
                    if(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                        return[
                            'id' => @$item->id,
                            'type' =>@$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => @$item->type,
                            'transaction_heading' => "Send Remitance to @" .@$item->details->receiver_recipient->email,
                            'request_amount' => get_amount($item->request_amount,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'total_charge' => get_amount($item->charge->total_charge,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'exchange_rate' => get_amount(1, $item->details->charges->sender_cur_code??get_default_currency_code())." = ".get_amount($item->details->charges->exchange_rate??$item->details->to_country->rate,$item->details->to_country->code,$item->details->charges->r_precision_digit??2),
                            'payable' => get_amount($item->payable,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'sending_country' => @$item->details->form_country,
                            'receiving_country' => @$item->details->to_country->country,
                            'sender_recipient_name' => @$item->details->sender_recipient->firstname.' '.@$item->details->sender_recipient->lastname,
                            'receiver_recipient_name' => @$item->details->receiver_recipient->firstname.' '.@$item->details->receiver_recipient->lastname,
                            'remittance_type' => Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER) ,
                            'remittance_type_name' => $transactionType ,
                            'recipient_get' =>  get_amount(@$item->details->recipient_amount,@$item->details->to_country->code,$item->details->charges->r_precision_digit??2),
                            'current_balance' => get_amount($item->available_balance,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'status' => @$item->stringStatus->value ,
                            'date_time' => @$item->created_at ,
                            'status_info' =>(object)@$statusInfo ,
                            'rejection_reason' =>@$item->reject_reason??"" ,
                        ];
                    }elseif(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_BANK_TRANSFER)){
                        return[
                            'id' => @$item->id,
                            'type' =>@$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => @$item->type,
                            'transaction_heading' => "Send Remitance to @" . @$item->details->receiver_recipient->email,
                            'request_amount' => get_amount($item->request_amount,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'total_charge' => get_amount($item->charge->total_charge,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'exchange_rate' => get_amount(1, $item->details->charges->sender_cur_code??get_default_currency_code())." = ".get_amount($item->details->charges->exchange_rate??$item->details->to_country->rate,$item->details->to_country->code,$item->details->charges->r_precision_digit??2),
                            'payable' => get_amount($item->payable,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'sending_country' => @$item->details->form_country,
                            'receiving_country' => @$item->details->to_country->country,
                            'sender_recipient_name' => @$item->details->sender_recipient->firstname.' '.@$item->details->sender_recipient->lastname,
                            'receiver_recipient_name' => @$item->details->receiver_recipient->firstname.' '.@$item->details->receiver_recipient->lastname,
                            'remittance_type' => Str::slug(GlobalConst::TRX_BANK_TRANSFER) ,
                            'remittance_type_name' => $transactionType ,
                            'recipient_get' =>  get_amount(@$item->details->recipient_amount,@$item->details->to_country->code,$item->details->charges->r_precision_digit??2),
                            'bank_name' => ucwords(str_replace('-', ' ', @$item->details->receiver_recipient->alias)),
                            'current_balance' => get_amount($item->available_balance,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'status' => @$item->stringStatus->value ,
                            'date_time' => @$item->created_at ,
                            'status_info' =>(object)@$statusInfo ,
                            'rejection_reason' =>@$item->reject_reason??"",
                        ];
                    }elseif(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_CASH_PICKUP)){
                        return[
                            'id' => @$item->id,
                            'type' =>@$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => @$item->type,
                            'transaction_heading' => "Send Remitance to @" . @$item->details->receiver_recipient->email,
                            'request_amount' => get_amount($item->request_amount,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'total_charge' => get_amount($item->charge->total_charge,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'exchange_rate' => get_amount(1, $item->details->charges->sender_cur_code??get_default_currency_code())." = ".get_amount($item->details->charges->exchange_rate??$item->details->to_country->rate,$item->details->to_country->code,$item->details->charges->r_precision_digit??2),
                            'payable' => get_amount($item->payable,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'sending_country' => @$item->details->form_country,
                            'receiving_country' => @$item->details->to_country->country,
                            'sender_recipient_name' => @$item->details->sender_recipient->firstname.' '.@$item->details->sender_recipient->lastname,
                            'receiver_recipient_name' => @$item->details->receiver_recipient->firstname.' '.@$item->details->receiver_recipient->lastname,
                            'remittance_type' => Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER) ,
                            'remittance_type_name' => $transactionType ,
                            'recipient_get' =>  get_amount(@$item->details->recipient_amount,@$item->details->to_country->code,$item->details->charges->r_precision_digit??2),
                            'pickup_point' => ucwords(str_replace('-', ' ', @$item->details->receiver_recipient->alias)),
                            'current_balance' => get_amount($item->available_balance,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                            'status' => @$item->stringStatus->value ,
                            'date_time' => @$item->created_at ,
                            'status_info' =>(object)@$statusInfo ,
                            'rejection_reason' =>@$item->reject_reason??"" ,
                        ];
                    }
                }elseif($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'transaction_heading' => __("Received Remittance from")." @" ." (".@$item->details->sender->email.")",
                        'request_amount' => get_amount($item->payable,$item->details->charges->receiver_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'sending_country' => @$item->details->form_country,
                        'receiving_country' => @$item->details->to_country->country,
                        'sender_recipient_name' => @$item->details->sender_recipient->firstname.' '.@$item->details->sender_recipient->lastname,
                        'receiver_recipient_name' => @$item->details->receiver_recipient->firstname.' '.@$item->details->receiver_recipient->lastname,
                        'remittance_type' => Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER) ,
                        'remittance_type_name' => $transactionType ,
                        'recipient_get' =>  get_amount(@$item->details->recipient_amount,@$item->details->to_country->code,$item->details->charges->r_precision_digit??2),
                        'current_balance' => get_amount($item->available_balance,$item->details->charges->receiver_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                        'rejection_reason' =>$item->reject_reason??"" ,
                    ];

                }

        });
        $get_remaining_fields = [
            'transaction_type'  =>  PaymentGatewayConst::SENDREMITTANCE,
            'attribute'         =>  PaymentGatewayConst::SEND,
        ];
        $data =[
            'fromCountryFlugPath'       => 'backend/images/currency-flag',
            'toCountryFlugPath'         => 'public/backend/images/country-flag',
            'default_image'             => "public/backend/images/default/default.webp",
            'transactionTypes'          => $transaction_type,
            'get_remaining_fields'      => (object) $get_remaining_fields,
            'remittanceCharge'          => (object)$remittanceCharge,
            'fromCountry'               => $fromCountries,
            'toCountries'               => $toCountries,
            'sender_recipients'         => $sender_recipients,
            'receiver_recipients'       => $receiver_recipients,
            'transactions'              => $transactions,
        ];

        $message =  ['success'=>[__('Remittance Information')]];
        return Helpers::success($data,$message);
    }
    public function getRecipientSender(Request $request){
        $validator = Validator::make(request()->all(), [
            'transaction_type'     => "nullable|string",
            'from_country'     => "nullable|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $transaction_type = $request->transaction_type;
        $sender_country = $request->from_country;
            $recipient = AgentRecipient::auth()->sender()->where('type',$transaction_type)->where('country',$sender_country)->get()->map(function($data){
                $basic_settings = BasicSettings::first();
                if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country??"",
                        'trx_type' => $data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => $basic_settings->site_name.' Wallet',
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];
                }else{
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country??"",
                        'trx_type' => @$data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];

                }
            });

        $data =[
            'sender_recipient' => $recipient,
        ];
        $message =  ['success'=>[__('Successfully got sender recipient')]];
        return Helpers::success($data,$message);
    }
    public function getRecipientReceiver(Request $request){
        $validator = Validator::make(request()->all(), [
            'to_country'     => "required",
            'transaction_type'     => "nullable|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $receiver_country = $request->to_country;
        $transaction_type = $request->transaction_type;
        if( $transaction_type != null || $transaction_type != ''){
            $recipient = AgentRecipient::auth()->receiver()->where('country', $receiver_country)->where('type',$transaction_type)->get()->map(function($data){
                $basic_settings = BasicSettings::first();
                if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country??"",
                        'trx_type' => $data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => $basic_settings->site_name.' Wallet',
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];
                }else{
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country??"",
                        'trx_type' => @$data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];

                }
            });

        }else{
            $recipient = AgentRecipient::auth()->receiver()->where('country', $receiver_country)->get()->map(function($data){
                $basic_settings = BasicSettings::first();
                if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country??"",
                        'trx_type' => $data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => $basic_settings->site_name.' Wallet',
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];
                }else{
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country??"",
                        'trx_type' => @$data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];

                }
            });
        }
        $data =[
            'sender_recipient' => $recipient,
        ];
        $message =  ['success'=>[__('Successfully got receiver recipient')]];
        return Helpers::success($data,$message);
    }
    //confirmed remittance
    public function confirmed(Request $request){
        $validator = Validator::make(request()->all(), [
            'form_country'               =>'required',
            'to_country'                 =>'required',
            'transaction_type'           =>'required|string',
            'sender_recipient'           =>'required',
            'receiver_recipient'         =>'required',
            'send_amount'                =>"required|numeric",
            'receive_amount'                =>"required|numeric",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated =  $validator->validate();
        $exchangeCharge = TransactionSetting::where('slug','remittance')->where('status',1)->first();
        $user = authGuardApi()['user'];
        $transaction_type = $validated['transaction_type'];
        $basic_setting = BasicSettings::first();

        $sender_currency = Currency::where('id',$request->form_country)->active()->first();
        if(!$sender_currency){
            $error = ['error'=>[__('Sender Country Not Found')]];
            return Helpers::error($error);
        }

        $userWallet = AgentWallet::where('agent_id',$user->id)->whereHas("currency",function($q) use ($sender_currency) {
            $q->where("code",$sender_currency->code)->active();
        })->active()->first();
        if(!$userWallet){
            $error = ['error'=>[__("Agent doesn't exists.")]];
            return Helpers::error($error);
        }

        $to_country = Currency::where('id',$request->to_country)->active()->first();
        if(!$to_country){
            $error = ['error'=>[__('Receiver country not found')]];
            return Helpers::error($error);
        }
        if($sender_currency->code == $to_country->code){
            $error = ['error'=>[__('Remittances cannot be sent within the same country')]];
            return Helpers::error($error);
        }
        $recipient = AgentRecipient::auth()->sender()->where("id",$request->sender_recipient)->first();
        if(!$recipient){
            $error = ['error'=>[__('Recipient is invalid/mismatch transaction type or country')]];
            return Helpers::error($error);
        }
        $receiver_recipient = AgentRecipient::auth()->receiver()->where("id",$request->receiver_recipient)->first();
        if(!$receiver_recipient){
            $error = ['error'=>[__('Receiver Recipient is invalid')]];
            return Helpers::error($error);
        }

        $charges = $this->chargeCalculate($userWallet->currency,$receiver_recipient->receiver_country, $validated['send_amount'],$exchangeCharge);

        $sender_currency_rate = $userWallet->currency->rate;
        $min_amount = $exchangeCharge->min_limit * $sender_currency_rate;
        $max_amount = $exchangeCharge->max_limit * $sender_currency_rate;

        if($charges->sender_amount < $min_amount || $charges->sender_amount > $max_amount) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }
        //daily and monthly
        try{
            (new TransactionLimit())->trxLimit('agent_id',$userWallet->user->id,PaymentGatewayConst::SENDREMITTANCE,$userWallet->currency,$validated['send_amount'],$exchangeCharge,PaymentGatewayConst::SEND);
        }catch(Exception $e){
            $errorData = json_decode($e->getMessage(), true);
            $error = ['error'=>[__($errorData['message'] ?? __("Something went wrong! Please try again."))]];
            return Helpers::error($error);;
        }
        if($charges->payable > $userWallet->balance) {
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
         }
         $trx_id = $this->trx_id;
         $notifyData = [
            'trx_id'                    => $trx_id??'ndf',
            'title'                     => __("Send Remittance to")." @" . $receiver_recipient->fullname." (".@$receiver_recipient->email.")",
            'request_amount'            => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
            'exchange_rate'             => get_amount(1,$charges->sender_cur_code).' = '.get_amount($charges->exchange_rate,$charges->receiver_cur_code,$charges->r_precision_digit),
            'charges'                   => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
            'payable'                   => get_amount($charges->payable,$charges->sender_cur_code,$charges->precision_digit),
            'sending_country'           => @$sender_currency->country,
            'receiving_country'         => @$to_country->country,
            'sender_recipient_name'     => @$recipient->fullname,
            'receiver_recipient_name'   => @$receiver_recipient->fullname,
            'alias'                     => ucwords(str_replace('-', ' ', @$recipient->alias)),
            'transaction_type'          => @$transaction_type,
            'receiver_get'              => get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),
            'status'                    => __("Pending"),
          ];
        try{
            if($transaction_type === Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $receiver_user =  json_decode($receiver_recipient->details);
                $receiver_user_info =  $receiver_user;
                $receiver_user =  $receiver_user->id;
                $receiver_wallet = UserWallet::where('user_id',$receiver_user)->whereHas("currency",function($q) use ($to_country) {
                    $q->where("code",$to_country->code)->active();
                })->active()->first();
                if(!$receiver_wallet){
                    $error = ['error'=>[__('Sorry, Receiver wallet not found')]];
                    return Helpers::error($error);
                }

                $sender = $this->insertSender( $trx_id,$userWallet,$recipient,$sender_currency,$to_country,$transaction_type, $receiver_recipient,$charges);
                if($sender){
                     $this->insertSenderCharges( $sender,$charges,$user,$receiver_recipient);
                     try{
                        if( $basic_setting->agent_email_notification == true){
                            $user->notify(new SenderEmail($user,(object)$notifyData));
                        }
                     }catch(Exception $e){}
                     try{
                        if( $basic_setting->agent_sms_notification == true){
                            sendSms($user,'SEND_REMITTANCE_AGENT',[
                                'form_country'          => @$sender_currency->country,
                                'to_country'            => $to_country->country,
                                'transaction_type'      => ucwords(str_replace('-', ' ', @$transaction_type)),
                                'sender_recipient'      => @$recipient->fullname,
                                'receiver_recipient'    => $receiver_recipient->fullname,
                                'send_amount'           => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
                                'recipient_amount'      => get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),
                                'trx'                   => $trx_id,
                                'time'                  => now()->format('Y-m-d h:i:s A'),
                                'balance'               => get_amount($userWallet->balance,$userWallet->currency->code,$charges->precision_digit),
                            ]);
                        }
                     }catch(Exception $e){}
                }
                $receiverTrans = $this->insertReceiver($trx_id,$userWallet,$recipient,$sender_currency,$to_country,$transaction_type,$receiver_user,$receiver_wallet,$receiver_recipient,$charges);
                if($receiverTrans){
                     $this->insertReceiverCharges( $receiverTrans,$charges,$user,$recipient,$receiver_recipient,$receiver_user_info);
                }

            }else{
                $trx_id = $this->trx_id;
                $sender = $this->insertSender($trx_id,$userWallet,$recipient,$sender_currency,$to_country,$transaction_type, $receiver_recipient,$charges);
                if($sender){
                     $this->insertSenderCharges($sender,$charges,$user,$receiver_recipient);
                     try{
                         if( $basic_setting->agent_email_notification == true){
                             $user->notify(new SenderEmail($user,(object)$notifyData));
                         }
                     }catch(Exception $e){}
                     try{
                        if( $basic_setting->agent_sms_notification == true){
                            sendSms($user,'SEND_REMITTANCE_AGENT',[
                                'form_country'          => @$sender_currency->country,
                                'to_country'            => $to_country->country,
                                'transaction_type'      => ucwords(str_replace('-', ' ', @$transaction_type)),
                                'sender_recipient'      => @$recipient->fullname,
                                'receiver_recipient'    => $receiver_recipient->fullname,
                                'send_amount'           => get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit),
                                'recipient_amount'      => get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),
                                'trx'                   => $trx_id,
                                'time'                  => now()->format('Y-m-d h:i:s A'),
                                'balance'               => get_amount($userWallet->balance,$userWallet->currency->code,$charges->precision_digit),
                            ]);
                        }
                     }catch(Exception $e){}

                }
            }
            if($transaction_type != Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $this->adminNotification($trx_id,$charges,$user,$recipient,$receiver_recipient,$to_country,$sender_currency,$transaction_type);
            }
            $message =  ['success'=>[__('Remittance Money send successfully')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {

            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }

    }
    //sender transaction
    public function insertSender($trx_id,$userWallet,$recipient,$form_country,$to_country,$transaction_type, $receiver_recipient,$charges) {
        $trx_id = $trx_id;
        $authWallet = $userWallet;

        if($transaction_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
            $status = 1;
            $afterCharge = ($authWallet->balance - $charges->payable) + $charges->agent_total_commission;

        }else{
            $status = 2;
            $afterCharge = ($authWallet->balance - $charges->payable);
        }

        $details =[
           'recipient_amount' => $charges->will_get,
            'sender_recipient' => $recipient,
            'receiver_recipient' => $receiver_recipient,
            'form_country' => $form_country->name,
            'to_country' => $to_country,
            'remitance_type' => $transaction_type,
            'sender' => $userWallet->agent,
            'bank_account' => $recipient->account_number??'',
            'charges' => $charges,
    ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $userWallet->agent->id,
                'agent_wallet_id'               => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges->sender_amount,
                'payable'                       => $charges->payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::SENDREMITTANCE," ")) . " To " .$receiver_recipient->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => $status,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);
            if($transaction_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $this->agentProfitInsert($id,$authWallet,$charges);
            }

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($id,$charges,$user,$receiver_recipient) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges->percent_charge,
                'fixed_charge'      =>$charges->fixed_charge,
                'total_charge'      =>$charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();


            //notification
            $notification_content = [
                'title'         =>__("Send Remittance"),
                'message'       => "Send Remittance Request to ".$receiver_recipient->fullname.' ' .$charges->sender_amount.' '.$charges->sender_cur_code." successful",
                'image'         =>  get_image($user->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::SEND_REMITTANCE,
                'agent_id'  => $user->id,
                'message'   => $notification_content,
            ]);
             //Push Notifications
            if( $this->basic_settings->agent_push_notification == true){
                try{
                    (new PushNotificationHelper())->prepareApi([$user->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'agent',
                    ])->send();
                }catch(Exception $e) {}
            }
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$userWallet,$recipient,$form_country,$to_country,$transaction_type,$receiver_user,$receiver_wallet,$receiver_recipient,$charges) {
        $trx_id = $trx_id;
        $receiverWallet = $receiver_wallet;
        $recipient_amount = ($receiverWallet->balance + $charges->will_get);
        $details =[
            'recipient_amount' => $charges->will_get,
            'receiver' => $receiver_recipient,
            'form_country' => $form_country->name,
            'to_country' => $to_country,
            'remitance_type' => $transaction_type,
            'sender' => $userWallet->agent,
            'bank_account'      => $receiver_recipient->account_number??'',
            'charges' => $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $receiver_user,
                'user_wallet_id'                => $receiverWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges->sender_amount,
                'payable'                       => $charges->payable,
                'available_balance'             => $recipient_amount,
                'remark'                        =>  ucwords(remove_speacial_char(PaymentGatewayConst::RECEIVEREMITTANCE," ")) . " From " . $userWallet->agent->username,
                'details'                       => json_encode($details),
                'attribute'                      => PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateReceiverWalletBalance($receiverWallet,$recipient_amount) {

        $receiverWallet->update([
            'balance'   => $recipient_amount,
        ]);
    }
    public function insertReceiverCharges( $id,$charges,$user,$receipient,$receiver_recipient,$receiver_user_info) {

        DB::beginTransaction();

        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges->percent_charge,
                'fixed_charge'      =>$charges->fixed_charge,
                'total_charge'      =>$charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Send Remittance"),
                'message'       => "Send Remittance from ".$user->fullname.' ' .$charges->will_get.' '.$charges->receiver_cur_code." successful",
                'image'         =>  get_image($receiver_user_info->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::SEND_REMITTANCE,
                'user_id'  => $receiver_user_info->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            if( $this->basic_settings->push_notification == true){
                try{
                    (new PushNotificationHelper())->prepareApi([$receiver_user_info->id],[
                        'title' => $notification_content['title'],
                        'desc'  => $notification_content['message'],
                        'user_type' => 'user',
                    ])->send();
                }catch(Exception $e) {}
            }
        }catch(Exception $e) {

            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
    }
    //end transaction helpers

    public function agentProfitInsert($id,$authWallet,$charges) {
        DB::beginTransaction();
        try{
            DB::table('agent_profits')->insert([
                'agent_id'          => $authWallet->agent->id,
                'transaction_id'    => $id,
                'percent_charge'    => $charges->agent_percent_commission,
                'fixed_charge'      => $charges->agent_fixed_commission,
                'total_charge'      => $charges->agent_total_commission,
                'created_at'        => now(),
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
    }
    public function chargeCalculate($sender_currency,$receiver_currency,$amount,$charges) {
        $sPrecision = get_wallet_precision($sender_currency);
        $rPrecision = get_wallet_precision($receiver_currency);
        $exchange_rate = $receiver_currency->rate / $sender_currency->rate;

        $data['exchange_rate']              = $exchange_rate;
        $data['sender_amount']              = $amount;
        $data['sender_cur_code']            = $sender_currency->code;
        $data['sender_cur_rate']            = $sender_currency->rate;

        $data['receiver_cur_code']          = $receiver_currency->code;
        $data['receiver_cur_rate']          = $receiver_currency->rate;

        $data['percent_charge']             = ($amount / 100) * $charges->percent_charge;
        $data['fixed_charge']               = $sender_currency->rate * $charges->fixed_charge;
        $data['total_charge']               = $data['percent_charge'] + $data['fixed_charge'];

        $data['conversion_amount']          = $amount * $exchange_rate;
        $data['payable']                    = $amount + $data['total_charge'];
        $data['will_get']                   = $amount * $exchange_rate;

        $data['agent_percent_commission']   = ($amount / 100) * $charges->agent_percent_commissions ?? 0;
        $data['agent_fixed_commission']     = $sender_currency->rate * $charges->agent_fixed_commissions ?? 0;
        $data['agent_total_commission']     = $data['agent_percent_commission'] + $data['agent_fixed_commission'];
        $data['default_currency']           = get_default_currency_code();
        $data['precision_digit']            = $sPrecision;
        $data['r_precision_digit']      = $rPrecision;


        return (object) $data;
    }
    //admin notification
    public function adminNotification($trx_id,$charges,$user,$recipient,$receiver_recipient,$to_country,$form_country,$transaction_type){
        $exchange_rate = get_amount(1,$charges->sender_cur_code).' = '.get_amount($charges->exchange_rate,$charges->receiver_cur_code,$charges->precision_digit);
        if($transaction_type == 'bank-transfer'){
            $input_field = "bank Name";
        }else{
            $input_field = "Pickup Point";
        }
        $notification_content = [
            //email notification
            'subject' =>__("Send Remittance to")." @" . $receiver_recipient->firstname.' '.@$receiver_recipient->lastname." (".@$receiver_recipient->email.")",
            'greeting' =>__("Send Remittance Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$recipient->email."<br>".__("Receiver").": @".$receiver_recipient->email."<br>".__("Sender Amount")." : ".get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit)."<br>".__("Exchange Rate")." : ".$exchange_rate."<br>".__("Fees & Charges")." : ".get_amount($charges->total_charge,$charges->sender_cur_code,$charges->precision_digit)."<br>".__("Total Payable Amount")." : ".get_amount($charges->payable,$charges->sender_cur_code,$charges->precision_digit)."<br>".__("Recipient Received")." : ".get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit)."<br>".__("Transaction Type")." : ".ucwords(str_replace('-', ' ', @$transaction_type))."<br>".__("sending Country")." : ".$form_country."<br>".__("Receiving Country")." : ".$to_country->country."<br>".__($input_field)." : ".ucwords(str_replace('-', ' ', @$recipient->alias)),

            //push notification
            'push_title' => __("Send Remittance to")." @". $receiver_recipient->firstname.' '.@$receiver_recipient->lastname." (".@$receiver_recipient->email.")"." ".__('Successful'),
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$recipient->email." ".__("Receiver").": @".$receiver_recipient->email." ".__("Sender Amount")." : ".get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit)." ".__("Receiver Amount")." : ".get_amount($charges->will_get,$charges->receiver_cur_code,$charges->r_precision_digit),

            //admin db notification
            'notification_type' =>  NotificationConst::SEND_REMITTANCE,
            'admin_db_title' => "Send Remittance"." ".get_amount($charges->sender_amount,$charges->sender_cur_code)." (".$trx_id.")",
            'admin_db_message' =>"Sender".": @".$recipient->email.","."Receiver".": @".$receiver_recipient->email.","."Sender Amount"." : ".get_amount($charges->sender_amount,$charges->sender_cur_code,$charges->precision_digit).","."Receiver Amount"." : ".get_amount($charges->will_get,$to_country->code,$charges->r_precision_digit)
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.remitance.index','admin.remitance.pending','admin.remitance.complete','admin.remitance.canceled','admin.remitance.details','admin.remitance.approved','admin.remitance.rejected','admin.remitance.export.data'])
                                    ->mail(ActivityNotification::class, [
                                            'subject'   => $notification_content['subject'],
                                            'greeting'  => $notification_content['greeting'],
                                            'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                            'user_type' => "admin",
                                            'title' => $notification_content['push_title'],
                                            'desc'  => $notification_content['push_content'],
                                            'from'  => 'api',
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
