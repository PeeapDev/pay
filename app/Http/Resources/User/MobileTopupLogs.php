<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class MobileTopupLogs extends JsonResource
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
            "success"       => 1,
            "pending"       => 2,
            "hold"          => 3,
            "rejected"      => 4,
            "waiting"       => 5,
            "failed"        => 6,
            "processing"    => 7,
            ];
            if(isset($this->details->topup_type) && $this->details->topup_type == "MANUAL"){
                $exchange_rate = get_amount(1,$this->details->charges->destination_currency)." = ".get_amount($this->details->charges->exchange_rate,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency));
                $will_get = get_amount($this->details->charges->sender_amount,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency));
            }elseif(isset($this->details->topup_type) && $this->details->topup_type == "AUTOMATIC"){
                $exchange_rate = get_amount(1,$this->details->charges->sender_currency)." = ".get_amount($this->details->charges->exchange_rate,$this->details->charges->destination_currency,get_wallet_precision($this->creator_wallet->currency));
                $will_get = get_amount($this->details->charges->conversion_amount,$this->details->charges->destination_currency,get_wallet_precision($this->creator_wallet->currency));
            }else{
                $exchange_rate = get_amount(1,$this->details->charges->destination_currency)." = ".get_amount($this->details->charges->exchange_rate,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency));
                $will_get = get_amount($this->details->charges->sender_amount,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency));
            }


        return[
            'id' => $this->id,
            'trx' => $this->trx_id,
            'transaction_type' => $this->type,
            'request_amount' => get_amount($this->request_amount,topUpCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'exchange_rate'  => $exchange_rate,
            'payable' => get_amount($this->payable,topUpCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'will_get' => $will_get,
            'topup_type' => $this->details->topup_type_name,
            'mobile_number' =>$this->details->mobile_number,
            'total_charge' => get_amount($this->charge->total_charge,topUpCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'current_balance' => get_amount($this->available_balance,topUpCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'status' => $this->stringStatus->value,
            'status_value' => $this->status,
            'date_time' => $this->created_at,
            'status_info' =>(object)$statusInfo,
            'rejection_reason' =>$this->reject_reason??"",

        ];
    }
}
