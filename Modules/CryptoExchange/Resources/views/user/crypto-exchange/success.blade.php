@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow">
    <p class="mb-0 f-26 gilroy-Semibold text-center">{{ __(ucwords(str_replace('_', ' ', $result->type))) }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 3 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __(':x Complete', ['x' => __(ucwords(str_replace('crypto_', '', $result->type)))]) }}</p>
    <div class="text-center">{!! svgIcons('stepper_success') !!}</div>

    <div class="mt-36 d-flex justify-content-center position-relative h-44">
        <lottie-player class="position-absolute success-anim" src="{{ asset('public/user/templates/animation/confirm.json') }}" background="transparent" speed="1" autoplay></lottie-player>
    </div>
    <p class="mb-0 gilroy-medium f-20 success-text text-dark mt-20 text-center dark-5B r-mt-16">{{ __('Success') }}!</p>

    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-CDO mt-6 r-mt-8 leading-25">{{ __('Crypto exchange successful. Please wait for approval from the admin.') }}</p>

    <!-- Total -->
    <div class="exchange-total-parent border-light-mode d-flex justify-content-center align-items-center mt-24">
        <div class="exchange-total">
            <p class="text-gray-100 gilroy-medium f-16 text-center mb-0">{{ __('Total') }}</p>
            <p class="f-26 mb-0 mt-8"><span class="text-dark gilroy-medium">{!! moneyFormat('<span class="text-primary gilroy-Semibold">'.optional($result->fromCurrency)->code.'</span>', formatNumber($result->amount, optional($result->fromCurrency)->id)) !!}</span></p>
        </div>
    </div>

    <!-- Icon -->
    <div class="d-flex justify-content-center">
        <div class="border-hr d-flex justify-content-center"></div>
    </div>

    <!-- Getting Amount -->
    <div class="getting-amount border-light-mode">
        <p class="mb-0 text-center gilroy-medium text-dark dark-5B f-16">{{ __('Getting Amount') }}</p>
        <p class="f-26 mb-0 mt-8 text-center"><span class="text-dark gilroy-medium">{!! moneyFormat('<span class="text-primary gilroy-Semibold">'.optional($result->toCurrency)->code.'</span>', formatNumber($transInfo['getAmount'], optional($result->toCurrency)->id)) !!}</span></p>
    </div>

    <!-- Exchange -->
    <div class="Exchange-rate mt-24">

        <!-- Rate -->
        <p class="f-14 leading-17 text-gray-100 gilroy-medium text-center mb-0">{{ __('Exchange Rate') }}: 1 {{ optional($result->fromCurrency)->code }} ≈ {{ formatNumber($result->exchange_rate, $result->to_currency) }} {{ optional($result->toCurrency)->code }}
        </p>

        <!-- Track URL -->
        <p class="f-14 leading-17 text-gray-100 gilroy-medium text-center mb-0 track mt-24">{{ __('Track the transaction') }}</p>
        <p class="mb-0 mt-9 text-center"><a href="{{ $transInfo['trackUrl'] }}" class="link-text f-16 leading-20 gilroy-medium" target="_blank">{{ $transInfo['trackUrl'] }}</a>
        </p>
    </div>

    <!-- Button -->
    <div class="d-flex justify-content-center mt-30 r-mt-20">
        <a href="{{ route('crypto_exchange.print', $result->id) }}" class="print-btn d-flex justify-content-center align-items-center gap-10" target="_blank">
            {!! svgIcons('printer') !!}<span>{{ __('Print') }}</span>
        </a>
        <a href="{{ route('user_dashboard.crypto_buy_sell.create') }}" class="repeat-btn d-flex justify-content-center align-items-center ml-20">
            <span class="gilroy-medium">{{ __(':x Again', ['x' => __(ucwords(str_replace('_', ' ', $result->type)))]) }}</span>
        </a>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('public/user/templates/animation/lottie-player.min.js') }}"></script>
@endpush
