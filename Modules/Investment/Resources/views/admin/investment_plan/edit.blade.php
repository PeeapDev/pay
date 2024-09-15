@extends('admin.layouts.master')
@section('title', __('Edit Investment Plan'))

@section('head_style')
    <link rel="stylesheet" href="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/css/bootstrap-toggle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('Modules/Investment/Resources/assets/css/admin/investment_plan.min.css') }}">
@endsection

@section('page_content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-info" id="plan_edit">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Edit Investment Plan') }}</h3>
                </div>
                <br>

                <form action="{{ route('investment_plan.update', $investment->id) }}" method="post" class="form-horizontal" id="edit-investment-plan-form">
                    @csrf
                    @if ($investment->is_locked == 'Yes')
                        <input name="currency" type="hidden" value="{{ $investment->currency_id }}">
                    @endif
                    <input type="hidden" value="{{ $investment->is_locked }}" name="is_locked" id="is-locked">

                    <input type="hidden" name="currency_type" id="currency-type">

                    <!-- Name -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="name">{{ __('Name') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="name" class="form-control f-14" value="{{ $investment->name }}" placeholder="{{ __('Name') }}" required data-value-missing="{{ __('This field is required.') }}" id="name" maxlength="100">
                            <small class="form-text text-muted url slug-url"><strong>{{ url($investment->slug)}}</strong></small>
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        </div>
                    </div>

                    <!-- description -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="description">{{ __('Description') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="description" class="form-control f-14" value="{{ $investment->description }}" placeholder="{{ __('Description') }}" id="description" maxlength="200">
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        </div>
                    </div>

                    <!-- Invest Type -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="investment-type">{{ __('Investment Type') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 sl_common_bx type select2" name="investment_type" required data-value-missing="{{ __('This field is required.') }}" id="investment-type" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}}>
                                {!! generateOptions(['Fixed', 'Range'], $investment['investment_type']) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('investment_type') }}</span>
                        </div>
                    </div>

                    <!-- Term -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="term">{{ __('Duration') }}</label>
                        <div class="col-sm-3">
                            <input type="text" name="term" class="form-control f-14" value="{{ $investment->term }}" placeholder="{{ __('Term') }}" required data-value-missing="{{ __('This field is required.') }}" id="term" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}}>
                            <span class="text-danger">{{ $errors->first('term') }}</span>
                        </div>

                        <!-- Term Type -->
                        <div class="col-sm-3">
                            <select class="form-control f-14 sl_common_bx type select2" name="term_type" required data-value-missing="{{ __('This field is required.') }}" id="term-type" onchange="changeTerm()" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}}>
                                {!! generateOptions(['Hour', 'Day', 'Week', 'Month', 'Year'], $investment['term_type']) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('term_type') }}</span>
                        </div>
                    </div>

                    <!-- Interest rate -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="interest-rate">{{ __('Interest Rate') }}</label>
                        <div class="col-sm-3">
                            <input type="text" name="interest_rate" class="form-control f-14" value="{{ number_format((float) $investment->interest_rate, $preference, '.', '') }}" placeholder="{{ __('Interest rate') }}" required data-value-missing="{{ __('This field is required.') }}" id="interest-rate" onkeypress="return isNumberOrDecimalPointKey(this, event);" value="{{ old('amount') }}" oninput="restrictNumberToPrefdecimalOnInput(this)" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}}>
                            <span class="text-danger interest-rate">{{ $errors->first('interest_rate') }}</span>
                        </div>

                        <!-- Interest_rate_Type -->
                        <div class="col-sm-3">
                            <select class="form-control f-14 sl_common_bx type select2" name="interest_rate_type" required data-value-missing="{{ __('This field is required.') }}" id="interest-rate-type" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}}>
                                {!! generateOptions(['Percent', 'Fixed', 'APR'], $investment['interest_rate_type']) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('interest_rate_type') }}</span>
                        </div>
                    </div>

                    <!-- Interest_time_frame -->
                    <div class="form-group row align-items-center" id="interest-time-frame-div">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="interest-time-frame">{{ __('Profit Adjust') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 sl_common_bx type select2" name="interest_time_frame" required data-value-missing="{{ __('This field is required.') }}" id="interest-time-frame" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}}>
                                {!! generateOptions(['Hourly', 'Daily', 'Weekly', 'Monthly', 'Yearly'], $investment['interest_time_frame']) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('interest_time_frame') }}</span>
                        </div>
                    </div>

                    <!-- Currency -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="currency-id">{{ __('Currency') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 sl_common_bx type select2" name="currency_id" id="currency-id" required data-value-missing="{{ __('This field is required.') }}" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}}>
                                @foreach($currency as $item)
                                    <option {{ $item->id == $investment->currency_id ? 'selected' : '' }} data-type="{{ $item->type }}" value="{{ $item->id }}" >{{ $item->code }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger">{{ $errors->first('currency_id') }}</span>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="amount">{{ __('Amount') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="amount" class="form-control f-14" value="{{ number_format((float) $investment->amount, $preference, '.', '') }}" required data-value-missing="{{ __('This field is required.') }}" id="amount" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}}>
                            <span class="text-danger">{{ $errors->first('amount') }}</span>
                        </div>
                    </div>

                    <!-- Maximum Amount -->
                    <div class="form-group row align-items-center" id="maximum-amount-div">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="maximum-amount">{{ __('Maximum Amount') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="maximum_amount" class="form-control f-14" value="{{ $investment->maximum_amount ? number_format((float) $investment->maximum_amount, $preference, '.', '') : ''}}" placeholder="{{ __('Maximum Amount') }}" id="maximum-amount" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}}>
                            <span class="text-danger maximum-amount">{{ $errors->first('maximum_amount') }}</span>
                        </div>
                    </div>

                    <!-- Capital return term -->
                    <div class="form-group row align-items-center">
                        <label for="capital-return-term" class="col-sm-3 control-label f-14 fw-bold text-sm-end require">{{ __('Capital Return') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 sl_common_bx type select2" name="capital_return_term" required data-value-missing="{{ __('This field is required.') }}" id="capital-return-term" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}}>
                                {!! generateOptions(['Term Basis', 'After Matured'], $investment['capital_return_term']) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('capital_return_term') }}</span>
                        </div>
                    </div>

                    <!-- Maximum Investors -->
                    <div class="form-group row align-items-center">
                        <label for="maximum-investors" class="col-sm-3 control-label f-14 fw-bold text-sm-end require">{{ __('Maximum Invest Limit') }}</label>
                        <div class="col-sm-6">
                            <input type="number" name="maximum_investors" class="form-control f-14" required data-value-missing="{{ __('This field is required.') }}" value="{{ $investment->maximum_investors }}" placeholder="{{ __('Maximum times users can invest in a single plan (eg - 100 times)') }}" id="maximum-investors" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)">
                            <span class="text-danger">{{ $errors->first('maximum_investors') }}</span>
                        </div>
                    </div>
                    <!-- Maximum Limit for Investors -->
                    <div class="form-group row align-items-center">
                        <label for="maximum-limit-for-investor" class="col-sm-3 control-label f-14 fw-bold text-sm-end require">{{ __('Max Limit for A Investor') }}</label>
                        <div class="col-sm-6">
                            <input type="number" name="maximum_limit_for_investor" class="form-control f-14" required data-value-missing="{{ __('This field is required.') }}" value="{{ $investment->maximum_limit_for_investor }}" placeholder="{{ __('Maximum number of investments per user (eg - 5 times)') }}" id="maximum-limit-for-investor" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)">
                            <span class="text-danger investor-limit">{{ $errors->first('maximum_limit_for_investor') }}</span>
                        </div>
                    </div>


                    <!-- Payment Methods -->
                    <div class="form-group row align-items-center" id="payment-method-div">
                        <label for="payment-methods" class="col-sm-3 control-label f-14 fw-bold text-sm-end require">{{ __('Payment Methods') }}</label>
                        <div class="col-sm-6">
                            <select class="payment sl_common_bx form-control f-14" multiple="multiple" name="payment_methods[]" id="payment-methods" data-value-missing="{{ __('This field is required.') }}" required>
                                @foreach($paymentMethods as $paymentMethod)
                                    <option {{ $paymentMethod['id'] == $investment->paymentMethod ? 'selected' : '' }} value="{{ $paymentMethod['id'] }}">{{ $paymentMethod['name'] == 'Mts' ? 'Wallet' : $paymentMethod['name'] }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger">{{ $errors->first('payment_methods') }}</span>
                        </div>
                    </div>

                    <!-- Withdraw after matured -->
                    <div class="form-group row align-items-center" id="withdraw-after-matured-div">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="withdrawal-term">{{ __('Withdraw After Matured') }}</label>
                        <div class="col-sm-6">
                            <input type="checkbox" data-on="Yes" data-off="No" data-toggle="toggle" name="withdraw_after_matured" id="withdrawal-term" {{ $investment->is_locked == 'Yes' ? 'disabled' : ''}} {{ $investment->withdraw_after_matured == 'Yes'  ? 'checked' : ''}}>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <!-- Is featured -->
                    <div class="form-group row align-items-center" id="is-featured-div">
                        <input type="hidden" name="is_featured" value="{{ $investment->is_featured }}" id="is-featured">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="featured">{{ __('Is Featured') }}</label>
                        <div class="col-sm-6">
                            <input type="checkbox" data-on="Yes" data-off="No" data-toggle="toggle" name="is_featured" id="featured" {{ $investment->is_featured == 'Yes' ? 'checked' : ''}}>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="status">{{ __('Status') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 sl_common_bx select2" name="status" required data-value-missing="{{ __('This field is required.') }}" id="status">
                                {!! generateOptions(['Active', 'Inactive', 'Draft'], $investment['status']) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('status') }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 offset-md-3 mb-4">
                            <a class="btn btn-theme-danger f-14 me-1" href="{{ route('investment_plans.list') }}">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-theme f-14" id="investment-plan-edit-submit-btn">
                                <i class="fa fa-spinner fa-spin d-none"></i>
                                <span id="investment-plan-edit-submit-btn-text">{{ __('Update') }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('extra_body_scripts')

    @include('common.restrict_number_to_pref_decimal')
    @include('common.restrict_character_decimal_point')


    <script type="text/javascript">
        'use strict';
        var url = "{{ url('/') }}";
        var paymentMethodUrl = "{{ route('investment_plan.payment_methods') }}";
        var maxAmountError = "{{ __('Maximum amount should be greater than amount.') }}";
        var interestRateError = "{{ __('Interest rate should be less than amount.') }}";
        var investorError = "{{ __('Maximum invest limit should be greater than or equal to max limit for a investor.') }}";
        var updateButtonText = "{{ __('Updating...') }}";
        var paymentMethods = "{{ $investment->payment_methods }}";
        var selectedPaymentMethodText = " {{ __('Select Payment Methods.') }}";
        var selectedProfitAdjust = "{{ $investment['interest_time_frame'] }}";
    </script>
    <script src="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/js/bootstrap-toggle.min.js') }}" type="text/javascript"></script> 
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('Modules/Investment/Resources/assets/js/admin/investment_plan.min.js') }}" type="text/javascript"></script>
@endpush
