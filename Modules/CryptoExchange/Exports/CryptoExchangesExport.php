<?php

namespace Modules\CryptoExchange\Exports;

use Modules\CryptoExchange\Entities\CryptoExchange;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class CryptoExchangesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user = isset(request()->user_id) ? request()->user_id : null;

        $exchanges = (new CryptoExchange())->getExchangesListForCsvExport($from, $to, $status, $currency, $user)->orderBy('crypto_exchanges.id', 'desc');

        return $exchanges;
    }

    public function headings(): array
    {
        return [
            'Date',
            'User',
            'Type',
            'Amount',
            'Fees',
            'Total',
            'Rate',
            'From',
            'To',
            'Status',
        ];
    }

    public function map($currencyExchange): array
    {
        // Amount
        $amount = formatNumber($currencyExchange->amount, $currencyExchange->from_currency);

        //Total Amount
        $total =  formatNumber($currencyExchange->fee + $currencyExchange->amount, $currencyExchange->from_currency);

        // From Currency Code
        $fromCurrencyCode = getColumnValue($currencyExchange->fromCurrency, 'code');

        // To Currency Code
        $toCurrencyCode = getColumnValue($currencyExchange->toCurrency, 'code');

        $user = isset($currencyExchange->email_phone) ? $currencyExchange->email_phone : getColumnValue($currencyExchange->user) ;

        return [
            dateFormat($currencyExchange->created_at),
            $user,
            (isset($currencyExchange->transaction->transaction_type->name) && !empty($currencyExchange->transaction->transaction_type->name)) ? str_replace('_', ' ', $currencyExchange->transaction->transaction_type->name) : '-',
            formatNumber($amount, $currencyExchange->from_currency),
            ($currencyExchange->fee == 0) ? "-" : formatNumber($currencyExchange->fee, $currencyExchange->from_currency),
            formatNumber($total, $currencyExchange->from_currency),
            moneyFormat(isset($currencyExchange->toCurrency->symbol) ? optional($currencyExchange->toCurrency)->symbol: ''  , formatNumber($currencyExchange->exchange_rate, $currencyExchange->to_currency)),
            $fromCurrencyCode,
            $toCurrencyCode,
            getStatus($currencyExchange->status)
        ];
    }

    public function styles($currencyExchange)
    {
        $currencyExchange->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $currencyExchange->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $currencyExchange->getStyle('E:F')->getAlignment()->setHorizontal('center');
        $currencyExchange->getStyle('G:H')->getAlignment()->setHorizontal('center');
        $currencyExchange->getStyle('I')->getAlignment()->setHorizontal('center');
        $currencyExchange->getStyle('1')->getFont()->setBold(true);
    }
}
