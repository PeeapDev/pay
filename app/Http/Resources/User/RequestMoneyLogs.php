<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestMoneyLogs extends JsonResource
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
                    'id' => $this->id,
                    'title' => __("Request Money to")." @" . $this->details->receiver_username,
                    'trx' => $this->trx_id,
                    'attribute' => $this->attribute,
                    'type' => $this->type,
                    'request_amount' =>  get_amount($this->request_amount,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
                    'payable_amount' => '',
                    'total_charge' => "",
                    'will_get' =>  get_transaction_numeric_attribute_request_money($this->attribute).' '.get_amount($this->details->charges->receiver_amount,$this->details->charges->receiver_currency,get_wallet_precision($this->creator_wallet->currency)),
                    'status' => $this->stringStatus->value,
                    'status_value' => $this->status,
                    'remark' => $this->remark??'',
                    'created_at' => $this->created_at,
                    'status_info'           => (object)$statusInfo,
                ];
            }elseif($this->attribute == payment_gateway_const()::RECEIVED){
                return[
                    'id' => $this->id,
                    'title' => __("Request Money from")." @" . $this->details->sender_username,
                    'trx' => $this->trx_id,
                    'attribute' => $this->attribute,
                    'type' => $this->type,
                    'request_amount' => get_amount($this->request_amount,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
                    'payable_amount' =>   get_transaction_numeric_attribute_request_money($this->attribute).' '. get_amount($this->payable,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
                    'total_charge' => get_amount($this->details->charges->total_charge,$this->creator_wallet->currency->code),
                    'will_get' => "",
                    'status' => $this->stringStatus->value,
                    'status_value' => $this->status,
                    'remark' => $this->remark??'',
                    'created_at' => $this->created_at,
                    'status_info'           => (object)$statusInfo,
                ];

            }
    }
}
