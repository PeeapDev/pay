
@extends('merchant.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('merchant.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("merchant.dashboard"),
        ]
    ], 'active' => __("Money Exchange")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="row mt-20 mb-20-none">
        <div class="col-xl-7 col-lg-7 mb-20">
            <div class="custom-card mt-10">
                <div class="dashboard-header-wrapper">
                    <h4 class="title">{{ __("Money Exchange") }}</h4>
                </div>
                <div class="card-body">
                    <form class="card-form" action="{{ setRoute('merchant.money.exchange.submit') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 form-group text-center">
                                <div class="exchange-area">
                                    <code class="d-block text-center"><span>{{ __("Exchange Rate") }}</span> <span class="exchangeRateShow"></span></code>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group">
                                <label>{{ __("Exchange From") }}<span class="text--base">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control number-input" name="exchange_from_amount" value="{{ old('exchange_from_amount')}}" placeholder="{{ __("enter Amount") }}">
                                    <select class="form--control nice-select exchangeFromCurrency" name="exchange_from_currency">
                                        @foreach ($user_wallets as $item)
                                        <option
                                        value="{{ $item->currency->code }}"
                                        data-id="{{ $item->currency->id }}"
                                        data-rate="{{ $item->currency->rate }}"
                                        data-code="{{ $item->currency->code }}"
                                        data-type="{{ $item->currency->type }}"
                                        data-symbol="{{ $item->currency->symbol }}"
                                        data-balance="{{ $item->balance }}"
                                        data-country="{{ $item->currency->name }}"
                                        {{ get_default_currency_code() == $item->currency->code ? "selected": "" }}
                                            >{{ $item->currency->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <div class="note-area">
                                        <code class="d-block fees-show"></code>
                                        <code class="d-block mt-10 text-end fromWalletBalanceShow"></code>
                                    </div>
                                </div>

                            </div>
                            <div class="col-xl-12 col-lg-12 form-group">
                                <label>{{ __("Exchange To") }}<span class="text--base">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control number-input" name="exchange_to_amount" placeholder="{{ __("enter Amount") }}">
                                    <select class="form--control nice-select exchangeToCurrency" name="exchange_to_currency">
                                        @foreach ($user_wallets as $key => $item)
                                            <option
                                                value="{{ $item->currency->code }}"
                                                data-id="{{ $item->currency->id }}"
                                                data-rate="{{ $item->currency->rate }}"
                                                data-code="{{ $item->currency->code }}"
                                                data-type="{{ $item->currency->type }}"
                                                data-country="{{ $item->currency->name }}"
                                                {{ $loop->last ? "selected" : "" }}
                                                >
                                                {{ $item->currency->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12">
                            <button type="submit" class="btn--base w-100 exchange">{{ __("Exchange Money") }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-lg-5 mb-20">
            <div class="custom-card mt-10">
                <div class="dashboard-header-wrapper">
                    <h4 class="title">{{ __("Preview") }}</h4>
                </div>
            </div>
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h4 class="title">{{ __("Preview") }}</h4>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("From Wallet") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--success fromWallet">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("To Exchange") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="toExchange">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Exchange Rate") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="rateShow">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Total Exchange Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--danger requestAmount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Converted Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="receiveAmount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Total Charge") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fees">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Total Payable") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="payInTotal">--</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            {{-- limit section  --}}
            <div class="dash-payment-item-wrapper mt-10">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{__("Limit Information")}}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-wallet"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Transaction Limit") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="limit-show">--</span>
                                </div>
                            </div>
                            @if ($charges->daily_limit > 0)
                                <div class="preview-list-item">
                                    <div class="preview-list-left">
                                        <div class="preview-list-user-wrapper">
                                            <div class="preview-list-user-icon">
                                                <i class="las la-wallet"></i>
                                            </div>
                                            <div class="preview-list-user-content">
                                                <span>{{ __("Daily Limit") }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-list-right">
                                        <span class="limit-daily">--</span>
                                    </div>
                                </div>
                                <div class="preview-list-item">
                                    <div class="preview-list-left">
                                        <div class="preview-list-user-wrapper">
                                            <div class="preview-list-user-icon">
                                                <i class="las la-wallet"></i>
                                            </div>
                                            <div class="preview-list-user-content">
                                                <span>{{ __("Remaining Daily Limit") }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-list-right">
                                        <span class="daily-remaining">--</span>
                                    </div>
                                </div>
                            @endif
                            @if ($charges->monthly_limit > 0)
                                <div class="preview-list-item">
                                    <div class="preview-list-left">
                                        <div class="preview-list-user-wrapper">
                                            <div class="preview-list-user-icon">
                                                <i class="las la-wallet"></i>
                                            </div>
                                            <div class="preview-list-user-content">
                                                <span>{{ __("Monthly Limit") }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-list-right">
                                        <span class="limit-monthly">--</span>
                                    </div>
                                </div>
                                <div class="preview-list-item">
                                    <div class="preview-list-left">
                                        <div class="preview-list-user-wrapper">
                                            <div class="preview-list-user-icon">
                                                <i class="las la-wallet"></i>
                                            </div>
                                            <div class="preview-list-user-content">
                                                <span>{{ __("Remaining Monthly Limit") }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-list-right">
                                        <span class="monthly-remaining">--</span>
                                    </div>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title ">{{__("Money Exchange Log")}}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('merchant.transactions.index','money-exchange') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('merchant.components.transaction-log',compact("transactions"))
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        $(document).ready(function(){
            callFunctions();
            getReceiver_amount();
            $('.fromWalletBalanceShow').html("{{ __('Available Balance') }}: " + $("select[name=exchange_from_currency] :selected").attr("data-symbol") + parseFloat($("select[name=exchange_from_currency] :selected").attr("data-balance")).toFixed(acceptVar().exchangeFromDigit));
        })
        $('.exchangeFromCurrency').on('change', function(){
            callFunctions();

            $('.fromWalletBalanceShow').html("{{ __('Available Balance') }}: " + $("select[name=exchange_from_currency] :selected").attr("data-symbol") + parseFloat($("select[name=exchange_from_currency] :selected").attr("data-balance")).toFixed(acceptVar().exchangeFromDigit));
        })
        $('.exchangeToCurrency').on('change', function(){
            callFunctions()

        })
        $('input[name=exchange_from_amount]').keyup(function(){
            callFunctions()

        })
        $('input[name=exchange_to_amount]').keyup(function(){
            getReceiver_amount();
        })
        function callFunctions() {
            getExchangeRate();
            previewDetails();
            getFees();
            getLimit();
            getDailyMonthlyLimit();
            get_remaining_limits();
        }

        var fixedCharge     = "{{ $charges->fixed_charge ?? 0 }}";
        var percentCharge   = "{{ $charges->percent_charge ?? 0 }}";
        var minLimit        = "{{ $charges->min_limit ?? 0 }}";
        var maxLimit        = "{{ $charges->max_limit ?? 0 }}";
        var dailyLimit      = "{{ $charges->daily_limit ?? 0}}";
        var monthlyLimit    = "{{ $charges->monthly_limit ?? 0}}";



        function acceptVar() {
            var exchangeFromAmount = $("input[name=exchange_from_amount]").val();
            var exchangeFromRate = $("select[name=exchange_from_currency] :selected").attr("data-rate");
            var exchangeFromCode = $("select[name=exchange_from_currency] :selected").attr("data-code");
            var exchangeFromCountry = $("select[name=exchange_from_currency] :selected").attr("data-country");
            var exchangeFromType = $("select[name=exchange_from_currency] :selected").attr("data-type");

            var exchangeToAmount = $("input[name=exchange_to_amount]").val();
            var exchangeToRate = $("select[name=exchange_to_currency] :selected").attr("data-rate");
            var exchangeToCode = $("select[name=exchange_to_currency] :selected").attr("data-code");
            var exchangeToCountry = $("select[name=exchange_to_currency] :selected").attr("data-country");
            var exchangeToType = $("select[name=exchange_to_currency] :selected").attr("data-type");


            if(exchangeFromType == "CRYPTO"){
                var exchangeFromDigit = "{{ get_precision_from_admin()['crypto_precision_value'] }}";
            }else{
                var exchangeFromDigit = "{{  get_precision_from_admin()['fiat_precision_value'] }}";
            }
            if (exchangeToType == "CRYPTO") {
                var exchangeToDigit = "{{ get_precision_from_admin()['crypto_precision_value'] }}";
            } else {
                var exchangeToDigit = "{{  get_precision_from_admin()['fiat_precision_value'] }}";
            }

            return {
                exchangeFromAmount: exchangeFromAmount,
                exchangeFromRate: exchangeFromRate,
                exchangeFromCode: exchangeFromCode,
                exchangeFromCountry: exchangeFromCountry,
                exchangeFromDigit: exchangeFromDigit,

                exchangeToAmount:exchangeToAmount,
                exchangeToRate: exchangeToRate,
                exchangeToCode: exchangeToCode,
                exchangeToCountry: exchangeToCountry,
                exchangeToDigit: exchangeToDigit,

            };
        }
        //calculate exchange rate
        function getExchangeRate(){
            var exchangeRate = parseFloat(acceptVar().exchangeToRate) / parseFloat(acceptVar().exchangeFromRate);

            $('.exchangeRateShow').html("1 " + acceptVar().exchangeFromCode +" = " + exchangeRate.toFixed(acceptVar().exchangeToDigit) + " " + acceptVar().exchangeToCode);
            var exchangeToConverMmount = acceptVar().exchangeFromAmount*exchangeRate;
            $("input[name=exchange_to_amount]").val(exchangeToConverMmount.toFixed(acceptVar().exchangeToDigit));
        }
        function getReceiver_amount(){
               //receiver amount
                var exchangeRateTo = parseFloat(acceptVar().exchangeFromRate) / parseFloat(acceptVar().exchangeToRate) ;
                var exchangeFromConverMmount = acceptVar().exchangeToAmount*exchangeRateTo;
                $("input[name=exchange_from_amount]").val(exchangeFromConverMmount.toFixed(acceptVar().exchangeFromDigit));
                previewDetails();

        }
        function getLimit(){
            var exchangeFromCode =  acceptVar().exchangeFromCode;
            var min_limit = minLimit;
            var max_limit = maxLimit;

            var min_limit_calc = parseFloat(min_limit*acceptVar().exchangeFromRate);
            var max_limit_clac = parseFloat(max_limit*acceptVar().exchangeFromRate);
            $('.limit-show').html(min_limit_calc.toFixed(acceptVar().exchangeFromDigit) + " " + exchangeFromCode + " - " + max_limit_clac.toFixed(acceptVar().exchangeFromDigit) + " " + exchangeFromCode);

        }
        function getDailyMonthlyLimit(){
            var sender_currency = acceptVar().exchangeFromCode;
            var daily_limit = dailyLimit;
            var monthly_limit = monthlyLimit;

            if($.isNumeric(daily_limit) && $.isNumeric(monthly_limit)) {
                if(daily_limit > 0 ){
                    var daily_limit_calc = parseFloat(daily_limit * acceptVar().exchangeFromRate).toFixed(acceptVar().exchangeFromDigit);
                    $('.limit-daily').html( daily_limit_calc + " " + sender_currency);
                }else{
                    $('.limit-daily').html("");
                }

                if(monthly_limit > 0 ){
                    var montly_limit_clac = parseFloat(monthly_limit * acceptVar().exchangeFromRate).toFixed(acceptVar().exchangeFromDigit);
                    $('.limit-monthly').html( montly_limit_clac + " " + sender_currency);

                }else{
                    $('.limit-monthly').html("");
                }

            }else {
                $('.limit-daily').html("--");
                $('.limit-monthly').html("--");
                return {
                    dailyLimit:0,
                    monthlyLimit:0,
                };
            }

        }
        //calculate fees
        function feesCalculation(){
            var exchangeFromAmount =  acceptVar().exchangeFromAmount;
            var exchangeFromRate =  acceptVar().exchangeFromRate;
            var exchangeFromCode =  acceptVar().exchangeFromCode;

            var fixedChargeCalculation = parseFloat(exchangeFromRate)*fixedCharge;
            var percentChargeCalculation = parseFloat(percentCharge/100)*parseFloat(exchangeFromAmount*1);
            var totalCharge = fixedChargeCalculation+percentChargeCalculation;

            return {
                fixed_charge: fixedChargeCalculation,
                percent_charge: percentChargeCalculation,
                total_charge: totalCharge,
            };

        }
        function getFees() {
            var exchangeFromCode =  acceptVar().exchangeFromCode;
            var charges = feesCalculation();
            $('.fees-show').html("{{ __('Charge') }}: " + parseFloat(charges.fixed_charge).toFixed(acceptVar().exchangeFromDigit) + " " + exchangeFromCode +" + " + parseFloat(percentCharge) + "%" + " = "+ parseFloat(charges.total_charge).toFixed(acceptVar().exchangeFromDigit) + " " + exchangeFromCode);
        }
        //preview details
        function previewDetails(){
            var exchangeFromAmount =  acceptVar().exchangeFromAmount;
            var exchangeFromRate =  acceptVar().exchangeFromRate;
            var exchangeFromCode =  acceptVar().exchangeFromCode;
            var exchangeFromCountry =  acceptVar().exchangeFromCountry;

            var exchangeToAmount =  acceptVar().exchangeToAmount;
            var exchangeToRate =  acceptVar().exchangeToRate;
            var exchangeToCode =  acceptVar().exchangeToCode;
            var exchangeToCountry =  acceptVar().exchangeToCountry;

            //exchange rate
            var exchangeRate = parseFloat(exchangeToRate) / parseFloat(exchangeFromRate);

            $('.fromWallet').html(exchangeFromCountry+" ("+exchangeFromCode+")");
            $('.toExchange').html(exchangeToCountry+" ("+exchangeToCode+")");
            $('.rateShow').html("1 " + exchangeFromCode +" = " + exchangeRate.toFixed(acceptVar().exchangeToDigit) + " " + exchangeToCode)
            $('.requestAmount').html(parseFloat(exchangeFromAmount*1).toFixed(acceptVar().exchangeFromDigit) + " " +exchangeFromCode);
            //converted amount
            var convertedAmount = exchangeFromAmount*exchangeRate.toFixed(acceptVar().exchangeToDigit);
            $('.receiveAmount').html(parseFloat(exchangeToAmount).toFixed(acceptVar().exchangeToDigit) + " " +exchangeToCode);
            //show total fees
            var charges = feesCalculation();
            $('.fees').html(charges.total_charge.toFixed(acceptVar().exchangeFromDigit) + " " + exchangeFromCode);
            // Pay In Total
            var pay_in_total = parseFloat(charges.total_charge) + parseFloat(exchangeFromAmount*1);
            $('.payInTotal').text(parseFloat(pay_in_total).toFixed(acceptVar().exchangeFromDigit) + " " + exchangeFromCode);
        }

        function get_remaining_limits(){
            var csrfToken           = $('meta[name="csrf-token"]').attr('content');
            var user_field          = "merchant_id";
            var user_id             = "{{ userGuard()['user']->id }}";
            var transaction_type    = "{{ payment_gateway_const()::TYPEMONEYEXCHANGE }}";
            var currency_id         =  $("select[name=exchange_from_currency] :selected").attr("data-id");

            var sender_amount       = acceptVar().exchangeFromAmount;

            (sender_amount == "" || isNaN(sender_amount)) ? sender_amount = 0 : sender_amount = sender_amount;

            var charge_id           = "{{ $charges->id }}";
            var attribute           = "{{ payment_gateway_const()::SEND }}"

            $.ajax({
                type: 'POST',
                url: "{{ route('global.get.total.transactions') }}",
                data: {
                    _token:             csrfToken,
                    user_field:         user_field,
                    user_id:            user_id,
                    transaction_type:   transaction_type,
                    currency_id:        currency_id,
                    sender_amount:      sender_amount,
                    charge_id:          charge_id,
                    attribute:          attribute,
                },
                success: function(response) {
                    var sender_currency = acceptVar().exchangeFromCode;
                    var status  = response.status;
                    var message = response.message;
                    var amount_data = response.data;

                    if(status == false){
                        $('.exchange').attr('disabled',true);
                        $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                        $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                        throwMessage('error',[message]);
                        return false;
                    }else{
                        $('.exchange').attr('disabled',false);
                        $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                        $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                    }
                },
            });
        }

    </script>
@endpush
