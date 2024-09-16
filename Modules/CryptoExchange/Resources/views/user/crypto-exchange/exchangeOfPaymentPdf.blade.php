@extends('user.pdf.app')

@section('title', __('Crypto Exchange pdf'))
    
@section('content')
    <!-- Transaction details start -->
    <table class="tabl-width">
        <tbody>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('User') }}</span>
                    <h2 class="text-lg">{{ !empty($currencyExchange->user_id) ? getColumnValue($currencyExchange->user) : $currencyExchange->email_phone }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Transaction Type') }}</span>
                    <h2 class="text-lg">{{ ucwords(str_replace('_',' ',$currencyExchange->type)) }}</h2>
                </td>
            </tr>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Exchange From') }}</span>
                    <h2 class="text-lg">{{ optional($currencyExchange->fromCurrency)->code }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Exchange To') }}</span>
                    <h2 class="text-lg">{{ optional($currencyExchange->toCurrency)->code }}</h2>
                </td>
            </tr>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Transaction Date') }}</span>
                    <h2 class="text-lg">{{ dateFormat($currencyExchange->created_at) }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Transaction ID') }}</span>
                    <h2 class="text-lg">{{ $currencyExchange->uuid }}</h2>
                </td>
            </tr>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Exchanged Amount') }}</span>
                    <h2 class="text-lg">{{ moneyFormat(optional($currencyExchange->fromCurrency)->symbol, formatNumber($currencyExchange->amount, optional($currencyExchange->fromCurrency)->id)) }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Receivering amount') }}</span>
                    <h2 class="text-lg">{{ moneyFormat(optional($currencyExchange->toCurrency)->symbol, formatNumber($currencyExchange->get_amount, optional($currencyExchange->toCurrency)->id)) }}</h2>
                </td>
            </tr>
            <tr>
                <td class="pxy-36 align-left">
                    <span class="text-sm">{{ __('Status') }}</span>
                    <h2 class="text-lg {{ getColor($currencyExchange->status) }}">{{ $currencyExchange->status }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Transaction Rate') }}</span>
                    <h2 class="text-lg">{{ moneyFormat(optional($currencyExchange->fromCurrency)->code, formatNumber(1, optional($currencyExchange->fromCurrency)->id)) }} = {{ moneyFormat(optional($currencyExchange->toCurrency)->code, formatNumber($currencyExchange->exchange_rate, optional($currencyExchange->toCurrency)->id)) }}</h2>
                </td>
            </tr>
            @if($currencyExchange->type !== 'crypto_sell' && $currencyExchange->receive_via == 'address')
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Receiving via') }}</span>
                    <h2 class="text-lg">{{ ucfirst($currencyExchange->receive_via) }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Address') }}</span>
                    <h2 class="text-lg">{{ $currencyExchange->receiver_address }}</h2>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
    <!-- Transaction details end -->

    <!-- Transaction amount start -->
    <table class="tabl-width">
        <tbody>

            <tr>
                <td class="px-desc">
                    <p class="desc-title">{{ __('Description') }}</p>
                </td>
            </tr>
            <tr>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ __('Sub Total') }}</p>
                </td>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ optional($currencyExchange->fromCurrency)->symbol }} {{ formatNumber($currencyExchange->amount, optional($currencyExchange->fromCurrency)->id) }}</p>
                </td>
            </tr>
            <tr>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ __('Fees') }}</p>
                </td>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{  moneyFormat(optional($currencyExchange->fromCurrency)->symbol, formatNumber($currencyExchange->fee, optional($currencyExchange->fromCurrency)->id)) }}</p>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="pt-0 pb-10 pl-80 pr-120">
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ __('Total') }}</p>
                </td>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ optional($currencyExchange->fromCurrency)->symbol }} {{ formatNumber($currencyExchange->amount + $currencyExchange->fee, optional($currencyExchange->fromCurrency)->id) }}</p>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- Transaction amount end -->
@endsection
