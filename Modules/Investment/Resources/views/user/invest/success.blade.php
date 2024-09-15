@extends('user.layouts.app')
@section('content')
<div class="bg-white pxy-62" id="invest_success">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('New Investment') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 3 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Investment Complete') }}</p>
    <div class="text-center">
        {!! svgIcons('stepper_success') !!}            
    </div>
    <div class="mt-36 d-flex justify-content-center position-relative h-44">
        <lottie-player class="position-absolute success-anim" src="{{ asset('public/user/templates/animation/confirm.json') }}"  background="transparent" speed="1" autoplay></lottie-player>
    </div>
    <p class="mb-0 gilroy-medium f-20 success-text text-dark mt-20 text-center dark-5B r-mt-16">{{ __('Success!') }}</p>
    <p class="mb-0 text-center inv-complete f-14 gilroy-medium text-gray dark-CDO mt-6 r-mt-8 leading-25"> {{ __('Investment has been successfully done. You can see the details under the transaction details.') }} </p>
    <div class="success-amount-box mt-4">
        <P class="mb-0 gilroy-medium text-primary dark-A0 text-center mt-29 r-text-12 f-16">{{ __('Invested Amount') }}</P>
        <p class="mb-0 text-dark dark-5B gilroy-Semibold f-32 text-center r-text-24 pb-23 mt-2">{{ formatNumber($investInfo['amount'], $investInfo['currency_id']) }} {{ $investInfo['currency_code'] }}</p>
    </div>
    <p class="mb-0 inv-complete f-14 leading-17 gilroy-medium text-gray-100 text-center mt-24">{{ __(':x Plan via :y', ['x' => $investInfo['plan_name'], 'y' => $investInfo['payment_method_name']]) }} </p>
    <div class="d-flex justify-content-center mt-32 r-mt-20">
        <a href="{{ route('user.investment.details', $investInfo['investId']) }}" class="print-btn d-flex justify-content-center align-items-center inv-complete gap-10">
            <span class="ml-10">{{ __('Invest Details') }}</span>                        
        </a>
        <a href="{{ route('user.investment_plans.list')}}" class="repeat-btn d-flex justify-content-center align-items-center ml-20">
            <span class="gilroy-medium">{{ __('Available Plan') }}</span>                        
        </a>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('public/user/templates/animation/lottie-player.min.js') }}"></script>
    <script src="{{ asset('Modules/Investment/Resources/assets/js/user/invest.min.js') }}" type="text/javascript"></script>
@endpush