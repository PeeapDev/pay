<?php

namespace Modules\CryptoExchange\Entities;

class CryptoExchangeTransaction
{
    private $cryptoRelation = ['crypto_exchange:id,receiver_address,file_name,receiving_details,payment_details,exchange_rate,from_currency,to_currency,email_phone,verification_via'];

    private $relations = [];

    public function __construct(private array $transactionRelations = [])
    {
        $this->relations = array_merge($this->cryptoRelation, $this->transactionRelations);
    }

    public function getTransactionDetails($id)
    {
        $data['menu'] = 'transaction';
        $data['sub_menu'] = 'transactions';

        $data['transaction'] = $this->getTransaction($id);

        if(!empty($data['transaction']->crypto_exchange?->receiver_address)) {
            $data['receiver_address'] = optional($data['transaction']->crypto_exchange)->receiver_address;
        }
        if(!empty($data['transaction']->crypto_exchange?->file_name)) {
            $data['file_name'] = optional($data['transaction']->crypto_exchange)->file_name;
        }
        $data['payment_details'] = optional($data['transaction']->crypto_exchange)->payment_details;
        $data['exchange_rate'] = moneyFormat(optional($data['transaction']->crypto_exchange->fromCurrency)->symbol, formatNumber(1, optional($data['transaction']->crypto_exchange)->from_currency)).'  =  '. moneyFormat(optional($data['transaction']->crypto_exchange->toCurrency)->symbol, formatNumber(optional($data['transaction']->crypto_exchange)->exchange_rate, optional($data['transaction']->crypto_exchange)->to_currency) );

        return $data;
    }

    public function getTransaction($id)
    {
        return \App\Models\Transaction::with($this->relations)->find($id);
    }
}
