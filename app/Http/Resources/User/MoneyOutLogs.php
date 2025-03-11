<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class MoneyOutLogs extends JsonResource
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
            'id'                        => $this->id,
            'trx'                       => $this->trx_id,
            'gateway_name'              => $this->currency->gateway->name,
            'gateway_currency_name'     => @$this->currency->name,
            'transaction_type'          => "WITHDRAW",
            'request_amount'            => get_amount($this->request_amount,withdrawCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'payable'                   => get_amount($this->details->charges->payable??$this->request_amount,withdrawCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'will_get'                  => isCrypto($this->payable,withdrawCurrency($this)['gateway_currency'],$this->currency->gateway->crypto),
            'exchange_rate'             => '1 ' .withdrawCurrency($this)['wallet_currency'].' = '.isCrypto($this->details->charges->exchange_rate??$this->currency->rate??1,$this->currency->currency_code??get_default_currency_code(),$this->currency->gateway->crypto),
            'total_charge'              => get_amount($this->charge->total_charge??0,withdrawCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'current_balance'           => get_amount($this->available_balance,withdrawCurrency($this)['wallet_currency']??get_default_currency_code(),get_wallet_precision($this->creator_wallet->currency)),
            'status'                    => $this->stringStatus->value,
            'date_time'                 => $this->created_at,
            'status_info'               => (object)$statusInfo,
            'rejection_reason'          => $this->reject_reason??"",
        ];
    }
}
