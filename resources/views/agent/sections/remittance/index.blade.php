@extends('agent.layouts.master')
@php
    $token = (object)session()->get('sender_remittance_token');
    $sender_token = session()->get('sender_remittance_token');

    $rtoken = (object)session()->get('receiver_remittance_token');
    $receiver_token = session()->get('receiver_remittance_token');

@endphp
@php
$siteWallet = str_replace(' ','_',$basic_settings->site_name)."_Wallet";
@endphp

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="row mb-30-none">
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __(@$page_title) }} {{ __("Form") }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute('agent.remittance.confirmed') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center">
                                            <span>{{ __("Exchange Rate") }} <span class="rate-show">--</span></span>
                                        </code>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("From Country") }} <span class="text--base">*</span></label>
                                    <select class="form--control select2-auto-tokenize"  name="form_country" required data-minimum-results-for-search="Infinity">
                                        @foreach ($senderCountries as $country)
                                            <option value="{{ $country->id }}"
                                                data-code="{{ $country->code }}"
                                                data-symbol="{{ $country->symbol }}"
                                                data-rate="{{ $country->rate }}"
                                                data-type="{{ $country->type }}"
                                                data-name="{{ $country->name }}"
                                                >{{ $country->name }} ({{ $country->code }})</option>
                                        @endforeach
                                    </select>

                                </div>
                               @if($receiver_token)
                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("To Country") }}<span class="text--base">*</span></label>
                                    <select name="to_country" class="form--control select2-basic" required data-placeholder="Select To Country" >
                                        {{-- <option disabled selected value="">Select To Country</option> --}}
                                    @foreach ($receiverCountries as $country)
                                        <option value="{{ $country->id }}" {{ @$rtoken->receiver_country ==  $country->id ? 'selected':''}}
                                            data-code="{{ $country->code }}"
                                            data-symbol="{{ $country->symbol }}"
                                            data-rate="{{ $country->rate }}"
                                            data-type="{{ $country->type }}"
                                            data-name="{{ $country->name }}"
                                            >{{ $country->name }} ({{ $country->code }})</option>
                                    @endforeach

                                    </select>
                                </div>
                                @else
                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("To Country") }}<span class="text--base">*</span></label>
                                    <select name="to_country" class="form--control select2-basic" required data-placeholder="Select To Country" >
                                        {{-- <option disabled selected value="">Select To Country</option> --}}
                                        @foreach ($receiverCountries as $country)
                                        <option value="{{ $country->id }}"
                                            data-code="{{ $country->code }}"
                                            data-symbol="{{ $country->symbol }}"
                                            data-rate="{{ $country->rate }}"
                                            data-type="{{ $country->type }}"
                                            data-name="{{ $country->name }}"
                                            >{{ $country->name }} ({{ $country->code }})</option>
                                    @endforeach
                                    </select>
                                </div>
                                @endif
                                @if($sender_token)
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <label>{{ __("Transaction Type") }}<span>*</span></label>
                                    <select  name="transaction_type" required  class="form--control select2-auto-tokenize" data-placeholder="Select Transaction Type" data-minimum-results-for-search="Infinity">
                                        {{-- <option disabled selected value="">{{ __("Select Transaction Type") }}</option> --}}
                                        <option value="bank-transfer" {{ @$token->transacion_type == 'bank-transfer' ? 'selected':''}} data-name="Bank Transfer">{{__("Bank Transfer")}}</option>
                                        <option value="wallet-to-wallet-transfer" {{ @$token->transacion_type == 'wallet-to-wallet-transfer' ? 'selected':''}} data-name="wallet-to-wallet-transfer">{{ @$basic_settings->site_name }} {{__("Wallet")}}</option>
                                        <option value="cash-pickup" {{ @$token->transacion_type ==  'cash-pickup' ? 'selected':''}} data-name="Cash Pickup">{{__("Cash Pickup")}}</option>

                                </select>
                                </div>
                                @elseif($receiver_token)
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <label>{{ __("Transaction Type") }}<span>*</span></label>
                                    <select  name="transaction_type" required  class="form--control select2-auto-tokenize" data-placeholder="Select Transaction Type" data-minimum-results-for-search="Infinity">
                                        {{-- <option disabled selected value="">{{ __("Select Transaction Type") }}</option> --}}
                                        <option value="bank-transfer" {{ @$rtoken->transacion_type == 'bank-transfer' ? 'selected':''}} data-name="Bank Transfer">{{__("Bank Transfer")}}</option>
                                        <option value="wallet-to-wallet-transfer" {{ @$token->transacion_type == 'wallet-to-wallet-transfer' ? 'selected':''}} data-name="wallet-to-wallet-transfer">{{ @$basic_settings->site_name }} {{__("Wallet")}}</option>
                                        <option value="cash-pickup" {{ @$token->transacion_type ==  'cash-pickup' ? 'selected':''}} data-name="Cash Pickup">{{__("Cash Pickup")}}</option>

                                </select>
                                </div>
                                @else
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <label>{{ __("Transaction Type") }}<span>*</span></label>
                                    <select  name="transaction_type" required  class="form--control select2-auto-tokenize" data-placeholder="Select Transaction Type" data-minimum-results-for-search="Infinity">
                                        {{-- <option disabled selected value="">{{ __("Select Transaction Type") }}</option> --}}
                                        <option value="bank-transfer"  data-name="Bank Transfer">{{__("Bank Transfer")}}</option>
                                        <option value="wallet-to-wallet-transfer" data-name="wallet-to-wallet-transfer">{{ @$basic_settings->site_name }} {{__("Wallet")}}</option>
                                        <option value="cash-pickup"  data-name="Cash Pickup">{{__("Cash Pickup")}}</option>

                                </select>
                                </div>
                                @endif
                                <div class="col-xl-10 col-lg-10 form-group">
                                    <label>{{__("Sender Recipient")}} <span class="text--base">*</span></label>
                                    <select name="sender_recipient" class="form--control  select2-basic  recipient" required data-placeholder="{{ __("Select Sender Recipient") }}" >
                                    </select>
                                </div>
                                <div class="col-xl-2 col-lg-2 form-group mt-4">
                                    <div class="remittance-add-btn-area mt-2">
                                        <a href="javascript:void(0)" class="btn--base w-100 add-recipient">{{ __("Add") }} <i class="fas fa-plus-circle ms-1"></i></a>
                                    </div>
                                </div>
                                <div class="col-xl-10 col-lg-10 form-group">
                                    <label>{{__("Receiver Recipient")}} <span class="text--base">*</span></label>
                                    <select name="receiver_recipient" class="form--control  select2-basic  receiver_recipient" required data-placeholder="{{ __("Select Receiver Recipient") }}" >
                                    </select>
                                </div>
                                <div class="col-xl-2 col-lg-2 form-group mt-4">
                                    <div class="remittance-add-btn-area mt-2">
                                        <a href="javascript:void(0)" class="btn--base w-100 add-recipient-receiver">{{ __("Add") }} <i class="fas fa-plus-circle ms-1"></i></a>
                                    </div>
                                </div>

                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("sending Amount") }} <span class="text--base">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="send_amount" class="form--control number-input" placeholder="{{__('enter Amount')}}" value="{{ old('send_amount') }}" >
                                        <div class="input-group-append">
                                            <span class="input-group-text copytext sender_curr_code">{{ get_default_currency_code() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("recipient Amount") }} <span class="text--base">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="receive_amount" class="form--control number-input" placeholder="{{__('enter Amount')}}" value="{{ old('receive_amount') }}" >
                                        <div class="input-group-append">
                                            <span class="input-group-text reciver_curr_code">{{ get_default_currency_code() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <div class="note-area">
                                        <code class="d-block balance-show">{{ __("Available Balance") }}: {{ authWalletBalance() }} {{ get_default_currency_code() }}</code>
                                        <code class="d-block fees-show">--</code>
                                    </div>
                                </div>

                                <div class="withdraw-btn mt-20">
                                    <button type="submit" class="btn--base w-100 btn-loading confirmed">{{ __("Send Now") }} <i class="fas fa-paper-plane ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
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
                            @if ($exchangeCharge->daily_limit > 0)
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
                            @if ($exchangeCharge->monthly_limit > 0)
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
                                            <i class="las la-flag"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("sending Country") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="sender-county">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="lab la-font-awesome-flag"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Receiving Country") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="receiver-county">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-user-tag"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Sender Recipient") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="recipient-name">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-user-tag"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Receiver Recipient") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="receiver-recipient-name">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-cash-register"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Transaction Type") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="trans-type">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-paper-plane"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("sending Amount") }}</span>
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
                                            <i class="las la-arrow-right"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Transfer Fee") }}</span>
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
                                            <span>{{ __("recipient Get") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--base fw-bold recipient-amount">--</span>
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
                                    <span class="text--base last payable-amount">--</span>
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
            <h4 class="title">{{ __("Remittance Log") }}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('agent.transactions.index','remittance') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('agent.components.transaction-log',compact("transactions"))
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    var defualCurrency = "{{ get_default_currency_code() }}";
    var defualCurrencyRate = "{{ get_default_currency_rate() }}";
    var senderCountry = "{{ get_default_currency_name() }}";
    var walletTransactionName ="{{ @$basic_settings->site_name }}" +' '+ 'Wallet';
    var selectedRecipientByToken = "{{ @$token->sender_recipient }}"
    var selectedRecipientByTokenReceiver = "{{ @$rtoken->receiver_recipient }}"



   $(document).ready(function(){
        checkReciverCountry();
        senderBalance();
        recipientFilterByCountry();
        recipientFilterByTransactionType();
        recipientFilterByCountryReceiver();
        recipientFilterByTransactionTypeReceiver();
        set_receiver_currency_code();
        getLimit();
        getDailyMonthlyLimit();
        get_remaining_limits();
        getFees();
        getExchangeRate();
        getPreview();


    });

    $("select[name=form_country]").change(function(){
        checkReciverCountry();
        senderBalance();
        recipientFilterByCountry();
        recipientFilterByTransactionType();
        recipientFilterByCountryReceiver();
        recipientFilterByTransactionTypeReceiver();
        set_sender_currency_code();
        set_receiver_currency_code();
        getLimit();
        getDailyMonthlyLimit();
        get_remaining_limits();
        getFees();
        getExchangeRate();
        getReceiverAmount();
        getSenderAmount();
        getPreview();
    });
    $("select[name=to_country]").change(function(){
        checkReciverCountry();
        recipientFilterByCountryReceiver();
        set_sender_currency_code();
        set_receiver_currency_code();
        getLimit();
        getDailyMonthlyLimit();
        getFees();
        getExchangeRate();
        getReceiverAmount();
        getSenderAmount();
        getPreview();

    });
    $("select[name=transaction_type]").change(function(){
        checkReciverCountry();
        recipientFilterByTransactionType();
        recipientFilterByTransactionTypeReceiver();
        set_receiver_currency_code();
        getLimit();
        getDailyMonthlyLimit();
        getFees();
        getExchangeRate();
        getPreview();

    });
    $("select[name=sender_recipient]").change(function(){
        checkReciverCountry();
        set_receiver_currency_code();
        getLimit();
        getDailyMonthlyLimit();
        getFees();
        getExchangeRate();
        getPreview();
    });
    $("select[name=receiver_recipient]").change(function(){
        checkReciverCountry();
        set_receiver_currency_code();
        getLimit();
        getDailyMonthlyLimit();
        getFees();
        getExchangeRate();
        getPreview();
    });
    $("input[name=send_amount]").keyup(function(){
        getFees();
        getReceiverAmount();
        getPreview();

    });
    $("input[name=receive_amount]").keyup(function(){
        getSenderAmount();
        getFees();
        getPreview();
    });
    $("input[name=send_amount]").focusout(function(){
        enterLimit();
    });
    $("input[name=receive_amount]").focusout(function(){
        enterLimit();
    });



    function acceptVar() {
        var senderCurrencyVal       = $("select[name=form_country] :selected");
        var sender_country          = $("select[name=form_country] :selected").val();
        var sender_country_name     = $("select[name=form_country] :selected").data('name');
        var sender_currency         = $("select[name=form_country] :selected").data('code');
        var sender_currency_rate    = $("select[name=form_country] :selected").data('rate');
        var senderCurrencyType      = $("select[name=form_country] :selected").data('type');

        var receiverCurrencyVal     = $("select[name=to_country] :selected");
        var receiver_conctry = $("select[name=to_country] :selected").val();
        var receiver_conctry_name = $("select[name=to_country] :selected").data('name');
        var receiverCurrency = $("select[name=to_country] :selected").data('code');
        var receiverCurrency_rate = $("select[name=to_country] :selected").data('rate');
        var receiverCurrencyType     = $("select[name=to_country] :selected").data('type');


        var tranaction_type         = $("select[name=transaction_type] :selected").val();
        var tranaction_name         = $("select[name=transaction_type] :selected").data('name');
        var currencyMinAmount       ="{{getAmount($exchangeCharge->min_limit)}}";
        var currencyMaxAmount       = "{{getAmount($exchangeCharge->max_limit)}}";
        var currencyFixedCharge     = "{{getAmount($exchangeCharge->fixed_charge)}}";
        var currencyPercentCharge   = "{{getAmount($exchangeCharge->percent_charge)}}";
        var currencyDailyLimit      = "{{getAmount($exchangeCharge->daily_limit)}}";
        var currencyMonthlyLimit    = "{{getAmount($exchangeCharge->monthly_limit)}}";
        var recipient               = $("select[name=sender_recipient] :selected").val();
        var recipientName           = $("select[name=sender_recipient] :selected").data('name');
        var receiver_recipient      = $("select[name=receiver_recipient] :selected").val();
        var receiver_recipientName  = $("select[name=receiver_recipient] :selected").data('name');

        if(senderCurrencyType == "CRYPTO"){
            var senderPrecison = "{{ get_precision_from_admin()['crypto_precision_value'] }}";
        }else{
            var senderPrecison = "{{  get_precision_from_admin()['fiat_precision_value'] }}";
        }
        if(receiverCurrencyType == "CRYPTO"){
            var receiverPrecison = "{{ get_precision_from_admin()['crypto_precision_value'] }}";
        }else{
            var receiverPrecison = "{{  get_precision_from_admin()['fiat_precision_value'] }}";
        }
        return {
            sCurrencyVal:senderCurrencyVal,
            sCountry:sender_country,
            sCountryName:sender_country_name,
            sCurrency:sender_currency,
            sCurrency_rate:sender_currency_rate,
            sPrecison:senderPrecison,

            receiverCurrencyVal:receiverCurrencyVal,
            receiver_conctry:receiver_conctry,
            receiver_conctry_name:receiver_conctry_name,
            receiverCurrency:receiverCurrency,
            receiverCurrency_rate:receiverCurrency_rate,
            rPrecison:receiverPrecison,

            tranaction_type:tranaction_type,
            tranaction_name:tranaction_name,
            currencyMinAmount:currencyMinAmount,
            currencyMaxAmount:currencyMaxAmount,
            currencyFixedCharge:currencyFixedCharge,
            currencyPercentCharge:currencyPercentCharge,
            currencyDailyLimit:currencyDailyLimit,
            currencyMonthlyLimit:currencyMonthlyLimit,
            recipient:recipient,
            recipientName:recipientName,
            receiver_recipient:receiver_recipient,
            receiver_recipientName:receiver_recipientName,
        };
    }
    function checkReciverCountry(){
        var from_country = acceptVar().sCountry;
        var to_country   = acceptVar().receiver_conctry;
        if(from_country == to_country){
            throwMessage('error',['{{ __("Remittances cannot be sent within the same country") }}']);
            $('.confirmed').attr('disabled',true);
            return false;
        }else{
            $('.confirmed').attr('disabled',false);
        }
    }
    function set_receiver_currency_code(){
        var receiverCurrency = acceptVar().receiverCurrency;
        $('.reciver_curr_code').text(receiverCurrency);
    }
    function set_sender_currency_code(){
        var senderCurrency = acceptVar().sCurrency;
        $('.sender_curr_code').text(senderCurrency);
    }

    function getLimit() {
        var sender_currency = acceptVar().sCurrency;
        var sender_currency_rate = acceptVar().sCurrency_rate;
        var min_limit = acceptVar().currencyMinAmount;
        var max_limit =acceptVar().currencyMaxAmount;
        if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
            var min_limit_calc = parseFloat(min_limit*sender_currency_rate).toFixed(acceptVar().sPrecison);
            var max_limit_clac = parseFloat(max_limit*sender_currency_rate).toFixed(acceptVar().sPrecison);
            $('.limit-show').html(min_limit_calc + " " + sender_currency + " - " + max_limit_clac + " " + sender_currency);
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
        var sender_currency = acceptVar().sCurrency;
        var sender_currency_rate = acceptVar().sCurrency_rate;
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
    function getExchangeRate() {
        var sender_currency         = acceptVar().sCurrency;
        var sender_currency_rate    = acceptVar().sCurrency_rate;
        var receiver_currency       = acceptVar().receiverCurrency;
        var receiver_currency_rate  = acceptVar().receiverCurrency_rate;
        var rate = parseFloat(receiver_currency_rate) / parseFloat(sender_currency_rate);
        $('.rate-show').html("1 " + sender_currency + " = " + parseFloat(rate).toFixed(acceptVar().rPrecison) + " " + receiver_currency);

        return rate;
    }
    function feesCalculation() {
        var sender_currency = acceptVar().sCurrency;
        var sender_currency_rate = acceptVar().sCurrency_rate;
        var sender_amount = $("input[name=send_amount]").val();
        sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

        var fixed_charge = acceptVar().currencyFixedCharge;
        var percent_charge = acceptVar().currencyPercentCharge;
        if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
            // Process Calculation
            var fixed_charge_calc = parseFloat(sender_currency_rate * fixed_charge);
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
        var sender_currency = acceptVar().sCurrency;
        var percent = acceptVar().currencyPercentCharge;
        var charges = feesCalculation();
        if (charges == false) {
            return false;
        }
        $(".fees-show").html("{{ __('charge') }}: " + parseFloat(charges.fixed).toFixed(acceptVar().sPrecison) + " " + sender_currency + " + " + parseFloat(charges.percent).toFixed(acceptVar().sPrecison) + "%  ");
    }



    function getSenderAmount() {
        var sender_currency = acceptVar().sCurrency;
        var sender_currency_rate = acceptVar().sCurrency_rate;
        var receiver_currency = acceptVar().receiverCurrency;
        var receiver_currency_rate = acceptVar().receiverCurrency_rate;
        var sender_amount = $("input[name=send_amount]");
        var receiver_amount = $("input[name=receive_amount]").val();
        if($.isNumeric(receiver_amount)) {
            var rate = parseFloat(sender_currency_rate) / parseFloat(receiver_currency_rate);
            var sender_will_get = parseFloat(rate) * parseFloat(receiver_amount);
            sender_will_get = parseFloat(sender_will_get).toFixed(acceptVar().sPrecison);
            sender_amount.val(sender_will_get);
            preview_receiver_will_get = parseFloat(receiver_amount).toFixed(acceptVar().sPrecison);
        }else {
            sender_amount.val("");
            preview_receiver_will_get = "0";
        }
    }
    function getReceiverAmount() {
            var sender_currency = acceptVar().sCurrency;
        var sender_currency_rate = acceptVar().sCurrency_rate;
        var receiver_currency = acceptVar().receiverCurrency;
        var receiver_currency_rate = acceptVar().receiverCurrency_rate;
        var sender_amount = $("input[name=send_amount]").val();
        var receiver_amount = $("input[name=receive_amount]");
        if($.isNumeric(sender_amount)) {
            var rate = parseFloat(receiver_currency_rate) / parseFloat(sender_currency_rate);
            var receiver_will_get = parseFloat(rate) * parseFloat(sender_amount);
            receiver_will_get = parseFloat(receiver_will_get).toFixed(acceptVar().rPrecison);
            receiver_amount.val(receiver_will_get);
            preview_receiver_will_get = receiver_will_get;
        }else {
            receiver_amount.val("");
            preview_receiver_will_get = "0";
        }
    }

    function  recipientFilterByCountry(){
        var sender_country = acceptVar().sCountry;
        var transacion_type = acceptVar().tranaction_type;
        $(".recipient").html('');
        $.ajax({
                url: "{{route('agent.remittance.get.recipient.country')}}",
                type: "POST",
                data: {
                    sender_country: sender_country,
                    transacion_type: transacion_type,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function (res) {
                    var recipients = res.recipient;
                    if( recipients == ''){
                        $('.recipient').html('<option value="">No Recipient Aviliable</option>');
                    }else{
                        $('.recipient').html('<option value="">Select Recipient</option>');

                    }
                     $.each(res.recipient, function (key, value) {
                            var selected = value.id == selectedRecipientByToken ? 'selected' : '';
                            $(".recipient").append('<option value="' + value.id + '" data-trx-type="' + value.type + '" data-name="' + value.firstname + ' ' + value.lastname +'" ' + selected + ' >' + value.firstname + ' ' + value.lastname + '</option>');
                    });


                }
            });

    }
    function  recipientFilterByTransactionType(){
        var sender_country = acceptVar().sCountry;
        var transacion_type = acceptVar().tranaction_type;

        $(".recipient").html('');
        $.ajax({
                url: "{{route('agent.remittance.get.recipient.transtype')}}",
                type: "POST",
                data: {
                    sender_country: sender_country,
                    transacion_type: transacion_type,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function (res) {
                    var recipients = res.recipient;

                    if( recipients == ''){
                        $('.recipient').html('<option value="">No Recipient Aviliable</option>');
                    }else{
                        $('.recipient').html('<option value="">Select Recipient</option>');

                    }
                    $.each(res.recipient, function (key, value) {
                            var selected = value.id == selectedRecipientByToken ? 'selected' : '';
                            $(".recipient").append('<option value="' + value.id + '" data-trx-type="' + value.type + '" data-name="' + value.firstname + ' ' + value.lastname +'" ' + selected + ' >' + value.firstname + ' ' + value.lastname + '</option>');
                    });

                }
            });

    }
    //receiver filter
    function  recipientFilterByCountryReceiver(){
        var receiver_country = acceptVar().receiver_conctry;
        var transacion_type = acceptVar().tranaction_type;
        $(".receiver_recipient").html('');
        $.ajax({
                url: "{{route('agent.remittance.get.receiver.recipient.country')}}",
                type: "POST",
                data: {
                    receiver_country: receiver_country,
                    transacion_type: transacion_type,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function (res) {
                    var recipients = res.recipient;
                    if( recipients == ''){
                        $('.receiver_recipient').html('<option value="">No Receiver Recipient Aviliable</option>');
                    }else{
                        $('.receiver_recipient').html('<option value="">Select Receiver Recipient</option>');

                    }
                     $.each(res.recipient, function (key, value) {
                            var selected = value.id == selectedRecipientByTokenReceiver ? 'selected' : '';
                            $(".receiver_recipient").append('<option value="' + value.id + '" data-trx-type="' + value.type + '" data-name="' + value.firstname + ' ' + value.lastname +'" ' + selected + ' >' + value.firstname + ' ' + value.lastname + '</option>');
                    });


                }
            });

    }
    function  recipientFilterByTransactionTypeReceiver(){
        var receiver_country = acceptVar().receiver_conctry;
        var transacion_type = acceptVar().tranaction_type;

        $(".receiver_recipient").html('');
        $.ajax({
                url: "{{route('agent.remittance.get.receiver.recipient.transtype')}}",
                type: "POST",
                data: {
                    receiver_country: receiver_country,
                    transacion_type: transacion_type,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function (res) {
                    var recipients = res.recipient;

                    if( recipients == ''){
                        $('.receiver_recipient').html('<option value="">No Receiver Recipient Aviliable</option>');
                    }else{
                        $('.receiver_recipient').html('<option value="">Select Receiver Recipient</option>');

                    }
                    $.each(res.recipient, function (key, value) {
                            var selected = value.id == selectedRecipientByTokenReceiver ? 'selected' : '';
                            $(".receiver_recipient").append('<option value="' + value.id + '" data-trx-type="' + value.type + '" data-name="' + value.firstname + ' ' + value.lastname +'" ' + selected + ' >' + value.firstname + ' ' + value.lastname + '</option>');
                    });

                }
            });

    }

    function getPreview() {
        var senderAmount = $("input[name=send_amount]").val();
        var sender_currency = acceptVar().sCurrency;
        var sender_currency_rate = acceptVar().sCurrency_rate;
        var sender_country = acceptVar().sCountryName;

        var receiveAmount = $("input[name=receive_amount]").val();
        var receiverCurrency = acceptVar().receiverCurrency;
        var receiverCurrency_rate = acceptVar().receiverCurrency_rate;
        var receiver_conctry_name = acceptVar().receiver_conctry_name;
        // var sender_country = senderCountry;

        var receipient = acceptVar().recipientName;
        var receiverReceipient = acceptVar().receiver_recipientName;
        var tranaction_name = acceptVar().tranaction_name;


        (senderAmount == "" || isNaN(senderAmount)) ? senderAmount = 0 : senderAmount = senderAmount;
        // Sending Amount
        $('.request-amount').text(parseFloat(senderAmount).toFixed(acceptVar().sPrecison) + " " + sender_currency);
        receiveAmount == "" ? receiveAmount = 0 : receiveAmount = receiveAmount;
        // receiveAmount Amount
        $('.recipient-amount').text(parseFloat(receiveAmount).toFixed(acceptVar().rPrecison) + " " + receiverCurrency);

        $('.sender-county').text(sender_country);
        $('.receiver-county').text(receiver_conctry_name);
        if(receipient === undefined){
            $('.recipient-name').text("Choose Recipient");
        }else{
            $('.recipient-name').text(receipient);
        }
        if(receiverReceipient === undefined){
            $('.receiver-recipient-name').text("Choose Recipient");
        }else{
            $('.receiver-recipient-name').text(receiverReceipient);
        }
        if(tranaction_name === undefined || tranaction_name === ''){
            $('.trans-type').text("Choose One");
        }else if(tranaction_name == 'wallet-to-wallet-transfer'){
            $('.trans-type').text(walletTransactionName);
        }else{
            $('.trans-type').text(tranaction_name);
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
            pay_in_total =parseFloat(0).toFixed(acceptVar().sPrecison);
        }else{
            pay_in_total =  parseFloat(totalPay) + parseFloat(charges.total);
        }
        $('.payable-amount').text(parseFloat(pay_in_total).toFixed(acceptVar().sPrecison) + " " + sender_currency);

       }
       function enterLimit(){
            var sender_currency_rate = acceptVar().sCurrency_rate;
            var min_limit = parseFloat("{{getAmount($exchangeCharge->min_limit)}}") * parseFloat(sender_currency_rate);
            var max_limit =parseFloat("{{getAmount($exchangeCharge->max_limit)}}") * parseFloat(sender_currency_rate);
            var sender_amount = parseFloat($("input[name=send_amount]").val());
            if( sender_amount < min_limit ){
                throwMessage('error',['{{ __("Please follow the mimimum limit") }}']);
                $('.confirmed').attr('disabled',true)
            }else if(sender_amount > max_limit){
                throwMessage('error',['{{ __("Please follow the maximum limit") }}']);
                $('.confirmed').attr('disabled',true)
            }else{
                $('.confirmed').attr('disabled',false)
            }

       }

    $(".add-recipient").click(function(){
        var receiver_country = acceptVar().receiver_conctry;
        var transacion_type = acceptVar().tranaction_type;
        var recipient = acceptVar().recipientName;
        var receiver_recipient = acceptVar().receiver_recipientName;
        if ( recipient === '' || recipient === undefined || receiver_recipient === '' || receiver_recipient === undefined) {
            recipient = '';
            receiver_recipient = '';
        }
        var sender_amount = $("input[name=send_amount]").val();
        var receive_amount = $("input[name=receive_amount]").val();

        $.ajax({
                url: "{{route('agent.remittance.get.token.sender')}}",
                type: "POST",
                data: {
                    receiver_country: receiver_country,
                    transacion_type: transacion_type,
                    sender_recipient: recipient,
                    receiver_recipient: receiver_recipient,
                    sender_amount: sender_amount,
                    receive_amount: receive_amount,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function (res) {
                    setTimeout(function () {
                    window.location="{{ setRoute('agent.sender.recipient.index') }}";
                }, 500);

                }
        });

    });
    $(".add-recipient-receiver").click(function(){
        var receiver_country = acceptVar().receiver_conctry;
        var transacion_type = acceptVar().tranaction_type;
        var recipient = acceptVar().recipientName;
        var receiver_recipient = acceptVar().receiver_recipientName;
        if ( recipient === '' || recipient === undefined || receiver_recipient === '' || receiver_recipient === undefined) {
            recipient = '';
            receiver_recipient = '';
        }
        var sender_amount = $("input[name=send_amount]").val();
        var receive_amount = $("input[name=receive_amount]").val();

        $.ajax({
                url: "{{route('agent.remittance.get.token.receiver')}}",
                type: "POST",
                data: {
                    receiver_country: receiver_country,
                    transacion_type: transacion_type,
                    sender_recipient: recipient,
                    receiver_recipient: receiver_recipient,
                    sender_amount: sender_amount,
                    receive_amount: receive_amount,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function (res) {
                    setTimeout(function () {
                    window.location="{{ setRoute('agent.receiver.recipient.index') }}";
                }, 500);

                }
        });

    });
    //sender wallet balance
    function senderBalance() {
        var senderCurrency = acceptVar().sCurrency;
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
    function get_remaining_limits(){
        var csrfToken           = $('meta[name="csrf-token"]').attr('content');
        var user_field          = "agent_id";
        var user_id             = "{{ userGuard()['user']->id }}";
        var transaction_type    = "{{ payment_gateway_const()::SENDREMITTANCE }}";
        var currency_id         = acceptVar().sCountry;
        var sender_amount       = $("input[name=send_amount]").val();

        (sender_amount == "" || isNaN(sender_amount)) ? sender_amount = 0 : sender_amount = sender_amount;

        var charge_id           = "{{ $exchangeCharge->id }}";
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
                var sender_currency = acceptVar().sCurrency;

                var status  = response.status;
                var message = response.message;
                var amount_data = response.data;

                if(status == false){
                    $('.confirmed').attr('disabled',true);
                    $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                    $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                    throwMessage('error',[message]);
                    return false;
                }else{
                    $('.confirmed').attr('disabled',false);
                    $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                    $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                }
            },
        });
    }

</script>

@endpush
