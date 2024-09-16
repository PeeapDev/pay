@php
    $extensions = json_encode(getFileExtensions(2));
@endphp

@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 pt-62 custom-input-height shadow" id="crypto_exchange_details">
    <p class="mb-0 f-26 gilroy-Semibold text-center">{{ __(ucwords(str_replace('_', ' ', $exchange_type))) }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Confirm :x', ['x' => __(ucwords(str_replace('crypto_', '', $exchange_type)))]) }}</p>
    <div class="text-center">{!! svgIcons('stepper_confirm') !!}</div>

    @php
        $url = ($exchange_type == 'crypto_buy') ? route('user_dashboard.crypto_buy_sell.gateway') : route('user_dashboard.crypto_buy_sell.success');
    @endphp

    @include('user.common.alert')


    <form action="{{ $url }}" method="post" id="crypto_buy_sell_from" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="exchangeType" value="{{ $exchange_type }}" id="exchangeType">

        <!-- Timer -->
        <div class="d-flex justify-content-between mt-32">
            <span class="f-16 gilroy-medium text-gray-100">{{ __('Time Remaining')}} : </span><span class="f-16 gilroy-medium text-danger" id="timer"></span>
        </div>

        <!-- Progressbar -->
        <div class="custom-progress mt-15" id="demo1">
            <div class="progress mx-auto w-100">
                <div class="progress-bar bg-warning" role="progressbar" id="progressBar" style="width: 100%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <!-- Crypto Exchange Details -->
        <div class="mt-32">
            <div class="exchange-send-get d-flex justify-content-between">

                <!-- Crypto Send -->
                <div class="send-left-box w-50">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('You Send') }}</p>

                    <p class="mb-0 f-24 l gilroy-Semibold mt-1"><span class="text-dark">{{  $send_amount }}</span><span class="text-primary"> {{ $from_currency_code }}</span></p>

                    <p class="mb-0 f-12 leading-17 gilroy-medium text-gray mt-1"><span>{{ __('Fees') }} ≈ </span><span>{{ $exchange_fee }} {{ $from_currency_code }}</span></p>

                    <span class="wallet-error"></span>
                </div>

                <!-- Crypto Get -->
                <div class="get-right-box w-50">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-light">{{ __('You Get') }}</p>
                    <p class="mb-0 f-24 l gilroy-Semibold mt-1"><span class="text-white">{{  $get_amount }}</span><span class="text-warning"> {{ $to_currency_code }}</span></p>

                    <p class="mb-0 f-12 leading-17 gilroy-medium text-muted mt-1"><span> {{ $exchange_rate_display }}</span></p>

                    <div class="right-box-icon">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12.5" cy="12.5" r="12.5" fill="white" />
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.5312 17.4705C10.2709 17.2102 10.2709 16.788 10.5312 16.5277L14.0598 12.9991L10.5312 9.47051C10.2708 9.21016 10.2708 8.78805 10.5312 8.5277C10.7915 8.26735 11.2137 8.26735 11.474 8.5277L15.474 12.5277C15.7344 12.788 15.7344 13.2102 15.474 13.4705L11.474 17.4705C11.2137 17.7309 10.7915 17.7309 10.5312 17.4705Z" fill="#6A6B87" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        @if($exchange_type !== 'crypto_buy')

            <!-- Crypto Pay With -->
            <div class="param-ref sm-send mt-20">
                <label class="gilroy-medium text-gray-100 mb-6 f-16 leading-20">{{ __('Send Crypto') }}</label>
                <select class="select2" data-minimum-results-for-search="Infinity" name="pay_with" id="pay_with">
                    <option value="others">{{ __(':x Address', ['x' => $from_currency_code]) }}</option>
                    @if($fromWallet)
                        <option value="wallet">{{ __('Wallet') }} ({{ $fromWallet->balance }} {{ $from_currency_code }})</option>
                    @endif
                </select>
            </div>

            <div id="payment_details">
            <!-- Crypto Pay Amount -->
            <div class="d-flex flex-wrap justify-content-between">
                <div class="left-merchant pt-20p">
                    <p class="mb-0 f-16 gilroy-medium text-gray-100 mt-38">{{ __('Please make payment') }}</p>
                    <p class="mb-0 f-32 l gilroy-Semibold mt-2"><span class="text-dark">{{ $send_amount + $exchange_fee  }}</span><span class="text-primary"> {{ $from_currency_code }}</span></p>
                    <p class="f-16 leading-25 text-gray-100 gilroy-medium mt-6">{{ __('to our merchant address below') }}</p>
                </div>

                <!-- QR Code -->
                <div class="right-qr-code mt-24 user-profile-qr-code">
                    <img class="img-fluid" src="{{ qrGenerate($merchantAddress) }}" alt="image" width="170" height="170">
                    <p class="mb-0 f-12 gilroy-medium text-gray-100 mt-8">{{ __('Scan QR code on your mobile') }}</p>
                </div>
            </div>

            <!-- Merchant Address -->
            <div class="d-flex justify-content-between m-address">
                <p class="mb-0 gilroy-medium text-gray-100 mb-2 mt-12">{{ __('Merchant Address') }}</p>
                <p class="mb-0 gilroy-medium text-gray-100 mb-2 mt-12 copy-parent-div top-0" id="copy-parent-div">{{ __('Copied') }}</p>
            </div>

            <div class="d-flex position-relative copy-div">
                <input class="form-control input-form-control apply-bg" type="text" id="merchantAddress" value="{{ $merchantAddress }}" name="merchant_address" readonly>
                <span id="copyButton" class="flex-shrink-1 b-none copy-btn">{!! svgIcons('copy_bg_icon') !!}</span>
            </div>

            <!-- Payment Details -->
            <div class="label-top mt-20">
                <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Payment Details') }}</label>
                <textarea class="form-control l-s0 input-form-control payment_details" id="exampleFormControlTextarea1" name="payment_details" row="3" required data-value-missing="{{ __('This field is required.') }}"></textarea>
                <span class="error">{{ $errors->first('payment_details') }}</span>
            </div>

            <!-- Payment Proof -->
            <div class="attach-file attach-print label-top mt-20">
                <label for="file" class="form-label text-gray-100 gilroy-medium">{{ __('Payment Proof') }}</label>
                <input class="form-control upload-filed payment_details" type="file" required data-value-missing="{{ __('Please select a file.') }}" name="proof_file" id="file">
                <span id="fileSpan" class="file-error"></span>
                <span class="error">{{ $errors->first('proof_file') }}</span>
            </div>
            </div>
        @endif

        @if($exchange_type !== 'crypto_sell')
        <!-- Receive Crypto -->
        <div class="param-ref sm-send mt-20">
            <label class="gilroy-medium text-gray-100 mb-6 f-16 leading-20">{{ __('Receive Crypto') }}</label>
            <select class="select2" data-minimum-results-for-search="Infinity" name="receive_with" id="receive_with">
                <option value="address">{{ __(':x Address', ['x' => $to_currency_code]) }}</option>
                <option value="wallet">{{ __(':x Wallet', ['x' => $to_currency_code]) }}  </option>
            </select>
        </div>

        <!-- Receive Address -->
        <div id="crypto_address_section">
            <div class="label-top mt-20">
                <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Receiving Address') }}</label>
                <input type="text" value="{{ old('crypto_address') }}" class="form-control input-form-control apply-bg crypto_address" name="crypto_address" id="crypto_address" required data-value-missing="{{ __('This field is required.') }}" placeholder="{{ __('Please provide your :x address', ['x' => $to_currency_code]) }}">
                <span class="error">{{ $errors->first('crypto_address') }}</span>
            </div>
            <p class="mb-0 text-gray-100 dark-B87 gilroy-regular r-f-12 f-12 mt-2"><em>* {{ __('Providing wrong address may permanent loss of your coin') }}</em></p>
        </div>
        @endif

        @if ($exchange_type == 'crypto_sell')
            <input type="hidden"  name="receive_with" value="wallet">
        @endif


        <!-- Payment Methods -->
        @if ($exchange_type == 'crypto_buy')
            <div class="row">
                <div class="col-12">
                    <div class="mt-20 param-ref" id="paymentMethodSection">
                        <label class="gilroy-medium text-gray-100 mb-2 f-15" for="payment_method">{{ __('Payment Method') }}</label>
                        <div class="avoid-blink">
                            <select class="select2" data-minimum-results-for-search="Infinity" name="gateway" id="payment_method" required data-value-missing="{{ __('This field is required.') }}">
                                @foreach($currencyPaymentMethods as $currencyPaymentMethod)
                                    <option value="{{ $currencyPaymentMethod->id }}">  {{ ($currencyPaymentMethod->id == Mts ) ? 'Wallet' : $currencyPaymentMethod->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        <!-- button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4 submit-button" id="exchange-confirm-submit-btn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                    <span class="visually-hidden"></span>
                </div>
                <span id="cryptoExchangeConfirmBtnText">{{ __('Confirm & :x', ['x' => __(ucwords(str_replace('crypto_', '', $exchange_type)))]) }}</span>
                <span id="rightAngleSvgIcon">{!! svgIcons('right_angle') !!}</span>
            </button>
        </div>

        <!-- Back Button -->
        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('user_dashboard.crypto_buy_sell.create') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn" id="cryptoConfirmBackBtn">
                {!! svgIcons('left_angle') !!}
                <span class="ms-1 back-btn ns cryptoConfirmBackBtnText">{{ __('Back') }}</span>
            </a>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>

<script type="text/javascript">
    'use strict';
    var confirmText = "{{ __('Confirming...') }}";
    var pretext = $('#cryptoExchangeConfirmBtnText').text();
    var extensions = JSON.parse(@json($extensions));
    var extensionsValidationRule = extensions.join('|');
    var extensionsValidation = extensions.join(', ');
    var errorMessage = '{{ __("Please select (:x) file.") }}';
    var invalidFileText = errorMessage.replace(':x', extensionsValidation);
    var exchangeTypeValue = "{{ $exchange_type }}";
    var defaultAmnt = "{{ $get_amount }}";
    var fromCurrencyValue = "{{ $from_currency }}";
    var toCurrencyValue = "{{ $to_currency }}";
    var expireTime = "{{ $expire_time }}";
    var expireSec = "{{ $expire_seconds }}";
    var expireText = "{{ __('Expired') }}";
</script>

<script src="{{ asset('Modules/CryptoExchange/Resources/assets/js/user_dashboard.min.js') }}"></script>
<script src="{{ asset('Modules/CryptoExchange/Resources/assets/js/countdown.min.js') }}"></script>
@endpush
