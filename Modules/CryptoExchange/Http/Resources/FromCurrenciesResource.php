<?php

namespace Modules\CryptoExchange\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FromCurrenciesResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => optional($this->fromCurrency)->id,
            'name' => optional($this->fromCurrency)->name,
            'symbol' => optional($this->fromCurrency)->symbol,
            'type' => optional($this->fromCurrency)->type,
            'code' => optional($this->fromCurrency)->code,
            'logo' => optional($this->fromCurrency)->logo,
            'status' => optional($this->fromCurrency)->status,
        ];


        return $data;

    }

}
