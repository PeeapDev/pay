<?php

namespace App\Http\Resources\User;

use App\Constants\PaymentGatewayConst;
use Illuminate\Http\Resources\Json\JsonResource;

class AddSubBalanceLogs extends JsonResource
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
         if($this->attribute == PaymentGatewayConst::SEND){
            $field_type = 'deducted_amount';
            $operation_type = strtoupper("subtract") ;
         }else{
            $field_type = 'receive_amount';
            $operation_type = strtoupper("add");
         }
        return[
            'id' => $this->id,
            'trx' => $this->trx_id,
            'transaction_type' => $this->type,
            'operation_type' => $operation_type,
            'transaction_heading' => __("Balance Update From Admin")." (".$this->creator_wallet->currency->code.")",
            'request_amount' =>  get_transaction_numeric_attribute($this->attribute)." ". get_amount($this->request_amount,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
            'current_balance' => get_amount($this->available_balance,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
            $field_type => get_amount($this->payable,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
            'exchange_rate' => '1 ' .get_default_currency_code().' = '.get_amount($this->creator_wallet->currency->rate,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
            'total_charge' => get_amount($this->charge->total_charge,$this->creator_wallet->currency->code,get_wallet_precision($this->creator_wallet->currency)),
            'remark' => $this->remark,
            'status' => $this->stringStatus->value,
            'date_time' => $this->created_at,
            'status_info' =>(object)$statusInfo,

        ];
    }
}
