@extends('user.layouts.app')

@section('content')
<div class="bg-white remove-p buy-sell-tab pb-40 shadow" id="crypto_exchange_user">
    <div class="navtab-crypto r-pxy-62 pxy-62 pb-0">
        <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Crypto Exchange') }}</p>
        <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 1 of 3') }}</p>
        <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Setup Currency') }}</p>
        <div class="text-center">{!! svgIcons('stepper_create') !!}</div>
        <p class="mb-0 mobile-font f-14 leading-23 text-gray text-center mt-19 gilroy-medium">{{ __('Exchange crypto manually from the comfort of your home, quickly, safely with minimal fees. Select the wallet & put the amount you want to exchange.') }}</p>
        <div class="cryto-section">
            <form action="{{ route('user_dashboard.crypto_buy_sell.confirm') }}"  method="POST" accept-charset='UTF-8' id="crypto-send-form">
                @csrf
                <input type="hidden" name="from_type" id="from_type" value="{{ isset($transInfo['exchangeType'])  ? $transInfo['exchangeType'] : $exchangeType }}">
                <div class="crypto-exchange-tab">
                    <!-- Nav start -->
                    <nav>
                        <div class="nav-tab-parent d-flex justify-content-center mt-4" styl>
                            <div class="d-flex p-2 border-1p rounded-pill gap-1 bg-white nav-tab-child" id="nav-tab" role="tablist">
                            @if (transactionTypeCheck('crypto_swap'))
                                <a class="tablink-edit text-gray-100 crypto crypto_swap {{ (isset($exchangeType) && $exchangeType == 'crypto_swap' ? 'tabactive' : '') }}" data-type="crypto_swap" href="#">{{ __('Crypto Swap') }}</a>
                            @endif
                            @if (transactionTypeCheck('crypto_buy_sell'))
                                <a class="tablink-edit text-gray-100 crypto crypto_buy {{ (isset($exchangeType) && $exchangeType == 'crypto_buy' ? 'tabactive' : '') }}" data-type="crypto_buy" href="#">{{ __('Crypto Buy') }}</a>
                                <a class="tablink-edit text-gray-100 crypto crypto_sell {{ (isset($exchangeType) && $exchangeType == 'crypto_sell' ? 'tabactive' : '') }}" data-type="crypto_sell" href="#">{{ __('Crypto Sell') }}</a>
                            @endif
                            </div>
                        </div>
                    </nav>
                    <!-- Nav End -->
                    <div class="tab-content">
                        <div class="row mt-8">

                            <!-- Send amount -->
                            <div class="col-8 col-md-8">
                                <div class="label-top label-top-sendget">
                                    <div class="d-flex justify-content-between mb-2 mt-4 r-mt-amount">
                                        <span class="text-gray-100 gilroy-medium f-15">{{ __('You Send') }}</span>
                                    </div>
                                    <input type="text" class="form-control input-form-control apply-bg l-s2" name="send_amount" id="send_amount" value="{{ isset($transInfo['defaultAmnt'])  ? $transInfo['defaultAmnt'] : (isset($min_amount) ? $min_amount : 1.0) }}" required onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnSendInput(this)">
                                    <p class="send_amount custom-error"></p>
                                    <p class="direction custom-error"></p>
                                </div>
                            </div>

                            <!-- Send Currency -->
                            <div class="col-4 col-md-4 col-pl-7">
                                <div class="param-ref sm-send mt-20">
                                    <label class="gilroy-medium text-gray-100 mb-6 f-16 leading-20" for="from_currency"><span>{{ __('Currency') }}</span></label>
                                        <select class="select2" name="from_currency" id="from_currency">
                                            @if(isset($fromCurrencies))
                                                @foreach($fromCurrencies as $exchangeDirection)
                                                    <option value="{{ optional($exchangeDirection->fromCurrency)->id }}" {{ ( $selectedFrom == optional($exchangeDirection->fromCurrency)->id) ? 'selected' : '' }}>{{ optional($exchangeDirection->fromCurrency)->code }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                </div>
                            </div>
                        </div>
                        <!-- Cypto Exchange Fee -->
                        <div class="row cypto-create">

                            <!-- Fee -->
                            <div class="col-8 col-md-8">
                                <div class="fees-rate mt-11">
                                    <p class="mb-0 f-12 gilroy-regular text-dark"><span>{{ __('Fees') }}: </span><span class="exchange_fee"></span></p>
                                    <p class="mb-0 f-12 gilroy-regular text-dark gilroy-medium mt-6"><span>{{ __('Estimated rate') }}: </span><span class="rate"></span></p>
                                </div>
                            </div>
                        </div>
                        <!-- Receive Currency Div -->
                        <div class="row r-mb-proceed">

                            <!-- Receive Amount -->
                            <div class="col-8 col-md-8">
                                <div class="label-top mt-get">
                                    <label class="d-flex justify-content-between mb-2 mt-4 r-mt-amount"><span class="text-gray-100 gilroy-medium f-15">{{ __('You Get') }}</span></label>
                                    <input type="text" class="form-control input-form-control apply-bg l-s2" name="get_amount" id="get_amount" value="{{ isset($transInfo['finalAmount']) ?  formatNumber($transInfo['finalAmount'], $direction->to_currency_id) : 0.1 }}" required onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnReceiveInput(this)">
                                </div>
                            </div>

                            <!-- Receive Currency -->
                            <div class="col-4 col-md-4 col-pl-7">
                                <div class="param-ref sm-get mt-20">
                                    <label class="gilroy-medium text-gray-100 mb-7 f-16 leading-20" for="to_currency">{{ __('Currency') }}</label>
                                    <select class="select2" name="to_currency" id="to_currency">
                                        @if(isset($toCurrencies))
                                            @foreach($toCurrencies as $to_currency)
                                                <option value="{{ $to_currency->id }}" {{ ($selectedTo  == $to_currency->id) ? 'selected' : '' }}>
                                                    {{ $to_currency->code }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-lg btn-primary mt-4" id="crypto_buy_sell_button">
                                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                    <span class="visually-hidden"></span>
                                </div>
                                <span class="px-1" id="rp_text">{{ __('Proceed') }}</span>
                                <span id="rightAngleSvgIcon">{!! svgIcons('right_angle') !!}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @include('user.common.alert')
</div>

@endsection

@push('js')
    <script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>

    @include('common.restrict_character_decimal_point')
    @include('common.restrict_number_to_pref_decimal')

    <script type="text/javascript">
        'use strict';
        var waitingText = "{{ __('Please Wait') }}";
        var LoadingText = "{{ __('Loading...') }}";
        var submitText = "{{ __('Submitting...') }}";
        var requiedText = "{{ __('This field is required.') }}";
        var numberText = "{{ __('Please enter a valid number.') }}";
        var exchangeText = "{{ __('Exchanging...') }}";
        var nextText = "{{ __('Proceed') }}";
        var directionNotAvaillable = "{{ __('Direction not available.') }}";
        var decimalPreferrence = "{{ preference('decimal_format_amount_crypto', 8) }}";
        var directionListUrl = "{{ route('guest.crypto_exchange.direction_list') }}";
        var directionAmountUrl = "{{ route('guest.crypto_exchange.direction_amount') }}";
        var directionTypeUrl = "{{ route('guest.crypto_exchange.direction_type') }}";
        var cryptoBuySellConfirmUrl = "{{ route('user_dashboard.crypto_buy_sell.payment_confirm') }}";
    </script>

    <script src="{{ asset('Modules/CryptoExchange/Resources/assets/js/user_dashboard.min.js') }}"></script>
@endpush
