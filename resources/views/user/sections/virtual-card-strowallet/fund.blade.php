@extends('user.layouts.master')

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{__(@$page_title)}}</h3>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="row mb-30-none">
                <div class="col-xl-6 mb-30">
                    <div class="dash-payment-item-wrapper">
                        <div class="dash-payment-item active">
                            <div class="dash-payment-title-area">
                                <span class="dash-payment-badge">!</span>
                                <h5 class="title">{{ __(@$page_title) }}</h5>
                            </div>
                            <div class="dash-payment-body">
                                <form class="card-form row" action="{{ setRoute('user.strowallet.virtual.card.fund') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $myCard->id }}">
                                    <div class="col-xl-12 col-lg-12 form-group text-center">
                                        <div class="exchange-area">
                                            <code class="d-block text-center">
                                                <span>{{ __("Exchange Rate") }} <span class="f-exchange-rate">--</span></span>
                                            </code>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 col-lg-12 form-group">
                                        <label>{{ __("Amount") }} <span class="text--base">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form--control number-input" required placeholder="{{__('enter Amount')}}" name="fund_amount" value="{{ old("fund_amount") }}">
                                            <select class="form--control nice-select currency" name="currency">
                                                @foreach ($supported_currency as $item)
                                                <option value="{{ $item->code }}"
                                                    data-code="{{ $item->code }}"
                                                    data-symbol="{{ $item->symbol }}"
                                                    data-rate="{{ $item->rate }}"
                                                    data-wallet="{{ $item->id }}"
                                                    data-currency-id="{{ $item->id }}"
                                                    data-name="{{ $item->country }}">{{ $item->code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 col-lg-12 form-group">
                                        <label>{{ __("From Wallet") }} <span class="text--base">*</span></label>
                                        <select class="form--control select2-auto-tokenize from_currency" name="from_currency">
                                            @foreach ($from_wallets as $item)
                                            <option value="{{ $item->currency->code }}"
                                                data-code="{{ $item->currency->code }}"
                                                data-symbol="{{ $item->currency->symbol }}"
                                                data-rate="{{ $item->currency->rate }}"
                                                data-type="{{ $item->currency->type }}"
                                                data-balance="{{ $item->balance }}"
                                                data-wallet="{{ $item->id }}"
                                                data-currency-id="{{ $item->currency->id }}"
                                                data-name="{{ $item->currency->country }}">{{ucwords($item->currency->name) ."( ".get_amount($item->balance,$item->currency->code." )")}}</option>
                                            @endforeach
                                        </select>
                                        <div class="note-area mt-10">
                                            <code class="d-block f-fees-show">--</code>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 col-lg-12 form-group">
                                        <button type="submit" class="btn--base w-100 fundBtn btn-loading">{{__("Fund Card")}} <i class="las la-plus-circle ms-1"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 mb-30">
                    <div class="dash-payment-item-wrapper">
                        <div class="dash-payment-item active">
                            <div class="dash-payment-title-area">
                                <span class="dash-payment-badge">!</span>
                                <h5 class="title">{{__("Preview")}}</h5>
                            </div>
                            <div class="dash-payment-body">
                                <div class="preview-list-wrapper">

                                    <div class="preview-list-item">
                                        <div class="preview-list-left">
                                            <div class="preview-list-user-wrapper">
                                                <div class="preview-list-user-icon">
                                                    <i class="las la-coins"></i>
                                                </div>
                                                <div class="preview-list-user-content">
                                                    <span>{{ __("Fund Amount") }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="preview-list-right">
                                            <span class="fw-bold request-amount">--</span>
                                        </div>
                                    </div>
                                    <div class="preview-list-item">
                                        <div class="preview-list-left">
                                            <div class="preview-list-user-wrapper">
                                                <div class="preview-list-user-icon">
                                                    <i class="las la-battery-half"></i>
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
                                                    <i class="las la-money-check-alt"></i>
                                                </div>
                                                <div class="preview-list-user-content">
                                                    <span>{{__("Total Payable")}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="preview-list-right">
                                            <span class="last payable-total text-warning">--</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    {{-- limit section  --}}
                    <div class="dash-payment-item-wrapper limit">
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
                                    @if ($cardReloadCharge->daily_limit > 0)
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
                                    @if ($cardReloadCharge->monthly_limit > 0)
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

        </div>

    </div>
</div>
@endsection

@push('script')
<script>
    var defualCurrency = "{{ get_default_currency_code() }}";
    var defualCurrencyRate = "{{ get_default_currency_rate() }}";

    $(document).ready(function(){
        getExchangeRate();
        getLimit();
        getDailyMonthlyLimit();
        getFees();
        getPreview();
        get_remaining_limits();
    });

    $("input[name=fund_amount]").keyup(function(){
        getFees();
        getPreview();
        get_remaining_limits();
    });


    $("input[name=fund_amount]").focusout(function(){
        enterLimit();
        get_remaining_limits();
    });
    $(".currency").change(function(){
        getExchangeRate();
        getLimit();
        getDailyMonthlyLimit();
        getFees();
        getPreview();
        get_remaining_limits();
    });
    $(".from_currency").change(function(){
        getExchangeRate();
        getLimit();
        getDailyMonthlyLimit();
        getFees();
        getPreview();
        get_remaining_limits();
    });

    function acceptVar() {
        var defualCurrency          = defualCurrency;
        var defualCurrencyRate      = defualCurrencyRate;

        var cCurrencySelected       = $(".currency :selected");
        var currencyCode            = $(".currency :selected").val();
        var currencyRate            = $(".currency :selected").data('rate');

        var fCurrencySelected       = $(".from_currency :selected");
        var fCurrencyCode           = $(".from_currency :selected").val();
        var fCurrencyRate           = $(".from_currency :selected").data('rate');
        var senderCurrencyType      = $(".from_currency :selected").data('type');

        var currencyMinAmount ="{{getAmount($cardReloadCharge->min_limit)}}";
        var currencyMaxAmount = "{{getAmount($cardReloadCharge->max_limit)}}";
        var currencyFixedCharge = "{{getAmount($cardReloadCharge->fixed_charge)}}";
        var currencyPercentCharge = "{{getAmount($cardReloadCharge->percent_charge)}}";
        var currencyDailyLimit      = "{{getAmount($cardReloadCharge->daily_limit)}}";
        var currencyMonthlyLimit      = "{{getAmount($cardReloadCharge->monthly_limit)}}";

        if(senderCurrencyType == "CRYPTO"){
            var senderPrecison = "{{ get_precision_from_admin()['crypto_precision_value'] }}";
        }else{
            var senderPrecison = "{{  get_precision_from_admin()['fiat_precision_value'] }}";
        }


        return {
            defualCurrency:defualCurrency,
            defualCurrencyRate:defualCurrencyRate,

            cCurrencySelected:cCurrencySelected,
            currencyCode:currencyCode,
            currencyRate:currencyRate,

            fCurrencySelected:fCurrencySelected,
            fCurrencyCode:fCurrencyCode,
            fCurrencyRate:fCurrencyRate,
            sPrecison:senderPrecison,

            currencyMinAmount:currencyMinAmount,
            currencyMaxAmount:currencyMaxAmount,
            currencyFixedCharge:currencyFixedCharge,
            currencyPercentCharge:currencyPercentCharge,
            currencyDailyLimit:currencyDailyLimit,
            currencyMonthlyLimit:currencyMonthlyLimit,
        };
    }
    function getExchangeRate(){
        var card_currency = acceptVar().currencyCode;
        var card_currency_rate = acceptVar().currencyRate;

        var from_currency = acceptVar().fCurrencyCode;
        var from_currency_rate = acceptVar().fCurrencyRate;

        var rate =  parseFloat(from_currency_rate)/parseFloat(card_currency_rate);
        $('.f-exchange-rate').html("1 " + card_currency + " = " + parseFloat(rate).toFixed(acceptVar().sPrecison) + " " + from_currency);

        return rate;
    }
    function getLimit() {
        var currencyCode = acceptVar().currencyCode;
        var currencyRate = acceptVar().currencyRate;

        var min_limit = acceptVar().currencyMinAmount;
        var max_limit = acceptVar().currencyMaxAmount;

        if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
            var min_limit_calc = parseFloat(min_limit*currencyRate).toFixed(acceptVar().sPrecison);
            var max_limit_clac = parseFloat(max_limit*currencyRate).toFixed(acceptVar().sPrecison);
            $('.limit-show').html( min_limit_calc + " " + currencyCode + " - " + max_limit_clac + " " + currencyCode);

            return {
                minLimit:min_limit_calc,
                maxLimit:max_limit_clac,
            };
        }else {
            $('.limit-show').html("--");
            return {
                minLimit:0,
                maxLimit:0,
            };
        }
    }
    function getDailyMonthlyLimit(){
        var sender_currency = acceptVar().currencyCode;
        var sender_currency_rate = acceptVar().currencyRate;
        var daily_limit = acceptVar().currencyDailyLimit;
        var monthly_limit = acceptVar().currencyMonthlyLimit

        if($.isNumeric(daily_limit) && $.isNumeric(monthly_limit)) {
            if(daily_limit > 0 ){
                var daily_limit_calc = parseFloat(daily_limit * sender_currency_rate).toFixed(acceptVar().sPrecison);
                $('.limit-daily').html( daily_limit_calc + " " + sender_currency);
            }else{
                $('.limit-daily').html("");
            }

            if(monthly_limit > 0 ){
                var montly_limit_clac = parseFloat(monthly_limit * sender_currency_rate).toFixed(acceptVar().sPrecison);
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
    function feesCalculation() {
        var from_currency_rate = acceptVar().fCurrencyRate;
        var exchange_rate = getExchangeRate();
        var sender_amount = $("input[name=fund_amount]").val();
        sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);
        var fixed_charge = acceptVar().currencyFixedCharge;
        var percent_charge = acceptVar().currencyPercentCharge;

        if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
            // Process Calculation
            var fixed_charge_calc = parseFloat(fixed_charge) * parseFloat(exchange_rate);
            var percent_charge_calc = (parseFloat(sender_amount * exchange_rate) / 100) * parseFloat(percent_charge);
            var total_charge = parseFloat(fixed_charge_calc) + parseFloat(percent_charge_calc);
            total_charge = parseFloat(total_charge).toFixed(acceptVar().sPrecison);
            // return total_charge;
            return {
                total: parseFloat(total_charge).toFixed(acceptVar().sPrecison),
                fixed: parseFloat(fixed_charge_calc).toFixed(acceptVar().sPrecison),
                percent: parseFloat(percent_charge).toFixed(acceptVar().sPrecison),
            };
        } else {
            // return "--";
            return false;
        }
    }
    function getFees() {
        var from_currency = acceptVar().fCurrencyCode;
        var percent = acceptVar().currencyPercentCharge;
        var charges = feesCalculation();
        if (charges == false) {
            return false;
        }
        $(".f-fees-show").html("{{ __('Fees') }}: " + parseFloat(charges.fixed).toFixed(acceptVar().sPrecison) + " " + from_currency + " + " + parseFloat(charges.percent).toFixed(acceptVar().sPrecison) + "% = " + parseFloat(charges.total).toFixed(acceptVar().sPrecison) + " " + from_currency);
    }
    function getPreview() {
        var exchange_rate = getExchangeRate();
        var senderAmount = $("input[name=fund_amount]").val();
        var from_currency = acceptVar().fCurrencyCode;
        var card_currency = acceptVar().currencyCode;

        senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;

        // Sending Amount
        $('.request-amount').html( parseFloat(senderAmount).toFixed(acceptVar().sPrecison) + " " + card_currency);

        //Fees
        var charges = feesCalculation();
        var total_charge = 0;
        if(senderAmount == 0){
            total_charge = 0;
        }else{
            total_charge = charges.total;
        }
        $('.fees').html( total_charge + " " + from_currency);
        var totalPay = parseFloat(senderAmount) * parseFloat(exchange_rate)
        var pay_in_total = 0;
        if(senderAmount == 0 ||  senderAmount == ''){
            pay_in_total = 0;
        }else{
            pay_in_total =  parseFloat(totalPay) + parseFloat(charges.total);
        }
        $('.payable-total').html("{{ __('Payable') }}"+" : " + pay_in_total.toFixed(acceptVar().sPrecison) + " " + from_currency);

    }
    function enterLimit(){
        var currencyRate = acceptVar().currencyRate;
        var min_limit = parseFloat("{{getAmount($cardReloadCharge->min_limit)}}") * parseFloat(currencyRate);
        var max_limit =parseFloat("{{getAmount($cardReloadCharge->max_limit)}}") * parseFloat(currencyRate);
        var sender_amount = parseFloat($("input[name=fund_amount]").val());

        if( parseFloat(sender_amount) < parseFloat(min_limit) ){
            throwMessage('error',['{{ __("Please follow the mimimum limit") }}']);
            $('.fundBtn').attr('disabled',true)
        }else if(parseFloat(sender_amount) > parseFloat(max_limit)){
            throwMessage('error',['{{ __("Please follow the maximum limit") }}']);
            $('.fundBtn').attr('disabled',true)
        }else{
            $('.fundBtn').attr('disabled',false)
        }

    }
    function get_remaining_limits(){
        var csrfToken           = $('meta[name="csrf-token"]').attr('content');
        var user_field          = "user_id";
        var user_id             = "{{ userGuard()['user']->id }}";
        var transaction_type    = "{{ payment_gateway_const()::VIRTUALCARD }}";
        var currency_id         = acceptVar().cCurrencySelected.data('currency-id');
        var sender_amount       = $("input[name=fund_amount]").val();

        (sender_amount == "" || isNaN(sender_amount)) ? sender_amount = 0 : sender_amount = sender_amount;

        var charge_id           = "{{ $cardReloadCharge->id }}";
        var attribute           = "{{ payment_gateway_const()::RECEIVED }}"

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
                var sender_currency = acceptVar().currencyCode;

                var status  = response.status;
                var message = response.message;
                var amount_data = response.data;

                if(status == false){
                    $('.fundBtn').attr('disabled',true);
                    $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                    $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                    throwMessage('error',[message]);
                    return false;
                }else{
                    $('.fundBtn').attr('disabled',false);
                    $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                    $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                }
            },
        });
    }

</script>
@endpush
