<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Resources\Json\JsonResource;

class AddMoneyLogs extends JsonResource
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
            "success"   =>     1,
            "pending"   =>     2,
            "rejected"  =>     3,
        ];
        return[
            'id'                => $this->id,
            'trx'               => $this->trx_id,
            'gateway_name'      => @$this->currency->name,
            'transaction_type'  => $this->type,
            'request_amount'    => get_amount($this->request_amount,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
            'payable'           => isCrypto($this->payable,$this->currency->currency_code??get_default_currency_code(),$this->currency->gateway->crypto),
            'exchange_rate'     => '1 ' . $this->creator_wallet->currency->code.' = '.isCrypto($this->details->amount->exchange_rate??$this->currency->rate,$this->currency->currency_code??get_default_currency_code(),$this->currency->gateway->crypto),
            'total_charge'      => isCrypto($this->charge->total_charge??0,$this->currency->currency_code??get_default_currency_code(),$this->currency->gateway->crypto),
            'current_balance'   => get_amount($this->available_balance,$this->creator_wallet->currency->code??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
            "confirm"           => $this->confirm??false,
            "dynamic_inputs"    => $this->dynamic_inputs,
            "confirm_url"       => $this->confirm_url,
            'status'            => $this->stringStatus->value ,
            'date_time'         => $this->created_at ,
            'status_info'       => (object)$statusInfo ,
            'rejection_reason'  => $this->reject_reason??"" ,

        ];
    }
}
