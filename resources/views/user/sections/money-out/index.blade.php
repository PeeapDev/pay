@extends('user.layouts.master')

@push('css')

@endpush

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
            <h3 class="title">{{__($page_title)}}</h3>
        </div>
    </div>
    <div class="row mb-30-none">
        <div class="col-lg-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __($page_title) }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute('user.money.out.insert') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center"><span>{{ __("Exchange Rate") }}</span> <span class="rate-show">--</span></code>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Payment Gateway") }}<span>*</span></label>
                                    <select class="form--control nice-select gateway-select" name="gateway">
                                        @forelse ($payment_gateways ?? [] as $item)
                                            <option
                                                value="{{ $item->alias  }}"
                                                data-currency="{{ $item->currency_code }}"
                                                data-min_amount="{{ $item->min_limit }}"
                                                data-max_amount="{{ $item->max_limit }}"
                                                data-percent_charge="{{ $item->percent_charge }}"
                                                data-fixed_charge="{{ $item->fixed_charge }}"
                                                data-rate="{{ $item->rate }}"
                                                data-crypto="{{ $item->gateway->crypto}}"
                                                data-daily_limit="{{ $item->daily_limit}}"
                                                data-monthly_limit="{{ $item->monthly_limit}}"
                                                data-currency-id="{{ $item->id}}"
                                                >
                                                {{ $item->name }}
                                            </option>
                                        @empty
                                            <option value="null" disabled  selected>{{ __('No Gateway Available') }}</option>
                                        @endforelse
                                    </select>
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">

                                    <label>{{ __("Amount") }}<span>*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" required placeholder="{{ __('enter Amount') }}" name="amount" value="{{ old("amount") }}">
                                        <select class="form--control nice-select currency" name="currency">
                                            @foreach ($wallet_currencies ?? [] as $data)
                                                <option value="{{  $data->code }}"
                                                    data-rate="{{ $data->rate }}"
                                                    data-type="{{ $data->type}}"
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
                                        <code class="d-block fees-show">--</code>
                                        <code class="d-block mt-10 text-end text--dark fw-bold balance-show">--</code>
                                    </div>
                                </div>
                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading withdrawBtn">{{ __("Withdraw Money") }} <i class="fas fa-arrow-alt-circle-right ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __($page_title) }} {{__("Preview")}}</h5>
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
                                            <span>{{ __("Entered Amount") }}</span>
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
                                            <i class="lab la-get-pocket"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Conversion Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="conversionAmount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Total Fees & Charges") }}</span>
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
                                            <span class="">{{ __("Will Get") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--success will-get">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span class="last">{{ __("Payable Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--warning last total-pay">--</span>
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


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title ">{{__("withdraw Log")}}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('user.transactions.index','withdraw') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('user.components.transaction-log',compact("transactions"))
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
         var defualCurrency = "{{ get_default_currency_code() }}";
         var defualCurrencyRate = "{{ get_default_currency_rate() }}";

        $('select[name=gateway]').on('change',function(){
            isCrypto();
            walletBalance();
            getExchangeRate();
            getLimit();
            getDailyMonthlyLimit();
            get_remaining_limits();
            getFees();
            activeItems();
        });
        $('select[name=currency]').on('change',function(){
            isCrypto();
            walletBalance();
            getExchangeRate();
            getLimit();
            getDailyMonthlyLimit();
            get_remaining_limits();
            getFees();
            activeItems();
        });
        $(document).ready(function(){
            isCrypto();
            walletBalance();
            getExchangeRate();
            getLimit();
            getDailyMonthlyLimit();
            get_remaining_limits();
            getFees();
            activeItems();
        });
        $("input[name=amount]").keyup(function(){
             getFees();
             activeItems();
             get_remaining_limits();
        });

        function acceptVar(){
            var selectedVal             = $("select[name=gateway] :selected");
            var currencyCode            = $("select[name=gateway] :selected").attr("data-currency");
            var currencyRate            = $("select[name=gateway] :selected").attr("data-rate");
            var currencyMinAmount       = $("select[name=gateway] :selected").attr("data-min_amount");
            var currencyMaxAmount       = $("select[name=gateway] :selected").attr("data-max_amount");
            var currencyFixedCharge     = $("select[name=gateway] :selected").attr("data-fixed_charge");
            var currencyPercentCharge   = $("select[name=gateway] :selected").attr("data-percent_charge");
            var currencyDailyLimit      = $("select[name=gateway] :selected").attr("data-daily_limit");
            var currencyMonthlyLimit      = $("select[name=gateway] :selected").attr("data-monthly_limit");

            var wCurrencyVal            = $("select[name=currency] :selected");
            var wCurrencyCode           = $("select[name=currency] :selected").val();
            var wCurrencyRate           = $("select[name=currency] :selected").data('rate');
            var walletCurrencyType      = $("select[name=currency] :selected").data('type');

            if(walletCurrencyType == "CRYPTO"){
                var walletPrecison = "{{ get_precision_from_admin()['crypto_precision_value'] }}";
            }else{
                var walletPrecison = "{{  get_precision_from_admin()['fiat_precision_value'] }}";
            }


            return {
                currencyCode:currencyCode,
                currencyRate:currencyRate,
                currencyMinAmount:currencyMinAmount,
                currencyMaxAmount:currencyMaxAmount,
                currencyFixedCharge:currencyFixedCharge,
                currencyPercentCharge:currencyPercentCharge,
                currencyDailyLimit:currencyDailyLimit,
                currencyMonthlyLimit:currencyMonthlyLimit,
                selectedVal:selectedVal,

                wCurrencyVal:wCurrencyVal,
                wCurrencyCode:wCurrencyCode,
                wCurrencyRate:wCurrencyRate,
                wPrecison:walletPrecison,

            };
        }
        function isCrypto(){
            var pricison = 0;
            var crypto = acceptVar().selectedVal.data('crypto');
            if(crypto == 1){
                pricison= "{{ get_precision_from_admin()['crypto_precision_value'] }}";
            }else{
                 pricison= "{{  get_precision_from_admin()['fiat_precision_value'] }}";
            }
            return pricison;
        }
        function getExchangeRate() {
            if(acceptVar().selectedVal.val() === "null"){
                return false;
            }
            var pricison = isCrypto();
            var gCurrencyCode = acceptVar().currencyCode;
            var gCurrencyRate = acceptVar().currencyRate;
            var wCurrencyCode = acceptVar().wCurrencyCode;
            var wCurrencyRate = acceptVar().wCurrencyRate;
            var exchange_rate = parseFloat(gCurrencyRate / wCurrencyRate).toFixed(pricison);
            $('.rate-show').html("1 " + wCurrencyCode + " = " + parseFloat(exchange_rate).toFixed(pricison) + " " + gCurrencyCode);
            return exchange_rate;
        }
        function getLimit() {
            var pricison = isCrypto();
            var exchange_rate = getExchangeRate();
            var wCurrencyCode = acceptVar().wCurrencyCode;
            var wCurrencyRate = acceptVar().wCurrencyRate;
            var min_limit = acceptVar().currencyMinAmount;
            var max_limit =acceptVar().currencyMaxAmount;

            if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
                var min_limit_calc = parseFloat(min_limit/exchange_rate).toFixed(acceptVar().wPrecison);
                var max_limit_clac = parseFloat(max_limit/exchange_rate).toFixed(acceptVar().wPrecison);

                $('.limit-show').html( min_limit_calc + " " + wCurrencyCode + " - " + max_limit_clac + " " + wCurrencyCode);
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
            var pricison = isCrypto();
            var wCurrencyCode = acceptVar().wCurrencyCode;
            var wCurrencyRate = acceptVar().wCurrencyRate;
            var exchange_rate = getExchangeRate();
            var daily_limit = acceptVar().currencyDailyLimit;
            var monthly_limit = acceptVar().currencyMonthlyLimit

            if($.isNumeric(daily_limit) && $.isNumeric(monthly_limit)) {
                if(daily_limit > 0 ){
                    var daily_limit_calc = parseFloat(daily_limit / exchange_rate).toFixed(acceptVar().wPrecison);
                    $('.limit-daily').html( daily_limit_calc + " " + wCurrencyCode);
                }else{
                    $('.limit-daily').html("{{ __('unlimited') }}");
                }

                if(monthly_limit > 0 ){
                    var montly_limit_clac = parseFloat(monthly_limit / exchange_rate).toFixed(acceptVar().wPrecison);
                    $('.limit-monthly').html( montly_limit_clac + " " + wCurrencyCode);

                }else{
                    $('.limit-monthly').html("{{ __('unlimited') }}");
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
            var pricison = isCrypto();
            var exchange_rate = getExchangeRate();
            var sender_amount = $("input[name=amount]").val();
            sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

            var fixed_charge = acceptVar().currencyFixedCharge;
            var percent_charge = acceptVar().currencyPercentCharge;

            if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge)) {
                // Process Calculation
                var fixed_charge_calc = parseFloat(fixed_charge) / parseFloat(exchange_rate);
                var percent_charge_calc = (parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
                var total_charge = parseFloat(fixed_charge_calc) + parseFloat(percent_charge_calc);
                total_charge = parseFloat(total_charge).toFixed(acceptVar().wPrecison) ;
                // return total_charge;
                return {
                    total: parseFloat(total_charge).toFixed(acceptVar().wPrecison),
                    fixed: parseFloat(fixed_charge_calc).toFixed(acceptVar().wPrecison),
                    percent: parseFloat(percent_charge).toFixed(acceptVar().wPrecison),
                };
            } else {
                // return "--";
                return false;
            }
        }
        function getFees() {
            var pricison = isCrypto();
            var wCurrencyCode = acceptVar().wCurrencyCode;
            var percent = acceptVar().currencyPercentCharge;
            var charges = feesCalculation();
            if (charges == false) {
                return false;
            }
            $(".fees-show").html("{{ __('charge') }}: " + parseFloat(charges.fixed).toFixed(acceptVar().wPrecison) + " " + wCurrencyCode + " + " + parseFloat(charges.percent).toFixed(acceptVar().wPrecison) + "%");
        }
        function activeItems(){
            var selectedVal = acceptVar().selectedVal.val();
            if(selectedVal === undefined || selectedVal === '' || selectedVal === null){
                return false;
            }else{
                return getPreview();
            }
        }
        function getPreview() {
            var pricison = isCrypto();
            var wCurrencyCode = acceptVar().wCurrencyCode;
            var gCurrencyCode = acceptVar().currencyCode;
            var exchange_rate = getExchangeRate();
            var senderAmount = $("input[name=amount]").val();

            if(acceptVar().selectedVal.val() === "null"){
                return false;
            }
            (senderAmount == "" || isNaN(senderAmount)) ? senderAmount = 0 : senderAmount = senderAmount;

            // Sending Amount
            $('.request-amount').text(parseFloat(senderAmount).toFixed(acceptVar().wPrecison)  + " " + wCurrencyCode);

            // Fees
            var charges = feesCalculation();
            var total_charge = 0;
            if(senderAmount == 0){
                total_charge =parseFloat(0).toFixed(acceptVar().wPrecison);
            }else{
                total_charge = parseFloat(charges.total).toFixed(acceptVar().wPrecison);
            }

            $('.fees').text(total_charge + " " + wCurrencyCode);

            var conversionAmount = senderAmount * exchange_rate;
            $('.conversionAmount').text(parseFloat(conversionAmount).toFixed(pricison) + " " + gCurrencyCode);
            // willget
            var will_get = parseFloat(conversionAmount)
            var will_get_total = 0;
            if(senderAmount == 0){
                    will_get_total = 0;
            }else{
                    will_get_total =  parseFloat(will_get);
            }
            $('.will-get').text(parseFloat(will_get_total).toFixed(pricison) + " " + gCurrencyCode);

            // total payable
            var totalPay = parseFloat(senderAmount) + parseFloat( total_charge)
            var pay_in_total = 0;
            if(senderAmount == 0){
                    pay_in_total = 0;
            }else{
                //  pay_in_total =  parseFloat(totalPay) + parseFloat(charges.total);
                    pay_in_total =  parseFloat(totalPay);
            }
            $('.total-pay').text(parseFloat(pay_in_total).toFixed(acceptVar().wPrecison) + " " + wCurrencyCode);
        }
        function walletBalance(){
            var pricison = isCrypto();
            var senderCurrency = acceptVar().wCurrencyCode;
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: 'POST',
                url: "{{ route('user.wallets.balance') }}",
                data: {
                    target: senderCurrency,
                    _token: csrfToken
                },
                success: function(response) {
                    $('.balance-show').html("{{ __('Available Balance') }}: " + parseFloat(response.data).toFixed(acceptVar().wPrecison)  + " " + senderCurrency);
                }
            });
        }
        function get_remaining_limits(){
            var csrfToken           = $('meta[name="csrf-token"]').attr('content');
            var user_field          = "user_id";
            var user_id             = "{{ userGuard()['user']->id }}";
            var transaction_type    = "{{ payment_gateway_const()::TYPEMONEYOUT }}";
            var currency_id         = acceptVar().wCurrencyVal.data('currency-id');
            var sender_amount       = $("input[name=amount]").val();

            (sender_amount == "" || isNaN(sender_amount)) ? sender_amount = 0 : sender_amount = sender_amount;

            var charge_id           = acceptVar().selectedVal.data('currency-id');
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
                    var sender_currency = acceptVar().wCurrencyCode;
                    var daily_limit = acceptVar().currencyDailyLimit;
                    var monthly_limit = acceptVar().currencyMonthlyLimit


                    var status  = response.status;
                    var message = response.message;
                    var amount_data = response.data;

                    if(status == false){
                        $('.withdrawBtn').attr('disabled',true);
                        if(daily_limit > 0){
                            $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                            throwMessage('error',[message]);
                            return false;
                        }else{
                            $('.daily-remaining').html("{{ __('unlimited') }}");
                        }
                        if(monthly_limit > 0){
                            $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                            throwMessage('error',[message]);
                            return false;
                        }else{
                            $('.monthly-remaining').html("{{ __('unlimited') }}");
                        }

                    }else{
                        $('.withdrawBtn').attr('disabled',false);
                        if(daily_limit > 0){
                            $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                        }else{
                            $('.daily-remaining').html("{{ __('unlimited') }}");
                        }
                        if(monthly_limit > 0){
                            $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                        }else{
                            $('.monthly-remaining').html("{{ __('unlimited') }}");
                        }
                    }
                },
            });
        }
    </script>
@endpush
