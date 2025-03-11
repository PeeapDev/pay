<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class GiftCardLogs extends JsonResource
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
            "rejected" =>     4,
            ];
        return[
            'id' => $this->id,
            'trx' => $this->trx_id,
            'transaction_type' => __($this->type),
            'card_unit_price' => get_amount($this->details->card_info->card_amount,$this->details->card_info->card_currency),
            'card_quantity' => $this->details->card_info->qty,
            'card_total_price' => get_amount($this->details->card_info->card_total_amount,$this->details->card_info->card_currency),
            'exchange_rate' => get_amount(1,$this->details->charge_info->card_currency) ." = ". get_amount($this->details->card_info->exchange_rate,$this->details->card_info->user_wallet_currency,get_wallet_precision($this->creator_wallet->currency)),
            'request_amount' => get_amount($this->request_amount,$this->details->card_info->user_wallet_currency,get_wallet_precision($this->creator_wallet->currency)),
            'total_charge' => get_amount($this->charge->total_charge,$this->details->card_info->user_wallet_currency,get_wallet_precision($this->creator_wallet->currency)),
            'payable' => get_amount($this->payable,$this->details->card_info->user_wallet_currency,get_wallet_precision($this->creator_wallet->currency)),
            'card_name' => $this->details->card_info->card_name,
            'receiver_email' => $this->details->card_info->recipient_email,
            'receiver_phone' => $this->details->card_info->recipient_phone,
            'current_balance' => get_amount($this->available_balance,$this->details->card_info->user_wallet_currency,get_wallet_precision($this->creator_wallet->currency)),
            'status' => $this->stringStatus->value ,
            'status_value' => $this->status,
            'date_time' => $this->created_at ,
            'status_info' =>(object)$statusInfo ,
            'rejection_reason' =>$this->reject_reason??"" ,

        ];
    }
}
