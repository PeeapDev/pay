<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class MoneyInLogs extends JsonResource
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
        return[
            'id' => @$this->id,
            'type' =>$this->attribute,
            'trx' => @$this->trx_id,
            'transaction_type' => $this->type,
            'transaction_heading' => __("Money In From")." @" . @$this->details->sender_email,
            'request_amount' => get_amount(@$this->request_amount,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency)),
            'total_charge' => get_amount(0,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency)),
            'payable' => get_amount(@$this->payable,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency)),
            'recipient_received' => get_amount(@$this->details->charges->receiver_amount,$this->details->charges->receiver_currency,$this->details->charges->r_precision_digit??2),
            'current_balance' => get_amount(@$this->available_balance,$this->details->charges->receiver_currency,get_wallet_precision($this->creator_wallet->currency)),
            'status' => @$this->stringStatus->value ,
            'date_time' => @$this->created_at ,
            'status_info' =>(object)@$statusInfo ,
        ];
    }
}
