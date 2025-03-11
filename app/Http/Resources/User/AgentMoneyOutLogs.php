<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class AgentMoneyOutLogs extends JsonResource
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
                return[
                    'id'                        => @$this->id,
                    'type'                      => $this->attribute,
                    'trx'                       => @$this->trx_id,
                    'transaction_type'          => $this->type,
                    'transaction_heading'       => "Money Out to @" . @$this->details->receiver_email,
                    'request_amount'            => get_amount($this->details->charges->payable,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency)),
                    'payable'                   => get_amount($this->details->charges->payable,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency)),
                    'exchange_rate'             => get_amount(1,$this->details->charges->sender_amount) ." = ". get_amount($this->details->charges->exchange_rate,$this->details->charges->receiver_currency,$this->details->charges->r_precision_digit??2),
                    'total_charge'              => get_amount($this->charge->total_charge,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency)),
                    'recipient_received'        => get_amount($this->details->charges->receiver_amount,$this->details->charges->receiver_currency,$this->details->charges->r_precision_digit??2),
                    'current_balance'           => get_amount($this->available_balance,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency)),
                    'status'                    => @$this->stringStatus->value,
                    'status_values'             => @$this->status,
                    'date_time'                 => @$this->created_at,
                    'status_info'               =>(object)@$statusInfo,
                ];
            }
    }
}
