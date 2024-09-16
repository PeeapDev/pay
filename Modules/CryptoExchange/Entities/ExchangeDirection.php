<?php

namespace Modules\CryptoExchange\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Auth;
use App\Models\{Currency,
    PaymentMethod,
    CurrencyPaymentMethod
};


class ExchangeDirection extends Model
{
    use HasFactory;

    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    public static function getDirection($from_currency_id, $to_currency_id)
    {
        return self::where([
            'from_currency_id' => $from_currency_id,
            'to_currency_id' => $to_currency_id
        ])->first();
    }

    public static function getDirections($from_currency_id)
    {
        return self::where('from_currency_id', $from_currency_id)->get();
    }

    /**
     * Method getCurrencies() [Get the to currencies based on from currencies and direction type]
     *
     * @param INT $from_currency_id [send currency id]
     * @param String $type [Type - exchange, buy or sell]
     *
     * @return Collection
     */
    public static function getCurrencies($from_currency_id, $type)
    {
        if (empty($from_currency_id)) {
           return;
        }

        if (preference('available') == 'guest_user' &&  $type == 'crypto_buy') {
            $to_currency_ids = self::toCurrenciesWithGateway($from_currency_id, $type);
        } elseif (!Auth::check() && ($type == 'crypto_buy')) {
            $to_currency_ids = self::toCurrenciesWithGateway($from_currency_id, $type);
        } else {
            // Both Authenticate Unauthenticate and Crypto exchange
            $to_currency_ids = self::where(['from_currency_id' => $from_currency_id, 'type' => $type, 'status' => 'Active' ])->pluck('to_currency_id')->toArray();
        }

        if (empty($to_currency_ids)) {
            return;
        }

        return Currency::whereStatus('Active')->findMany($to_currency_ids, ['id', 'name', 'symbol', 'code', 'type', 'logo', 'status']);
    }

    public static function currencyPaymentMethodList($from_currency_id, $gateway_list = '')
    {
        $paymentMethods = [];

        $currencyPaymentMethods = CurrencyPaymentMethod::with('method')->where('currency_id', $from_currency_id)->where('activated_for', 'like', "%crypto_buy%");

        if ($gateway_list !== '') {
            $currencyPaymentMethods = $currencyPaymentMethods->whereIn('method_id', $gateway_list);
        }

        $currencyPaymentMethods = $currencyPaymentMethods->get();

        foreach ($currencyPaymentMethods as $currencyPaymentMethod) {
            $paymentMethods[$currencyPaymentMethod->id] = $currencyPaymentMethod->method['id'];
        }

        return PaymentMethod::where('status', 'Active')->whereIn('id', $paymentMethods)->get(['id','name']);
    }

    public static function toCurrenciesWithGateway($from_currency_id, $type)
    {
        $directions = self::where(['from_currency_id' => $from_currency_id, 'type' => $type, 'status' => 'active' ])->whereNotNull('gateways')->get();
        $to_currency_ids = [];

        foreach ($directions as $key => $direction) {
            $gateways = explode(',' , $direction->gateways);
            $currencyPaymentMethods = [];
            $currencyPaymentMethods = CurrencyPaymentMethod::with('method')->whereIn('method_id', $gateways)->where('activated_for', 'like', "%crypto_buy%")->get();
            if (count($currencyPaymentMethods)) {
                $to_currency_ids[$key]['id'] = $direction->to_currency_id;
            }
        }

        return $to_currency_ids;
    }

    public function exchangeDirection($exchangeType, $relation = [])
    {
        return ExchangeDirection::with($relation)
        ->whereHas('fromCurrency', function($query) {
            $query->where('status', 'Active');
        })->whereHas('toCurrency', function($query) {
            $query->where('status', 'Active');
        })
        ->where(['status' => 'Active', 'type' => $exchangeType])->get();
    }

    public function exchangeDirectionWithGateway($exchangeType, $gateways,  $relation = [])
    {
        return $this->exchangeDirection($exchangeType, $relation)->whereNotNull($gateways);
    }

    public function exchangeDirectionWithCurrency($exchangeType, $currencyId,  $relation = [])
    {
        return $this->exchangeDirection($exchangeType, $relation)->unique($currencyId);
    }
}

