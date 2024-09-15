@extends('user.layouts.app')

@section('content')
	<div class="bg-white pxy-62" id="invest_confirm">
        <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('New Investment') }}</p>
        <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
        <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Confirm Investment') }}</p>
		<div class="text-center">
			{!! svgIcons('stepper_confirm') !!}
		</div>
        <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Please review all of the information below before confirming the investment.') }}</p>
		@include('user.common.alert')
		<form action="{{ route('user.invest.payment') }}" method="POST" accept-charset="UTF-8" id="confirm-form">
			<input value="{{csrf_token()}}" name="_token" id="token" type="hidden">
			<input value="{{ $investInfo['payment_method'] }}" name="payment_method" id="method" type="hidden">
			<input value="{{ $investInfo['amount'] }}" name="userAmount" id="amount" type="hidden">

			<div class="plan-details">
				<p class="mb-18 text-dark f-18 leading-22 gilroy-Semibold text-start mt-32">{{ __('Plan Details') }}</p>
				<div class="transaction-box">
					<div class="pb-11 border-b-EF d-flex justify-content-between">
						<p class="mb-0 f-16 leading-19 gilroy-regular text-gray-100">{{ __('Plan Name') }}</p>
						<p class="mb-0 f-16 leading-19 gilroy-regular text-gray-100">{{ $investInfo['plan_name']}}</p>
					</div>
					<div class="pb-11 border-b-EF d-flex justify-content-between mt-14">
						<p class="mb-0 f-16 leading-19 gilroy-regular text-gray-100">{{ __('Duration') }}</p>
						<p class="mb-0 f-16 leading-19 gilroy-regular text-gray-100">{{ $investInfo['term'] }} {{ $investInfo['term_type'] }}</p>
					</div>
					<div class="pb-11 border-b-EF d-flex justify-content-between mt-14">
						<p class="mb-0 f-16 leading-19 gilroy-regular text-gray-100">{{ $investInfo['interest_time_frame'] }} {{ __('Profit') }}</p>
						<p class="mb-0 f-16 leading-19 gilroy-regular text-gray-100">
							{{ formatNumber($investInfo['interest_rate'], $investInfo['currency_id']) }}
							@if ($investInfo['interest_rate_type'] == 'Percent')
								{{ '%' }}
							@elseif ($investInfo['interest_rate_type'] == 'APR')
								{{ '% APR' }}
							@else
								{{ $investInfo['currency_code'] }}
							@endif
						</p>
					</div>
					<div class="d-flex justify-content-between mt-14">
						<p class="mb-0 f-16 leading-19 gilroy-regular text-gray-100">{{ __('Payment Method') }}</p>
						<p class="mb-0 f-16 leading-19 gilroy-regular text-gray-100">{{ $investInfo['payment_method_name'] == 'Wallet' ? __('Wallet') : $investInfo['payment_method_name'] }}</p>
					</div>
				</div>
				<p class="mb-18 text-dark f-18 leading-22 gilroy-Semibold text-start inv-mt mt-32">{{ __('Currency') }}</p>
				<div class="pb-13 border-b-EF d-flex justify-content-between transaction-box">
					<p class="mb-0 f-16 leading-19 gilroy-regular text-gray-100">{{ __('Investment amount') }}</p>
					<p class="mb-0 f-16 leading-19 gilroy-regular text-gray-100">{{ moneyFormat($investInfo['currency_symbol'], formatNumber($investInfo['amount'], $investInfo['currency_id'])) }}</p>
				</div>
				<div class="d-flex justify-content-between inv-mt mt-16">
					<p class="mb-0 f-16 leading-20 text-dark gilroy-medium w-181">{{ __('Total return amount with estimate profit:') }}</p>
					<p class="mb-0 f-16 leading-20 text-dark gilroy-medium">{{ moneyFormat($investInfo['currency_symbol'], formatNumber($investInfo['total'], $investInfo['currency_id'])) }}</p>
				</div>
				<div class="d-grid">
					<button type="submit" id="confirm-button" class="btn btn-lg btn-primary mt-4">
						<div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                            <span class="visually-hidden"></span>
                        </div>
						<span id="confirm-button-text">{{ __('Continue')}}</span>
						{!! svgIcons('right_angle') !!}
					</button>
				</div>
				<div class="d-flex justify-content-center align-items-center inv-back-mt mt-4 back-direction">
					<a href="{{ route('user.invest.create') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn deposit-confirm-back-btn">
						{!! svgIcons('left_angle') !!}
					<span class="ms-1 back-btn">{{ __('Back') }}</span>
					</a>
				</div>
			</div>
		</form>
	</div>
@endsection

@push('js')
	<script type="text/javascript">
		'use strict';
		var continueButtonText = "{{ __('Investing...') }}";
		var pretext = "{{ __('Continue') }}";
	</script>
	<script src="{{ asset('Modules/Investment/Resources/assets/js/user/invest.min.js') }}" type="text/javascript"></script>
@endpush
