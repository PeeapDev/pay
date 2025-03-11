@extends('agent.layouts.master')

@php
    $base_code =  getDialCode();
@endphp
@push('css')
    <style>
        .input-group.mobile-code .nice-select{
            border-radius: 5px 0 0 5px !important;
        }
        .input-group.mobile-code .nice-select .list{
            width: auto !important;
        }
        .input-group.mobile-code .nice-select .list::-webkit-scrollbar {
            height: 20px;
            width: 3px;
            background: #F1F1F1;
            border-radius: 10px;
        }

        .input-group.mobile-code .nice-select .list::-webkit-scrollbar-thumb {
            background: #999;
            border-radius: 10px;
        }

        .input-group.mobile-code .nice-select .list::-webkit-scrollbar-corner {
            background: #999;
            border-radius: 10px;
        }
    </style>
@endpush

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("agent.dashboard"),
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
    <div class="row mb-30-none">
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __("Recharge") }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute('agent.mobile.topup.manual.confirm') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center">
                                            <span>{{ __("Exchange Rate") }} <span class="exchange-rate">--</span></span>
                                        </code>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6  form-group">
                                    <label>{{ __("Mobile Topup") }} <span class="text--base">*</span></label>
                                    <select class="form--control" name="topup_type">
                                        @forelse ($topupType ??[] as $type)
                                           <option value="{{ $type->id }}" data-name="{{ $type->name }}">{{ $type->name }}</option>
                                        @empty
                                           <option disabled selected value="null">{{ __('No Items Available') }}</option>
                                        @endforelse

                                    </select>
                                </div>

                                <div class="col-xl-6 col-lg-6  form-group">
                                    <label>{{ __("Mobile Number") }}<span>*</span></label>
                                    <div class="input-group mobile-code">
                                        <select class="form--control nice-select" name="mobile_code">
                                            @foreach(get_all_countries() ?? [] as $key => $code)
                                                <option value="{{ remove_speacial_char($code->mobile_code) }}" {{ $code->name === auth()->user()->address->country ? 'selected' :'' }}>+{{ remove_speacial_char($code->mobile_code) }}</option>
                                            @endforeach

                                        </select>
                                        <input type="text" class="form--control number-input" name="mobile_number" placeholder="{{ __("enter Mobile Number") }}" value="{{ old('mobile_number') }}">
                                    </div>

                                </div>
                                <div class="col-xxl-12 col-xl-12 col-lg-12  form-group">
                                    <label>{{ __("Amount") }}<span>*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" required placeholder="{{__('enter Amount')}}" name="amount" value="{{ old("amount") }}">
                                        <select class="form--control nice-select currency" name="currency">
                                            @foreach ($sender_wallets ?? [] as $data)
                                                <option value="{{  $data->code }}"
                                                    data-rate="{{ $data->rate }}"
                                                    data-type="{{ $data->type }}"
                                                    data-currency-id="{{ $data->id }}"
                                                    data-sender-country-name="{{ $data->name }}"
                                                    >{{  $data->code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <div class="note-area">
                                        <code class="d-block fw-bold balance-show">{{ __("Available Balance") }}: {{ authWalletBalance() }} {{ get_default_currency_code() }}</code>
                                    </div>
                                </div>

                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading mobileTopupBtn">{{ __("Recharge Now") }} <i class="fas fa-mobile ms-1"></i></button>
                                </div>
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
                        <h5 class="title">{{ __("Preview") }}</h5>
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
                                            <span>{{ __("Sending Wallet") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fw-bold sending-wallet">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-plug"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("TopUp Type") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="topup-type">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-phone-volume"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Mobile Number") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="mobile-number">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-funnel-dollar"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="request-amount">--</span>
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
                                            <span>{{ __("Total Payable") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--base last payable-total">--</span>
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
                            @if ($topupCharge->daily_limit > 0)
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
                            @if ($topupCharge->monthly_limit > 0)
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
@endsection

@push('script')
<script>
    var defualCurrency = "{{ get_default_currency_code() }}";
    var defualCurrencyRate = "{{ get_default_currency_rate() }}";

    $(document).ready(function(){
        senderBalance();
        getLimit();
        getDailyMonthlyLimit();
        get_remaining_limits();
        getFees();
        getExchangeRate();
        activeItems();
    });
    $("input[name=amount]").keyup(function(){
        getFees();
        get_remaining_limits();
        activeItems();
    });
    $("input[name=amount]").focusout(function(){
        enterLimit();
        get_remaining_limits();
    });
    $("input[name=mobile_number]").keyup(function(){
        getFees();
        activeItems();
    });
    $("select[name=topup_type]").change(function(){
        getFees();
        activeItems();
    });
    $("select[name=mobile_code]").change(function(){
        activeItems();
    });
    $("select[name=currency]").change(function(){
        senderBalance();
        getLimit();
        get_remaining_limits();
        getDailyMonthlyLimit();
        getFees();
        getExchangeRate();
        activeItems();
    });
    function getLimit() {
    if(acceptVar().topUp.val() === "null"){
        return false;
    }
        var currencyCode = acceptVar().currencyCode;
        var currencyRate = acceptVar().currencyRate;

        var min_limit = acceptVar().currencyMinAmount;
        var max_limit =acceptVar().currencyMaxAmount;
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
    function acceptVar() {
        var selectedVal             = $("select[name=currency] :selected");
        var currencyCode            = $("select[name=currency] :selected").val();
        var currencyRate            = $("select[name=currency] :selected").data('rate');
        var senderCurrencyType      = $("select[name=currency] :selected").data('type');

        var mobileNumber            = $("input[name=mobile_number]").val();

        var currencyMinAmount       ="{{getAmount($topupCharge->min_limit)}}";
        var currencyMaxAmount       = "{{getAmount($topupCharge->max_limit)}}";
        var currencyFixedCharge     = "{{getAmount($topupCharge->fixed_charge)}}";
        var currencyPercentCharge   = "{{getAmount($topupCharge->percent_charge)}}";
        var currencyDailyLimit      = "{{getAmount($topupCharge->daily_limit)}}";
        var currencyMonthlyLimit      = "{{getAmount($topupCharge->monthly_limit)}}";

        var topUp                   = $("select[name=topup_type] :selected");
        var topUpname               = $("select[name=topup_type] :selected").data("name");
        var mobileCode              = $("select[name=mobile_code] :selected").val();
        var mobileNumber            = $("input[name=mobile_number]").val();

        if(senderCurrencyType == "CRYPTO"){
            var senderPrecison = "{{ get_precision_from_admin()['crypto_precision_value'] }}";
        }else{
            var senderPrecison = "{{  get_precision_from_admin()['fiat_precision_value'] }}";
        }

        return {
            currencyCode:currencyCode,
            currencyRate:currencyRate,
            sPrecison:senderPrecison,

            currencyMinAmount:currencyMinAmount,
            currencyMaxAmount:currencyMaxAmount,
            currencyFixedCharge:currencyFixedCharge,
            currencyPercentCharge:currencyPercentCharge,
            currencyDailyLimit:currencyDailyLimit,
            currencyMonthlyLimit:currencyMonthlyLimit,

            topUpname:topUpname,
            mobileNumber:mobileNumber,
            mobileCode:mobileCode,
            topUp:topUp,
            selectedVal:selectedVal,

        };
    }
    function feesCalculation() {
        var currencyCode = acceptVar().currencyCode;
        var currencyRate = acceptVar().currencyRate;
        var sender_amount = $("input[name=amount]").val();
        sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

        var fixed_charge = acceptVar().currencyFixedCharge;
        var percent_charge = acceptVar().currencyPercentCharge;
        if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
            // Process Calculation
            var fixed_charge_calc = parseFloat(currencyRate * fixed_charge);
            var percent_charge_calc = (parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
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
        if(acceptVar().topUp.val() === "null"){
            return false;
        }
        var currencyCode = acceptVar().currencyCode;
        var percent = acceptVar().currencyPercentCharge;
        var charges = feesCalculation();
        if (charges == false) {
            return false;
        }
        $(".fees-show").html("{{ __('TopUp Fee') }}: " + parseFloat(charges.fixed).toFixed(acceptVar().sPrecison) + " " + currencyCode + " + " + parseFloat(charges.percent).toFixed(acceptVar().sPrecison) + "%  ");
    }
    function activeItems(){
        var topUp = acceptVar().topUp.val();
        if(topUp === undefined || topUp === '' || topUp === null){
            return false;
        }else{
            return getPreview();
        }
    }
    function getPreview() {
        var senderAmount = $("input[name=amount]").val();
        var sender_currency = acceptVar().currencyCode;
        var sender_currency_rate = acceptVar().currencyRate;
        var topup_type = acceptVar().topUpname;
        var mobile_number = acceptVar().mobileNumber;
        var mobile_code = acceptVar().mobileCode;
        senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;

        $(".sending-wallet").text(sender_currency + " (" + acceptVar().selectedVal.data('sender-country-name') + ")");
        // Sending Amount
        $('.request-amount').text(parseFloat(senderAmount).toFixed(acceptVar().sPrecison) + " " + sender_currency);
        //topup type
        $('.topup-type').text(topup_type);
        // Fees
        //topup number
        if(mobile_number == '' || mobile_number == 0){
            $('.mobile-number').text("Ex: +1234567891");
        }else{
            $('.mobile-number').text("+"+mobile_code+mobile_number);
        }

        // Fees
        var charges = feesCalculation();
        var total_charge = 0;
        if(senderAmount == 0){
            total_charge = parseFloat(0).toFixed(acceptVar().sPrecison);
        }else{
            total_charge = charges.total;
        }

        $('.fees').text(total_charge + " " + sender_currency);

        // Pay In Total
        var totalPay = parseFloat(senderAmount)
        var pay_in_total = 0;
        if(senderAmount == 0){
            pay_in_total = 0;
        }else{
            pay_in_total =  parseFloat(totalPay) + parseFloat(charges.total);
        }
        $('.payable-total').text(parseFloat(pay_in_total).toFixed(acceptVar().sPrecison) + " " + sender_currency);

    }
    function enterLimit(){
        var currencyRate = acceptVar().currencyRate;
        var min_limit = parseFloat("{{getAmount($topupCharge->min_limit)}}") * parseFloat(currencyRate);
        var max_limit =parseFloat("{{getAmount($topupCharge->max_limit)}}") * parseFloat(currencyRate);
        var sender_amount = parseFloat($("input[name=amount]").val());

        if( sender_amount < min_limit ){
            throwMessage('error',['{{ __("Please follow the mimimum limit") }}']);
            $('.mobileTopupBtn').attr('disabled',true)
        }else if(sender_amount > max_limit){
            throwMessage('error',['{{ __("Please follow the maximum limit") }}']);
            $('.mobileTopupBtn').attr('disabled',true)
        }else{
            $('.mobileTopupBtn').attr('disabled',false)
        }
    }
    function senderBalance() {
            var senderCurrency = acceptVar().currencyCode;
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: 'POST',
                url: "{{ route('agent.wallets.balance') }}",
                data: {
                    target: senderCurrency,
                    _token: csrfToken
                },
                success: function(response) {
                    $('.balance-show').html("{{ __('Available Balance') }}: " + parseFloat(response.data).toFixed(acceptVar().sPrecison) + " " + senderCurrency);
                }
            });
    }
    function getExchangeRate(){
        var sender_currency = acceptVar().currencyCode;
        var sender_currency_rate = acceptVar().currencyRate;
        var rate = parseFloat(sender_currency_rate);
        $('.exchange-rate').html("1 " + defualCurrency + " = " + parseFloat(rate).toFixed(acceptVar().sPrecison) + " " + sender_currency);

        return rate;
    }
    function get_remaining_limits(){
        var csrfToken           = $('meta[name="csrf-token"]').attr('content');
        var user_field          = "agent_id";
        var user_id             = "{{ userGuard()['user']->id }}";
        var transaction_type    = "{{ payment_gateway_const()::MOBILETOPUP }}";
        var currency_id         = acceptVar().selectedVal.data('currency-id');
        var sender_amount       = $("input[name=amount]").val();

        (sender_amount == "" || isNaN(sender_amount)) ? sender_amount = 0 : sender_amount = sender_amount;

        var charge_id           = "{{ $topupCharge->id }}";
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
                var sender_currency = acceptVar().currencyCode;

                var status  = response.status;
                var message = response.message;
                var amount_data = response.data;

                if(status == false){
                    $('.mobileTopupBtn').attr('disabled',true);
                    $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                    $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                    throwMessage('error',[message]);
                    return false;
                }else{
                    $('.mobileTopupBtn').attr('disabled',false);
                    $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                    $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                }
            },
        });
    }

</script>


@endpush
