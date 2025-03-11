<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Resources\Json\JsonResource;

class BillPayLogs extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
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
        return[
            'id'                        => $this->id,
            'trx'                       => $this->trx_id,
            'transaction_type'          => $this->type,
            'request_amount'            => get_amount($this->request_amount,billPayCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'payable'                   => get_amount($this->payable,billPayCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'exchange_rate'             => get_amount(1,$this->details->charges->wallet_currency)." = ".get_amount($this->details->charges->exchange_rate,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency)),
            'bill_type'                 => $this->details->bill_type_name,
            'bill_month'                => $this->details->bill_month??"",
            'bill_number'               => $this->details->bill_number,
            'bill_amount'               => get_amount($this->details->charges->conversion_amount,$this->details->charges->sender_currency,get_wallet_precision($this->creator_wallet->currency)),
            'total_charge'              => get_amount($this->charge->total_charge,billPayCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'current_balance'           => get_amount($this->available_balance,billPayCurrency($this)['wallet_currency'],get_wallet_precision($this->creator_wallet->currency)),
            'status'                    => $this->stringStatus->value,
            'date_time'                 => $this->created_at,
            'status_info'               => (object)$statusInfo,
            'status_value'              => $this->status,
            'rejection_reason'          =>$this->reject_reason??"",

        ];

    }
}
