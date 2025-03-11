<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class MerchantPaymentLogs extends JsonResource
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
        return [
            'id' => $this->id,
            'trx' => $this->trx_id,
            'transaction_type' => $this->type,
            'transaction_heading' => "Payment Money to @" . @$this->details->payment_to." (".@$this->details->pay_type.")",
            'request_amount' =>get_amount($this->request_amount,$this->details->charges->sender_currency,2),
            'payable' => get_amount($this->details->charges->payable,$this->details->charges->payable_currency??get_default_currency_code(),2),
            'env_type' => $this->details->env_type,
            'sender_amount' => get_amount($this->details->charges->sender_amount,$this->details->charges->sender_currency,2),
            'recipient' =>  $this->details->receiver_username,
            'recipient_amount' => get_amount($this->details->charges->receiver_amount,$this->details->charges->receiver_currency,2),
            'status' => $this->stringStatus->value ,
            'date_time' => $this->created_at ,
            'status_info' =>(object)$statusInfo ,
            'rejection_reason' =>$this->reject_reason??"" ,
        ];
    }
}
