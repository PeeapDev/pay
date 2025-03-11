<?php

namespace App\Http\Resources\User;

use App\Constants\GlobalConst;
use App\Models\Admin\BasicSettings;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class RemittanceLogs extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $basic_settings = BasicSettings::first();
        $statusInfo = [
            "success" =>      1,
            "pending" =>      2,
            "rejected" =>     3,
            ];
            if( @$this->details->remitance_type == "wallet-to-wallet-transfer"){
                $transactionType = @$basic_settings->site_name." Wallet";

            }else{
                $transactionType = ucwords(str_replace('-', ' ', @$this->details->remitance_type));
            }
            if($this->attribute == payment_gateway_const()::SEND){
                if(@$this->details->remitance_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                    return[
                        'id' => @$this->id,
                        'type' =>$this->attribute,
                        'trx' => @$this->trx_id,
                        'transaction_type' => $this->type,
                        'transaction_heading' => __("Send Remittance to @")." " . " (".@$this->details->receiver->email.")",
                        'request_amount' => get_amount($this->request_amount,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                        'total_charge' => get_amount($this->charge->total_charge,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                       'exchange_rate' => get_amount(1, $this->details->charges->sender_cur_code??get_default_currency_code())." = ".get_amount($this->details->charges->exchange_rate??$this->details->to_country->rate,$this->details->to_country->code,$this->details->charges->r_precision_digit??2),
                        'payable' => get_amount($this->payable,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                        'sending_country' => @$this->details->form_country,
                        'receiving_country' => @$this->details->to_country->country,
                        'receipient_name' => @$this->details->receiver->firstname.' '.@$this->details->receiver->lastname,
                        'remittance_type' => Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER) ,
                        'remittance_type_name' => $transactionType ,
                        'recipient_get' =>  get_amount(@$this->details->recipient_amount,@$this->details->to_country->code,$this->details->charges->r_precision_digit??2),
                        'current_balance' => get_amount($this->available_balance,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                        'status' => @$this->stringStatus->value ,
                        'date_time' => @$this->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                        'rejection_reason' =>$this->reject_reason??"" ,
                        'account_number' => @$this->details->bank_account??""

                    ];
                }elseif(@$this->details->remitance_type == Str::slug(GlobalConst::TRX_BANK_TRANSFER)){
                    return[
                        'id' => @$this->id,
                        'type' =>$this->attribute,
                        'trx' => @$this->trx_id,
                        'transaction_type' => $this->type,
                        'transaction_heading' => __("Send Remittance to @")." " .  " (".@$this->details->receiver->email.")",
                        'request_amount' => get_amount($this->request_amount,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency),get_wallet_precision($this->creator_wallet->currency)),
                        'total_charge' => get_amount($this->charge->total_charge,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency),get_wallet_precision($this->creator_wallet->currency)),
                       'exchange_rate' => get_amount(1, $this->details->charges->sender_cur_code??get_default_currency_code())." = ".get_amount($this->details->charges->exchange_rate??$this->details->to_country->rate,$this->details->to_country->code,$this->details->charges->r_precision_digit??2),
                        'payable' => get_amount($this->payable,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency),get_wallet_precision($this->creator_wallet->currency)),
                        'sending_country' => @$this->details->form_country,
                        'receiving_country' => @$this->details->to_country->country,
                        'receipient_name' => @$this->details->receiver->firstname.' '.@$this->details->receiver->lastname,
                        'remittance_type' => Str::slug(GlobalConst::TRX_BANK_TRANSFER) ,
                        'remittance_type_name' => $transactionType ,
                        'recipient_get' =>  get_amount(@$this->details->recipient_amount,@$this->details->to_country->code,$this->details->charges->r_precision_digit??2),
                        'bank_name' => ucwords(str_replace('-', ' ', @$this->details->receiver->alias)),
                        'account_number' => @$this->details->bank_account??"",
                        'current_balance' => get_amount($this->available_balance,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency),get_wallet_precision($this->creator_wallet->currency)),
                        'status' => @$this->stringStatus->value ,
                        'date_time' => @$this->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                        'rejection_reason' =>$this->reject_reason??"",
                    ];
                }elseif(@$this->details->remitance_type == Str::slug(GlobalConst::TRX_CASH_PICKUP)){
                    return[
                        'id' => @$this->id,
                        'type' =>$this->attribute,
                        'trx' => @$this->trx_id,
                        'transaction_type' => $this->type,
                        'transaction_heading' => __("Send Remittance to @")." " .  " (".@$this->details->receiver->email.")",
                        'request_amount' => get_amount($this->request_amount,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                        'total_charge' => get_amount($this->charge->total_charge,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                       'exchange_rate' => get_amount(1, $this->details->charges->sender_cur_code??get_default_currency_code())." = ".get_amount($this->details->charges->exchange_rate??$this->details->to_country->rate,$this->details->to_country->code,$this->details->charges->r_precision_digit??2),
                        'payable' => get_amount($this->payable,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                        'sending_country' => @$this->details->form_country,
                        'receiving_country' => @$this->details->to_country->country,
                        'receipient_name' => @$this->details->receiver->firstname.' '.@$this->details->receiver->lastname,
                        'remittance_type' => Str::slug(GlobalConst::TRX_CASH_PICKUP) ,
                        'remittance_type_name' => $transactionType ,
                        'recipient_get' =>  get_amount(@$this->details->recipient_amount,@$this->details->to_country->code,$this->details->charges->r_precision_digit??2),
                        'pickup_point' => ucwords(str_replace('-', ' ', @$this->details->receiver->alias)),
                        'current_balance' => get_amount($this->available_balance,$this->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                        'status' => @$this->stringStatus->value ,
                        'date_time' => @$this->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                        'rejection_reason' =>$this->reject_reason??"" ,
                        'account_number' => @$this->details->bank_account??''
                    ];
                }

            }elseif($this->attribute == payment_gateway_const()::RECEIVED){
            return[
                    'id' => @$this->id,
                    'type' =>$this->attribute,
                    'trx' => @$this->trx_id,
                    'transaction_type' => $this->type,
                    'transaction_heading' => __("Received Remittance from")." @" ." (".@$this->details->sender->email.")",
                    'request_amount' => get_amount($this->payable,$this->details->charges->receiver_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                    'sending_country' => @$this->details->form_country,
                    'receiving_country' => @$this->details->to_country->country,
                    'sender_recipient_name' => @$this->details->sender_recipient->firstname.' '.@$this->details->sender_recipient->lastname,
                    'receiver_recipient_name' => @$this->details->receiver_recipient->firstname.' '.@$this->details->receiver_recipient->lastname,
                    'remittance_type' => Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER) ,
                    'remittance_type_name' => $transactionType ,
                    'recipient_get' =>  get_amount(@$this->details->recipient_amount,@$this->details->to_country->code,$this->details->charges->r_precision_digit??2),
                    'current_balance' => get_amount($this->available_balance,$this->details->charges->receiver_cur_code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                    'status' => @$this->stringStatus->value ,
                    'date_time' => @$this->created_at ,
                    'status_info' =>(object)@$statusInfo ,
                    'rejection_reason' =>$this->reject_reason??"" ,
                ];

            }
    }
}
