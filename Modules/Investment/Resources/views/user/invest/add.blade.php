@extends('user.layouts.app')
@section('content')
    <div class="bg-white pxy-62" id="invest_add">
        <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('New Investment') }}</p>
        @if ($planCount != 0)
            <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 1 of 3') }}</p>
            <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Fill Information') }}</p>
            <div class="text-center">
                {!! svgIcons('stepper_create') !!}
            </div>
            @include('user.common.alert')
            <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20"> {{ __('You can invest on any plan using our popular payment methods or wallet.') }}</p>
       
            <form method="POST" action="{{ route('user.invest.store') }}" id="invest-form">
                <input type="hidden" value="{{ auth()->id() }}" name="user_id" id="user-id">
                <input type="hidden" value="{{ csrf_token() }}" name="_token" id="token">
                @if(isset($investmentPlan))
                    <div class="row">
                        <div class="col-md-12">
                            <div class="label-top mt-20">
                                <label class="gilroy-medium text-gray-100 mb-2 f-15" for="plan">{{ __('Investment Plan') }}</label>
                                <input type="hidden" class="form-control input-form-control apply-bg l-s2" value="{{ $investmentPlan->id }}" name="plan_id"  
                                placeholder="{{ __('Investment Plan') }}" id="investment-plan" 
                                data-type="{{ $investmentPlan->investment_type }}" 
                                data-amount="{{ number_format((float) $investmentPlan->amount, $preference, '.', '') }}" data-maximum-amount="{{ number_format((float) $investmentPlan->maximum_amount, $preference, '.', '') }}" data-currency="{{ $investmentPlan->currency_id }}" 
                                data-payment-methods="{{ $investmentPlan->payment_methods }}" 
                                data-currency-code="{{ optional($investmentPlan->currency)->code }}" 
                                data-currency-type="{{ optional($investmentPlan->currency)->type }}">

                                <input type="text" class="form-control input-form-control apply-bg l-s2" value="{{ $investmentPlan->name }}" name="plan" placeholder="{{ __('Investment Plan') }}" id="plan" required data-value-missing="{{ __('This field is required.') }}" readonly>

                                @error('investment_plan')
                                    <div class="error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-12">
                            <div class="mt-28 param-ref">
                                <label class="gilroy-medium text-gray-100 mb-2 f-15" for="investment-plan">{{ __('Investment Plan') }}</label>
                                <div class="avoid-blink">
                                    <select class="select2 sl_common_bx" name="plan_id" id="investment-plan" required data-value-missing="{{ __('This field is required.') }}">
                                        @foreach ($investmentPlans as $plan)
                                            <option value="{{ $plan->id }}"
                                                data-type="{{ $plan->investment_type }}" 
                                                data-amount="{{ $plan->amount }}"
                                                data-maximum-amount="{{ $plan->maximum_amount }}"
                                                data-currency="{{ $plan->currency_id }}" 
                                                data-payment-methods="{{ $plan->payment_methods }}"
                                                data-currency-code="{{ optional($plan->currency)->code }}"
                                                data-currency-type="{{ optional($plan->currency)->type }}" {{ old('plan_id') ==  $plan->id ? 'selected' : ''}} 
                                            >
                                                {{ $plan->name }} ({{ $plan->investment_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('investment_plan')
                                    <div class="error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="label-top mt-20">
                            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="amount">{{ __('Amount') }} <span class="currency"></span></label>
                            <input type="text" class="form-control input-form-control apply-bg l-s2 amount"value="{{ old('user_amount') }}" name="user_amount" placeholder="{{ __('Give an amount') }}" id="amount" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" required data-value-missing="{{ __('This field is required.') }}">
                        </div>
                        <div class="custom-error amountLimit"></div>

                        <div class="d-flex justify-content-between mt-10 d-none" id="amount-limit-div">

                            <p class="mb-0 f-12 leading-15 gilroy-medium text-gray"><span class="text-gray-100" id="amount-range"></span></p>

                            <p class="mb-0 f-12 leading-15 gilroy-medium text-gray"><span class="text-gray-100" id="maximum-amount"></span></p>
                        </div>

                        

                        @error('user_amount')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row d-none" id="empty-payment">
                    <div class="col-12">
                        <div class="mt-20 param-ref">
                            <span class="text-danger">{{ __('Wallet or payment method is not available.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row d-none" id="payment-method-div">
                    <div class="col-12">
                        <div class="mt-20 param-ref">
                            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="payment-method">{{ __('Payment Methods') }}</label>
                            <div class="avoid-blink">
                                <select class="select2 sl_common_bx" data-minimum-results-for-search="Infinity" name="payment_method" id="payment-method" required data-value-missing="{{ __('This field is required.') }}">
                                
                                </select>
                            </div>
                        </div>
                        @error('payment_method')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-lg btn-primary mt-4" id="invest-money-btn">
                        <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                            <span class="visually-hidden"></span>
                        </div>
                        <span>{{ __('Next') }}</span>
                        {!! svgIcons('right_angle') !!}
                    </button>
                </div> 
            </form>
        @else
            <div class="notfound mt-16 bg-white p-4">
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-26">
                    <div class="image-notfound">
                        <img src="{{ asset('public/dist/images/not-found.png') }}" class="img-fluid">
                    </div>
                    <div class="text-notfound">
                        <p class="mb-0 f-20 leading-25 gilroy-medium text-dark text-center">{{ __('Sorry!') }} {{ __('No data found.') }}</p>
                        <p class="mb-0 f-16 leading-24 gilroy-regular text-gray-100 mt-12">{{ __('The requested data does not exist for this feature overview.') }}</p>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection

@push('js')
    
    @include('common.restrict_number_to_pref_decimal')
    @include('common.restrict_character_decimal_point')

    <script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        'use strict';
        var minAmount = "{{ __('Minimum amount') }}";
        var maxAmount = "{{ __('Maximum amount') }}";
        var investSubmitButtonText = "{{ __('Submitting..') }}";
        var preText = "{{ __('Next') }}";
        var failedText = "{{ __('Failed') }}";
        var investmentPlan = "{{ isset($investmentPlan) && !empty($investmentPlan) ? true : false }}"; 
        var transactionTypeId = "{{ Investment }}";
        var currencyTypeUrl = "{{ route('user.invest.check_currency_type') }}";
        var paymentMethodUrl = "{{ route('user.invest.active_payment_methods') }}";
        var amountLimitUrl = "{{ route('user.invest.check_amount_limit') }}";
        var waitText = "{{ __('Please Wait') }}";
        var loadingText = "{{ __('Loading') }}";
    </script>
    <script src="{{ asset('Modules/Investment/Resources/assets/js/user/invest.min.js') }}" type="text/javascript"></script>
@endpush
