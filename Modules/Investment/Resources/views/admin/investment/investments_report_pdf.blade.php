<!DOCTYPE html>

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title> {{ __('Investments') }} </title>
    <link rel="stylesheet" href="{{ asset('Modules/Investment/Resources/assets/css/admin/investment.min.css') }}">
</head>

<body>
    <div class="date-range">
        <div class="date">
            <div class="time-range">
                <div>
                    <strong>{{ settings('name') }}</strong>
                </div>
                <br>
                <div>
                    {{ __('Period') }} : {{ $date_range }}
                </div>
                <br>
                <div>
                    {{ __('Print Date') }} : {{ dateFormat(now()) }}
                </div>
            </div>
            <div class="logo">
                <div>
                    {!! getSystemLogo('photo') !!}
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <div class="top">
            <table class="table">
                <tr class="table-row">
                    <td>{{ __('Plan') }}</td>
                    <td>{{ __('User') }}</td>
                    <td>{{ __('Currency') }}</td>
                    <td>{{ __('Payment Method') }}</td>
                    <td>{{ __('Amount') }}</td>
                    <td>{{ __('Profit') }}</td>
                    <td>{{ __('Total') }}</td>
                    <td>{{ __('Total Term') }}</td>
                    <td>{{ __('Start Time') }}</td>
                    <td>{{ __('End Time') }}</td>
                    <td>{{ __('Status') }}</td>
                </tr>

                @foreach ($investments as $investment)
                    <tr class="table-data">
                        <td>{{ getColumnValue($investment->investmentPlan, 'name') }}</td>
                        <td>
                            {{ getColumnValue($investment->user) }}
                        </td>
                        <td>{{ getColumnValue($investment->currency, 'code') }}</td>
                        <td>{{ optional($investment->paymentMethod)->name == 'Mts' ? __('Wallet') : getColumnValue($investment->paymentMethod, 'name') }}
                        </td>
                        <td>{{ formatNumber($investment->amount, optional( $investment->currency)->id) }}</td>
                        <td>{{ formatNumber($investment->estimate_profit, $investment->currency_id) }}</td>
                        <td>{{ formatNumber($investment->total, $investment->currency_id) }}</td>
                        <td>{{ $investment->term_total }}</td>
                        <td>{{ dateFormat($investment->start_time) }}</td>
                        <td>{{ dateFormat($investment->end_time) }}</td>
                        <td>{{ $investment->status }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</body>

</html>