@extends('admin.pdf.app')

@section('title', __('Crypto Exchange pdf'))

@section('content')
    <div class="mt-30">
        <table class="table">
            <tr class="table-header">
                <td>{{ __('Date') }}</td>
                <td>{{ __('User') }}</td>
                <td>{{ __('Type') }}</td>
                <td>{{ __('Amount') }}</td>
                <td>{{ __('Fees') }}</td>
                <td>{{ __('Total') }}</td>
                <td>{{ __('Rate') }}</td>
                <td>{{ __('From') }}</td>
                <td>{{ __('To') }}</td>
                <td>{{ __('Status') }}</td>
            </tr>

            @foreach($crypto_exchanges as $exchange)
                <tr class="table-body">
                    <td>{{ dateFormat($exchange->created_at) }}</td>
                    <td>{{ isset($exchange->email_phone) ? $exchange->email_phone : getColumnValue($exchange->user)  }}</td>
                    <td>{{ getColumnValue(optional($exchange->transaction)->transaction_type, 'name')  }}</td>
                    <td>{{ formatNumber($exchange->amount, $exchange->from_currency) }}</td>
                    <td>{{ formatNumber($exchange->fee, $exchange->from_currency) }}</td>
                    <td>{{ formatNumber(($exchange->fee + $exchange->amount), $exchange->from_currency) }}</td>
                    <td>{{ moneyFormat( optional($exchange->toCurrency)->symbol, formatNumber($exchange->exchange_rate, $exchange->to_currency) ) }}</td>
                    <td>{{ optional($exchange->fromCurrency)->code }} </td>
                    <td>{{ optional($exchange->toCurrency)->code }}</td>
                    <td>{{ getStatus($exchange->status) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection