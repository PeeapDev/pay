@extends('agent.layouts.master')

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
                        <form class="card-form" action="{{ setRoute('agent.mobile.topup.automatic.pay') }}" method="POST">
                            @csrf
                            <input type="hidden" name="country_code">
                            <input type="hidden" name="phone_code">
                            <input type="hidden" name="exchange_rate">
                            <input type="hidden" name="operator">
                            <input type="hidden" name="operator_id">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center">
                                            <span>{{ __("Exchange Rate") }} <span class="rate-show">--</span></span>
                                        </code>
                                    </div>
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <label>{{ __("Mobile Number") }}<span>*</span></label>
                                    <div class="input-group mobile-code">
                                        <select class="form--control nice-select" name="mobile_code">
                                            @foreach(freedom_countries(global_const()::USER) ?? [] as $key => $code)
                                                <option value="{{ $code->iso2 }}"
                                                    data-mobile-code="{{ remove_speacial_char($code->mobile_code) }}"
                                                    {{ $code->name === auth()->user()->address->country ? 'selected' :'' }}
                                                    >
                                                    {{ $code->name." (+".remove_speacial_char($code->mobile_code).")" }}
                                                </option>
                                            @endforeach

                                        </select>
                                        <input type="text" class="form--control number-input" name="mobile_number" placeholder="{{ __("enter Mobile Number") }}" value="{{ old('mobile_number') }}">
                                        <span class="btn-ring-input"></span>
                                    </div>

                                </div>
                                <div  class="add_item">

                                </div>
                                <div  class="add_item_fixed_wallet">

                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <div class="note-area">
                                        <code class="d-block fw-bold balance-show">--</code>
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
                                            <i class="las la-plug"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Operator Name") }}</span>
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
                                            <i class="las la-hand-holding-usd"></i>
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
                                            <i class="las la-hand-holding-usd"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Conversion Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--info conversion-amount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hand-holding-usd"></i>
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
                                            <i class="las la-hand-holding-usd"></i>
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
    var sender_wallets = {!! $sender_wallets !!};


    $('.mobileTopupBtn').attr('disabled',true);
    $("select[name=mobile_code]").change(function(){
        if(acceptVar().mobileNumber != '' ){
            checkOperator();
        }
    });
    $("input[name=mobile_number]").focusout(function(){
        checkOperator();
    });
    $(document).on("click",".radio_amount",function(){
        preview();
        get_remaining_limits();
    });
    $(document).on("focusout","input[name=amount]",function(){
        var operator =  JSON.parse($("input[name=operator]").val());
        var denominationType = operator.denominationType;
        if(denominationType === "RANGE"){
            enterLimit();
        }
        preview();
    });
    $(document).on("keyup","input[name=amount]",function(){
        preview();
        get_remaining_limits();
    });
    $(document).on("change","select[name=currency]",function(){
        var operator =  JSON.parse($("input[name=operator]").val());
        var denominationType = operator.denominationType;
        senderBalance();
        getExchangeRate();
        if(denominationType === "RANGE"){
            getLimit();
            getDailyMonthlyLimit();
            get_remaining_limits();
        }
    });
    function acceptVar() {
        var selectedMobileCode = $("select[name=mobile_code] :selected");
        var mobileNumber = $("input[name=mobile_number]").val();
        var currencyCode = defualCurrency;
        var currencyRate = defualCurrencyRate;

        var senderCurrencyVal       = $("select[name=currency] :selected");
        var senderCurrencyCode      = $("select[name=currency] :selected").val();
        var senderCurrencyRate      = $("select[name=currency] :selected").data('rate');
        var senderCurrencyType      = $("select[name=currency] :selected").data('type');

        var currencyMinAmount       ="{{getAmount($topupCharge->min_limit)}}";
        var currencyMaxAmount       = "{{getAmount($topupCharge->max_limit)}}";
        var currencyFixedCharge     = "{{getAmount($topupCharge->fixed_charge)}}";
        var currencyPercentCharge   = "{{getAmount($topupCharge->percent_charge)}}";
        var currencyDailyLimit      = "{{getAmount($topupCharge->daily_limit)}}";
        var currencyMonthlyLimit      = "{{getAmount($topupCharge->monthly_limit)}}";

        if(senderCurrencyType == "CRYPTO"){
            var senderPrecison = "{{ get_precision_from_admin()['crypto_precision_value'] }}";
        }else{
            var senderPrecison = "{{  get_precision_from_admin()['fiat_precision_value'] }}";
        }

        return {
            selectedMobileCode:selectedMobileCode,
            mobileNumber:mobileNumber,

            currencyCode:currencyCode,
            currencyRate:currencyRate,

            sCurrencyVal:senderCurrencyVal,
            sCurrencyCode:senderCurrencyCode,
            sCurrencyRate:senderCurrencyRate,
            sPrecison:senderPrecison,

            currencyMinAmount:currencyMinAmount,
            currencyMaxAmount:currencyMaxAmount,
            currencyFixedCharge:currencyFixedCharge,
            currencyPercentCharge:currencyPercentCharge,
            currencyDailyLimit:currencyDailyLimit,
            currencyMonthlyLimit:currencyMonthlyLimit,

        };
    }
    function checkOperator() {
        var url = '{{ route('agent.mobile.topup.automatic.check.operator') }}';
        var mobile_code = acceptVar().selectedMobileCode.data('mobile-code');
        var phone = acceptVar().mobileNumber;
        var iso = acceptVar().selectedMobileCode.val();
        var token = '{{ csrf_token() }}';

        var data = {_token: token, mobile_code: mobile_code, phone: phone, iso: iso};

        $.post(url, data, function(response) {
            $('.btn-ring-input').show();
            if(response.status === true){
                var response_data = response.data;
                // Set operator value
                $("input[name=operator]").val(JSON.stringify(response_data));

                var destination_currency_code = response_data.destinationCurrencyCode;
                var destination_currency_symbol = response_data.destinationCurrencySymbol;
                var denominationType = response_data.denominationType;
                var destination_exchange_rate = response_data.fx.rate;
                $('.add_item').empty();
                // $('.add_item_fixed_wallet').empty();
                $('.limit-show').empty();

                 // Append the HTML code to the .add_item div for RANGE
                 var options = '';
                    sender_wallets.forEach(function(wallet) {
                        options += `<option value="${wallet.code}"
                                            data-rate="${wallet.rate}"
                                            data-type="${wallet.type}"
                                            data-currency-id="${wallet.id}"
                                            data-sender-country-name="${wallet.name}"
                                            ${wallet.code === destination_currency_code ? 'selected' : ''}
                                        >
                                        ${wallet.code}
                                </option>`;
                    });
                    $('.add_item').find('.currency').html(options);

                if(denominationType === "RANGE"){
                    $('.add_item_fixed_wallet').html('');
                    var minAmount = 0;
                    var maxAmount = 0;
                    var senderCurrencyCode = response_data.senderCurrencyCode;
                    var supportsLocalAmounts = response_data.supportsLocalAmounts;
                    if(supportsLocalAmounts == true && destination_currency_code == senderCurrencyCode && response_data.localMinAmount == null && response_data.localMaxAmount == null){
                        minAmount = parseFloat(response_data.minAmount).toFixed(acceptVar().sPrecison);
                        maxAmount = parseFloat(response_data.maxAmount).toFixed(acceptVar().sPrecison);
                    }else if(supportsLocalAmounts == true && response_data.localMinAmount != null && response_data.localMaxAmount != null){
                        minAmount = parseFloat(response_data.localMinAmount).toFixed(acceptVar().sPrecison);
                        maxAmount = parseFloat(response_data.localMaxAmount).toFixed(acceptVar().sPrecison);

                    }else{
                        minAmount = parseFloat(response_data.minAmount).toFixed(acceptVar().sPrecison);
                        maxAmount = parseFloat(response_data.maxAmount).toFixed(acceptVar().sPrecison);
                    }

                    $('.add_item').html(`
                        <div class="col-xxl-12 col-xl-12 col-lg-12 form-group">
                            <label>{{ __("Amount") }}<span>*</span></label>
                            <div class="input-group">
                                <input type="text" class="form--control number-input" required placeholder="{{__('enter Amount')}}" name="amount" value="{{ old("amount") }}">
                                <select class="form--control nice-select currency" name="currency">
                                        ${options} <!-- Append the generated options here -->
                                </select>
                            </div>
                        </div>
                    `);
                    $("select[name=currency]").niceSelect();



                    $('.limit-show').html(`
                        <span class="limit-show">{{ __("limit") }}: ${parseFloat(minAmount).toFixed(acceptVar().sPrecison)+" "+destination_currency_code+" - "+parseFloat(maxAmount).toFixed(acceptVar().sPrecison)+" "+destination_currency_code}</span>
                    `);
                      // Call getExchangeRate here for RANGE
                     getExchangeRate();
                    senderBalance();

                }else if(denominationType === "FIXED"){

                    var fixedAmounts = response_data.fixedAmounts;
                    // Multiply each value in fixedAmounts array by destination_exchange_rate
                    var multipliedAmounts = fixedAmounts.map(function(amount) {
                        return (amount * destination_exchange_rate).toFixed(acceptVar().sPrecison); // Set precision to two decimal places
                    });
                    // Generate radio input fields for each multiplied amount
                    var radioInputs = '';
                    $.each(multipliedAmounts, function(index, amount) {
                        // Check the first radio button by default
                        var checked = index === 0 ? 'checked' : '';
                        radioInputs += `
                            <div class="gift-card-radio-item">
                                <input type="radio" id="level-${index}" name="amount" value="${parseFloat(amount).toFixed(acceptVar().sPrecison)}" onclick="handleRadioClick(this)" class="radio_amount" ${checked}>
                                <label for="level-${index}">${parseFloat(amount).toFixed(acceptVar().sPrecison)} ${destination_currency_code}</label>
                            </div>
                        `;

                    });
                    // Append the HTML code to the .add_item div for FIXED with radio input fields
                    $('.add_item').html(`
                        <div class="col-xl-12 mb-20">
                            <label>{{ __("Amount") }}<span>*</span></label>
                            <div class="gift-card-radio-wrapper">
                                ${radioInputs}
                            </div>
                        </div>

                    `);
                    $('.add_item_fixed_wallet').html(`
                         <div class="col-xxl-12 col-xl-12 col-lg-12 form-group">
                            <label>{{ __("Sender Wallets") }}<span>*</span></label>
                                <select class="form--control nice-select currency" name="currency">
                                        ${options} <!-- Append the generated options here -->
                                </select>
                        </div>
                    `);


                    $("select[name=currency]").niceSelect();
                    // Call getExchangeRate here for FIXED
                    getExchangeRate();
                    senderBalance();

                }
                getFee();
                if(denominationType === "FIXED"){
                    var firstRadio = $('input[type="radio"]:first');
                    firstRadio.prop('checked', true);
                    handleRadioClick(firstRadio[0]);
                }

                $('.mobileTopupBtn').attr('disabled',false);
                setTimeout(function() {
                    $('.btn-ring-input').hide();
                },1000);
            }else if(response.status === false && response.from === "error"){
                $('.add_item, .limit-show').empty();
                $('.fees-show, .add_item_fixed_wallet, .balance-show, .rate-show, .topup-type, .mobile-number, .request-amount, .conversion-amount, .fees, .payable-total').html('--');
                $('.add_item_fixed_wallet').html('');
                $('input[name=phone_code], input[name=country_code],input[name=operator],input[name=operator_id],input[name=exchange_rate]').val('');
                $('.mobileTopupBtn').attr('disabled',true);
                setTimeout(function() {
                    $('.btn-ring-input').hide();
                    throwMessage('error',[response.message]);
                },1000);
                return false;
            }
        });

    }
    function feesCalculation() {
        var currencyCode = acceptVar().sCurrencyCode;
        var currencyRate = acceptVar().sCurrencyRate;
        var sender_amount = parseFloat(get_amount());
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
    function getFee(){
        var currencyCode = acceptVar().currencyCode;
        var percent = acceptVar().currencyPercentCharge;
        var charges = feesCalculation();
        if (charges == false) {
            return false;
        }
        $(".fees-show").html("{{ __('TopUp Fee') }}: " + parseFloat(charges.fixed).toFixed(acceptVar().sPrecison) + " " + currencyCode + " + " + parseFloat(charges.percent).toFixed(acceptVar().sPrecison) + "%  ");

    }
    function getExchangeRate(){
            var walletCurrencyCode = acceptVar().sCurrencyCode;
            var walletCurrencyRate = acceptVar().sCurrencyRate;
            var operator =  JSON.parse($("input[name=operator]").val());
            var destination_currency_code = operator.destinationCurrencyCode;
            var denominationType = operator.denominationType;
            $.ajax({
                type:'get',
                    url:"{{ route('global.receiver.wallet.currency') }}",
                    data:{code:destination_currency_code},
                    success:function(data){
                        var receiverCurrencyCode = data.code;
                        var receiverCurrencyRate = data.rate;
                        var exchangeRate = (receiverCurrencyRate/walletCurrencyRate);
                        $("input[name=exchange_rate]").val(exchangeRate);
                        $('.rate-show').html("1 " +walletCurrencyCode + " = " + parseFloat(exchangeRate).toFixed(acceptVar().sPrecison) + " " + destination_currency_code);
                        getFee();

                        if(denominationType === "RANGE"){
                            getLimit();
                            getDailyMonthlyLimit();
                            get_remaining_limits();
                        }
                        if(denominationType === "FIXED"){
                            fixed_reload_amount_section();
                        }
                        preview();

                    }
            });

    }
    function handleRadioClick(radio) {
            if (radio.checked) {
                amount = parseFloat(radio.value);
                $('.mobileTopupBtn').attr('disabled',false);

            }
        }
    function preview(){
        var exchangeRate =  parseFloat($("input[name=exchange_rate]").val());
        var sender_currency = acceptVar().sCurrencyCode;
        var operator =  JSON.parse($("input[name=operator]").val());
        var destination_currency_code = operator.destinationCurrencyCode;

        var senderAmount = parseFloat(get_amount());
        senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;


        var conversion_amount = parseFloat(senderAmount) * parseFloat(exchangeRate);
        var phone_code = acceptVar().selectedMobileCode.data('mobile-code');
        var phone = "+"+phone_code+acceptVar().mobileNumber;
        //fees
        var charges = feesCalculation();
        var total_charge = parseFloat(charges.total);
        var payable = senderAmount + total_charge

        $('.topup-type').text(operator.name);
        $('.mobile-number').text(phone);
        $('.request-amount').text(parseFloat(senderAmount).toFixed(acceptVar().sPrecison) + " " + sender_currency);
        $('.conversion-amount').text(parseFloat(conversion_amount).toFixed(acceptVar().sPrecison) + " " + destination_currency_code);
        $('.fees').text(parseFloat(total_charge).toFixed(acceptVar().sPrecison) + " " + sender_currency);
        $('.payable-total').text(parseFloat(payable).toFixed(acceptVar().sPrecison) + " " + sender_currency);
        //hidden filed fullups
        $('input[name=phone_code]').val(phone_code);
        $('input[name=country_code]').val(acceptVar().selectedMobileCode.val());
        $('input[name=operator_id]').val(operator.operatorId);

    }
    var amount = 0;
    function get_amount(){
        var operator =  JSON.parse($("input[name=operator]").val());
        var denominationType = operator.denominationType;
        if(denominationType === "RANGE"){
            amount =  amount = parseFloat($("input[name=amount]").val());
            if (!($.isNumeric(amount))) {
                amount = 0;
            }else{
                amount = amount;
            }
        }else{
            amount = amount;
        }
        return amount;
    }
    function enterLimit(){
        var operator =  JSON.parse($("input[name=operator]").val());
        var exchangeRate =  parseFloat($("input[name=exchange_rate]").val());

        var minAmount = 0;
        var maxAmount = 0;
        var destination_currency_code = operator.destinationCurrencyCode;
        var senderCurrencyCode = operator.senderCurrencyCode;
        var supportsLocalAmounts = operator.supportsLocalAmounts;

        if(supportsLocalAmounts == true && destination_currency_code == senderCurrencyCode && operator.localMinAmount == null && operator.localMaxAmount == null){
            minAmount = parseFloat(operator.minAmount).toFixed(acceptVar().sPrecison);
            maxAmount = parseFloat(operator.maxAmount).toFixed(acceptVar().sPrecison);
        }else if(supportsLocalAmounts == true && operator.localMinAmount != null && operator.localMaxAmount != null){
            minAmount = parseFloat(operator.localMinAmount).toFixed(acceptVar().sPrecison);
            maxAmount = parseFloat(operator.localMaxAmount).toFixed(acceptVar().sPrecison);

        }else{

            minAmount = parseFloat(operator.minAmount).toFixed(acceptVar().sPrecison);
            maxAmount = parseFloat(operator.maxAmount).toFixed(acceptVar().sPrecison);
        }


        if(supportsLocalAmounts == true){
            var min_limit = parseFloat(minAmount/exchangeRate).toFixed(acceptVar().sPrecison);
            var max_limit = parseFloat(maxAmount/exchangeRate).toFixed(acceptVar().sPrecison);
        }else{
            var fxRate = operator.fx.rate;
            var min_limit = parseFloat((minAmount*fxRate) / exchangeRate).toFixed(acceptVar().sPrecison);
            var max_limit = parseFloat((maxAmount*fxRate) / exchangeRate).toFixed(acceptVar().sPrecison);
        }


        var senderAmount = parseFloat(get_amount());

        senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;

        if( senderAmount < min_limit ){
            throwMessage('error',['{{ __("Please follow the mimimum limit") }}']);
            $('.mobileTopupBtn').attr('disabled',true)
        }else if(senderAmount > max_limit){
            throwMessage('error',['{{ __("Please follow the maximum limit") }}']);
            $('.mobileTopupBtn').attr('disabled',true)
        }else{
            $('.mobileTopupBtn').attr('disabled',false)
        }

    }
    function senderBalance() {
        var senderCurrency = acceptVar().sCurrencyCode;
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            type: 'POST',
            url: "{{ route('agent.wallets.balance') }}",
            data: {
                target: senderCurrency,
                _token: csrfToken
            },
            success: function(response) {
                $('.balance-show').text("Available Balance : " + parseFloat(response.data).toFixed(acceptVar().sPrecison) + " " + senderCurrency);
            }
        });
    }

    function getLimit(){
        var exchangeRate =  parseFloat($("input[name=exchange_rate]").val());
        var walletCurrencyCode = acceptVar().sCurrencyCode;

        var operator =  JSON.parse($("input[name=operator]").val());

        var minAmount = 0;
        var maxAmount = 0;
        var destination_currency_code = operator.destinationCurrencyCode;
        var senderCurrencyCode = operator.senderCurrencyCode;
        var supportsLocalAmounts = operator.supportsLocalAmounts;

        if(supportsLocalAmounts == true && destination_currency_code == senderCurrencyCode && operator.localMinAmount == null && operator.localMaxAmount == null){
            minAmount = parseFloat(operator.minAmount).toFixed(acceptVar().sPrecison);
            maxAmount = parseFloat(operator.maxAmount).toFixed(acceptVar().sPrecison);
        }else if(supportsLocalAmounts == true && operator.localMinAmount != null && operator.localMaxAmount != null){
            minAmount = parseFloat(operator.localMinAmount).toFixed(acceptVar().sPrecison);
            maxAmount = parseFloat(operator.localMaxAmount).toFixed(acceptVar().sPrecison);

        }else{
            minAmount = parseFloat(operator.minAmount).toFixed(acceptVar().sPrecison);
            maxAmount = parseFloat(operator.maxAmount).toFixed(acceptVar().sPrecison);

        }

        if($.isNumeric(minAmount) && $.isNumeric(maxAmount)) {
            if(supportsLocalAmounts == true){
                var min_limit_calc = parseFloat(minAmount/exchangeRate).toFixed(acceptVar().sPrecison);
                var max_limit_clac = parseFloat(maxAmount/exchangeRate).toFixed(acceptVar().sPrecison);
            }else{
                var fxRate = operator.fx.rate;
                var min_limit_calc = parseFloat((minAmount*fxRate) / exchangeRate).toFixed(acceptVar().sPrecison);
                var max_limit_clac = parseFloat((maxAmount*fxRate) / exchangeRate).toFixed(acceptVar().sPrecison);
            }

                $('.limit-show').html(`
                        <span class="limit-show">${min_limit_calc+" "+walletCurrencyCode+" - "+max_limit_clac+" "+walletCurrencyCode}</span>
                    `);
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
        var sender_currency = acceptVar().sCurrencyCode;
        var sender_currency_rate = acceptVar().sCurrencyRate;
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
    function get_remaining_limits(){
        var csrfToken           = $('meta[name="csrf-token"]').attr('content');
        var user_field          = "agent_id";
        var user_id             = "{{ userGuard()['user']->id }}";
        var transaction_type    = "{{ payment_gateway_const()::MOBILETOPUP }}";
        var currency_id         = acceptVar().sCurrencyVal.data('currency-id');
        var sender_amount       = parseFloat(get_amount());

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
                var sender_currency = acceptVar().sCurrencyCode;
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
    function fixed_reload_amount_section(){
        var exchangeRate =  parseFloat($("input[name=exchange_rate]").val());

        var operator =  JSON.parse($("input[name=operator]").val());
        var walletCurrencyCode = acceptVar().sCurrencyCode;
        $('.add_item').empty();
        var fixedAmounts = operator.fixedAmounts;
        var destination_exchange_rate = operator.fx.rate;

        // Multiply each value in fixedAmounts array by destination_exchange_rate
        var multipliedAmounts = fixedAmounts.map(function(amount) {
            return ((amount / exchangeRate) * destination_exchange_rate).toFixed(acceptVar().sPrecison); // Set precision to two decimal places
        });
        // Generate radio input fields for each multiplied amount
        var radioInputs = '';
        $.each(multipliedAmounts, function(index, amount) {
            // Check the first radio button by default
            var checked = index === 0 ? 'checked' : '';
            radioInputs += `
                <div class="gift-card-radio-item">
                    <input type="radio" id="level-${index}" name="amount" value="${parseFloat(amount).toFixed(acceptVar().sPrecison)}" onclick="handleRadioClick(this)" class="radio_amount" ${checked}>
                    <label for="level-${index}">${parseFloat(amount).toFixed(acceptVar().sPrecison)} ${walletCurrencyCode}</label>
                </div>
            `;

        });
        // Append the HTML code to the .add_item div for FIXED with radio input fields
        $('.add_item').html(`
            <div class="col-xl-12 mb-20">
                <label>{{ __("Amount") }}<span>*</span></label>
                <div class="gift-card-radio-wrapper">
                    ${radioInputs}
                </div>
            </div>

        `);

        amount =  amount = parseFloat($("input[name=amount]").val());
        if (!($.isNumeric(amount))) {
            amount = 0;
        }else{
            amount = amount;
        }


    }
</script>

@endpush
