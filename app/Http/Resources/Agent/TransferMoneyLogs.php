<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Resources\Json\JsonResource;

class TransferMoneyLogs extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $statusInfo = [
            "success" =>      1,
            "pending" =>      2,
            "rejected" =>     3,
            ];
            if($this->attribute == payment_gateway_const()::SEND){
                $receiver = $this->details->receiver->email??$this->details->receiver_email;
                return[
                    'id' => @$this->id,
                    'type' =>$this->attribute,
                    'trx' => @$this->trx_id,
                    'transaction_type' => $this->type,
                    'transaction_heading' => __("Send Money to")." (@" .$receiver.")",
                    'request_amount' => get_amount($this->details->charges->sender_amount??$this->request_amount,$this->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                    'payable' => get_amount($this->details->charges->payable??$this->payable,$this->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                    'exchange_rate' => get_amount(1,$this->details->charges->sender_currency??get_default_currency_code())." = ".get_amount($this->details->charges->exchange_rate??get_default_currency_rate(),$this->details->charges->receiver_currency??get_default_currency_code(),$this->details->charges->r_precision_digit??2),
                    'total_charge' => get_amount($this->charge->total_charge,$this->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                    'recipient_received' => get_amount($this->details->charges->receiver_amount??$this->details->recipient_amount,$this->details->charges->receiver_currency??get_default_currency_code(),$this->details->charges->r_precision_digit??2),
                    'current_balance' => get_amount($this->available_balance,$this->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                    'status' => @$this->stringStatus->value,
                    'status_value' => @$this->status,
                    'date_time' => @$this->created_at,
                    'status_info' =>(object)@$statusInfo,
                ];
            }elseif($this->attribute == payment_gateway_const()::RECEIVED){
                $sender = $this->details->sender->email??$this->details->sender_email;
                return[
                    'id' => @$this->id,
                    'type' =>$this->attribute,
                    'trx' => @$this->trx_id,
                    'transaction_type' => $this->type,
                    'transaction_heading' => __("Received Money from")." (@" .$sender.")",
                    'request_amount' => get_amount($this->details->charges->sender_amount??$this->request_amount,$this->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                    'payable' => get_amount($this->details->charges->payable??$this->payable,$this->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                    'exchange_rate' => get_amount(1,$this->details->charges->sender_currency??get_default_currency_code())." = ".get_amount($this->details->charges->exchange_rate??get_default_currency_rate(),$this->details->charges->receiver_currency??get_default_currency_code(),$this->details->charges->r_precision_digit??2),
                    'total_charge' => get_amount($this->charge->total_charge,$this->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                    'recipient_received' => get_amount($this->details->charges->receiver_amount??$this->details->recipient_amount,$this->details->charges->receiver_currency??get_default_currency_code(),$this->details->charges->r_precision_digit??2),
                    'current_balance' => get_amount($this->available_balance,$this->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
                    'status' => @$this->stringStatus->value,
                    'status_value' => @$this->status,
                    'date_time' => @$this->created_at,
                    'status_info' =>(object)@$statusInfo,
                ];

        }

    }
}
