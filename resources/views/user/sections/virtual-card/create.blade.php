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
        @include('user.sections.virtual-card.component.create-card')
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
    $("input[name=card_amount]").keyup(function(){
        getFees();
        getPreview();
        get_remaining_limits();
    });
    $("input[name=card_amount]").focusout(function(){
        enterLimit();
        get_remaining_limits();
    });
    $("select[name=currency]").change(function(){
        getExchangeRate();
        getLimit();
        getDailyMonthlyLimit();
        getFees();
        getPreview();
        get_remaining_limits();
    });
    $("select[name=from_currency]").change(function(){
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

        var cCurrencySelected       = $("select[name=currency] :selected");
        var currencyCode            = $("select[name=currency] :selected").val();
        var currencyRate            = $("select[name=currency] :selected").data('rate');

        var fCurrencySelected       = $("select[name=from_currency] :selected");
        var fCurrencyCode           = $("select[name=from_currency] :selected").val();
        var fCurrencyRate           = $("select[name=from_currency] :selected").data('rate');
        var senderCurrencyType      = $("select[name=from_currency] :selected").data('type');

        var currencyMinAmount       ="{{getAmount($cardCharge->min_limit)}}";
        var currencyMaxAmount       = "{{getAmount($cardCharge->max_limit)}}";
        var currencyFixedCharge     = "{{getAmount($cardCharge->fixed_charge)}}";
        var currencyPercentCharge   = "{{getAmount($cardCharge->percent_charge)}}";
        var currencyDailyLimit      = "{{getAmount($cardCharge->daily_limit)}}";
        var currencyMonthlyLimit      = "{{getAmount($cardCharge->monthly_limit)}}";

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
        $('.exchange-rate').html("{{ __('Rate') }}"+": "+"1 " + card_currency + " = " + parseFloat(rate).toFixed(acceptVar().sPrecison) + " " + from_currency);

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
        var sender_amount = $("input[name=card_amount]").val();
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
        $(".fees-show").html("{{ __('Fees') }}: " + parseFloat(charges.fixed).toFixed(acceptVar().sPrecison) + " " + from_currency + " + " + parseFloat(charges.percent).toFixed(acceptVar().sPrecison) + "% = " + parseFloat(charges.total).toFixed(acceptVar().sPrecison) + " " + from_currency);
    }
    function getPreview() {
            var exchange_rate = getExchangeRate();
            var senderAmount = $("input[name=card_amount]").val();
            var from_currency = acceptVar().fCurrencyCode;
            var card_currency = acceptVar().currencyCode;

            senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;
            // Sending Amount
            $('.request-amount').html( senderAmount + " " + card_currency);

            // Fees
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
        var min_limit = parseFloat("{{getAmount($cardCharge->min_limit)}}") * parseFloat(currencyRate);
        var max_limit =parseFloat("{{getAmount($cardCharge->max_limit)}}") * parseFloat(currencyRate);
        var sender_amount = parseFloat($("input[name=card_amount]").val());
        if( parseFloat(sender_amount) < parseFloat(min_limit) ){
            throwMessage('error',['{{ __("Please follow the mimimum limit") }}']);
            $('.buyBtn').attr('disabled',true)
        }else if(parseFloat(sender_amount) > parseFloat(max_limit)){
            throwMessage('error',['{{ __("Please follow the maximum limit") }}']);
            $('.buyBtn').attr('disabled',true)
        }else{
            $('.buyBtn').attr('disabled',false)
        }

    }
    function get_remaining_limits(){
        var csrfToken           = $('meta[name="csrf-token"]').attr('content');
        var user_field          = "user_id";
        var user_id             = "{{ userGuard()['user']->id }}";
        var transaction_type    = "{{ payment_gateway_const()::VIRTUALCARD }}";
        var currency_id         = acceptVar().cCurrencySelected.data('currency-id');
        var sender_amount       = $("input[name=card_amount]").val();

        (sender_amount == "" || isNaN(sender_amount)) ? sender_amount = 0 : sender_amount = sender_amount;

        var charge_id           = "{{ $cardCharge->id }}";
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
                    $('.buyBtn').attr('disabled',true);
                    $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                    $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                    throwMessage('error',[message]);
                    return false;
                }else{
                    $('.buyBtn').attr('disabled',false);
                    $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                    $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                }
            },
        });
    }

</script>
@endpush
