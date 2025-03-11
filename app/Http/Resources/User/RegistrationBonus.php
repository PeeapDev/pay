<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationBonus extends JsonResource
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
            'id'                => $this->id,
            'trx'               => $this->trx_id,
            'transaction_type'  => __("Registration Bonus"),
            'attribute'         => $this->attribute,
            'request_amount'    => get_amount($this->request_amount,$this->details->request_currency),
            'payable'           => get_amount($this->request_amount,$this->details->request_currency),
            'current_balance'   => get_amount($this->available_balance,$this->details->request_currency),
            'status'            => $this->stringStatus->value ,
            'date_time'         => $this->created_at ,
            'status_info'       =>(object)$statusInfo ,

        ];

    }
}
