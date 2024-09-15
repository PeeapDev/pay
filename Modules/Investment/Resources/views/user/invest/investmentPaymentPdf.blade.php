<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>{{ __('Print') }}</title>
    <link rel="stylesheet" href="{{ asset('Modules/Investment/Resources/assets/css/user/invest_pdf.min.css') }}">
</head>

<body>
    <div class="main-div">
        <table class="table">
            <tr>
                <td>
                    {!! getSystemLogo() !!}
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td class="title"> {{ __('Investment Via') }} </td>
                        </tr>
                        <tr>
                            <td class="text"> {{ optional($transactionDetails->payment_method)->name == 'Mts' ? __('Wallet') : optional($transactionDetails->payment_method)->name }}
                            </td>
                        </tr>
                        <br><br>
                        <tr>
                            <td class="title"> {{ __('Invested to') }}</td>
                        </tr>
                        <tr>
                            <td class="text">{{ optional($transactionDetails->currency)->code }}</td>
                        </tr>
                        <br><br>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table>
                        <tr>
                            <td class="title"> {{ __('Payment Method') }} </td>
                        </tr>
                        <tr>
                            <td class="text">{{ $transactionDetails->uuid }}</td>
                        </tr>
                        <br><br>
                        <tr>
                            <td class="title"> {{ __('Transaction Date') }} </td>
                        </tr>
                        <tr>
                            <td class="text">{{ dateFormat($transactionDetails->created_at) }}</td>
                        </tr>
                        <br><br>
                        <tr>
                            <td class="title"> {{ __('Status') }} </td>
                        </tr>
                        <tr>
                            <td class="text">
                                {{ $transactionDetails->status == 'Blocked' ? __('Cancelled') : ($transactionDetails->status == 'Refund' ? __('Refunded') : __($transactionDetails->status)) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table class="table2">
                        <tr>
                            <td colspan="2" class="detail-title"> {{ __('Details') }} </td>
                        </tr>
                        <tr>
                            <td class="invest-amount-title">{{ __('Investment Amount') }}</td>
                            <td class="invest-amount-text">
                                {{ moneyFormat(optional($transactionDetails->currency)->symbol, formatNumber($transactionDetails->subtotal, optional($transactionDetails->currency)->id)) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
