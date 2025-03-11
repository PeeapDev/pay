<?php

namespace App\Http\Resources\Merchant;

use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeMoneyLogs extends JsonResource
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
                'transaction_heading' => $this->details->charges->from_wallet_country .' '.__("to").' '.$this->details->charges->to_wallet_country,
                'request_amount' => get_amount($this->request_amount,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
                'payable' => get_amount($this->payable,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
                'exchange_rate' => get_amount(1, $this->creator_wallet->currency->code ) .' = ' . get_amount($this->details->charges->exchange_rate,$this->details->charges->exchange_currency,$this->details->charges->r_precision_digit??2) ,
                'total_charge' => get_amount($this->charge->total_charge,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
                'exchangeable_amount' => get_amount($this->details->charges->exchange_amount,$this->details->charges->exchange_currency,$this->details->charges->r_precision_digit??2),
                'current_balance' => get_amount($this->available_balance,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
                'status' => @$this->stringStatus->value,
                'status_value' => @$this->status,
                'date_time' => @$this->created_at,
                'status_info' =>(object)@$statusInfo,
            ];
    }
}
