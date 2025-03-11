<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class VirtualCardLogs extends JsonResource
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
            'id' => $this->id,
            'trx' => $this->trx_id,
            'transaction_type' => "Virtual Card".'('. @$this->remark.')',
            'request_amount' => get_amount($this->request_amount,$this->details->charges->card_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
            'payable' => get_amount($this->payable,$this->details->charges->from_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
            'exchange_rate' => get_amount(1,$this->details->charges->card_currency??get_default_currency_code()) ." = ". get_amount($this->details->charges->exchange_rate??1,$this->details->charges->from_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
            'total_charge' => get_amount($this->charge->total_charge,$this->details->charges->from_currency??get_default_currency_code()),
            'card_amount' => get_amount(@$this->details->card_info->amount??@$this->details->card_info->balance,$this->details->charges->card_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
            'card_number' => $this->details->card_info->card_pan??$this->details->card_info->maskedPan??$this->details->card_info->card_number??"---- ---- ---- ----",
            'current_balance' => get_amount($this->available_balance,$this->details->charges->from_currency??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
            'status' => $this->stringStatus->value,
            'status_value' => $this->status,
            'date_time' => $this->created_at ,
            'status_info' =>(object)$statusInfo ,

        ];

    }
}
