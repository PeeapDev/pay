<?php

namespace App\Http\Resources\Merchant;

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
        return[
            'id' => $this->id,
            'trx' => $this->trx_id,
            'transaction_type' => $this->type,
            'transaction_heading' => "Payment Money from @" . @$this->details->sender_username." (".@$this->details->pay_type.")",
            'request_amount' => get_amount($this->request_amount,$this->details->charges->sender_currency,2),
            'payable' => get_amount($this->details->charges->receiver_amount,$this->details->charges->receiver_currency,2),
            'env_type' => $this->details->env_type,
            'sender' =>  $this->details->sender_username,
            'business_name' =>  $this->details->payment_to,
            'payment_amount' => get_amount($this->details->charges->receiver_amount,$this->details->charges->receiver_currency,2),
            'status' => $this->stringStatus->value ,
            'date_time' => $this->created_at ,
            'status_info' =>(object)$statusInfo ,
            'rejection_reason' =>$this->reject_reason??"" ,

        ];
    }
}
