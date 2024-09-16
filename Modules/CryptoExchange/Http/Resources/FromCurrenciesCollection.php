<?php

namespace Modules\CryptoExchange\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;


class FromCurrenciesCollection extends ResourceCollection
{

    public function toArray($request)
    {
        return FromCurrenciesResource::collection($this->collection);
    }

}
