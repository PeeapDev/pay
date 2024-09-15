@extends('admin.layouts.master')
@section('title', __('Add Investment Plan'))
@section('head_style')
    <link rel="stylesheet" href="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/css/bootstrap-toggle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('Modules/Investment/Resources/assets/css/admin/investment_plan.min.css') }}">
@endsection
@section('page_content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-info" id="plan_add">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Add Investment Plan') }}</h3>
                </div>
                <br>

                <form id="add-investment-plan-form" action="{{ route('investment_plan.store') }}" method="post" class="form-horizontal">
                    @csrf
                    <!-- Name -->
                    <input type="hidden" name="currency_type" id="currency-type">
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="name">{{ __('Name') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="name" class="form-control f-14" required data-value-missing="{{ __('This field is required.') }}" value="{{ old('name') }}" placeholder="{{ __('Name') }}" maxlength="50" data-max-length="{{ __('Name length should be maximum 50.') }}" id="name">
                            <small class="text-muted url"><strong class="slug-url" id="span"></strong></small>
                            <span class="text-danger name">{{ $errors->first('name') }}</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="description">{{ __('Description') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="description" class="form-control f-14" value="{{ old('description') }}" placeholder="{{ __('Description') }}" id="description" maxlength="200">
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        </div>
                    </div>

                    <!-- Investment Type -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="investment-type">{{ __('Investment Type') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 sl_common_bx type select2" name="investment_type" required data-value-missing="{{ __('This field is required.') }}" id="investment-type">
                                {!! generateOptions(['Fixed', 'Range'], old('investment_type')) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('investment_type') }}</span>
                        </div>
                    </div>

                    <!-- Term -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="term">{{ __('Duration') }}</label>
                        <div class="col-sm-3">
                            <input type="text" name="term" class="form-control f-14" value="{{ old('term') }}" placeholder="{{ __('Duration') }}" required data-value-missing="{{ __('This field is required.') }}" id="term" oninput="restrictNumberToPrefdecimalOnInput(this)">
                            <span class="text-danger">{{ $errors->first('term') }}</span>
                        </div>

                        <!-- Term Type -->
                        <div class="col-sm-3">
                            <select class="form-control f-14 sl_common_bx type select2" name="term_type" required data-value-missing="{{ __('This field is required.') }}" id="term-type" onchange="changeTerm()">
                            {!! generateOptions(['Hour', 'Day', 'Week', 'Month', 'Year'], old('term_type')) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('term_type') }}</span>
                        </div>
                    </div>

                    <!-- Interest Rate -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="interest-rate">{{ __('Interest Rate') }}</label>
                        <div class="col-sm-3">
                            <input type="text" name="interest_rate" class="form-control f-14" value="{{ old('interest_rate') }}" placeholder="{{ __('Interest Rate') }}" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" required data-value-missing="{{ __('This field is required.') }}" id="interest-rate">
                            <span class="text-danger interest-rate">{{ $errors->first('interest_rate') }}</span>
                        </div>

                        <!-- Interest Rate Type -->
                        <div class="col-sm-3">
                            <select class="form-control f-14 sl_common_bx type select2" name="interest_rate_type" required data-value-missing="{{ __('This field is required.') }}" id="interest-rate-type">
                                {!! generateOptions(['Percent', 'Fixed', 'APR'], old('interest_rate_type')) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('interest_rate_type') }}</span>
                        </div>
                    </div>

                    <!-- Profit Adjust -->
                    <div class="form-group row align-items-center" id="interest-time-frame-div">
                        <label class="col-sm-3 require control-label f-14 fw-bold text-sm-end" for="interest-time-frame">{{ __('Profit Adjust') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 sl_common_bx type select2" name="interest_time_frame" required data-value-missing="{{ __('This field is required.') }}" id="interest-time-frame">

                            </select>
                            <span class="text-danger">{{ $errors->first('interest_time_frame') }}</span>
                        </div>
                    </div>

                    <!-- Currency -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="currency-id">{{ __('Currency') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 sl_common_bx type select2" name="currency_id" required data-value-missing="{{ __('This field is required.') }}" id="currency-id">
                                @foreach ($currency as $item)
                                    <option {{ old('currency_id') == $item->id ? 'selected' : '' }} data-type="{{ $item->type }}" value="{{ $item->id }}">{{ $item->code }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger">{{ $errors->first('currency_id') }}</span>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="amount">{{ __('Amount') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="amount" class="form-control f-14" onkeypress="return isNumberOrDecimalPointKey(this, event);" value="{{ old('amount') }}" oninput="restrictNumberToPrefdecimalOnInput(this)" required data-value-missing="{{ __('This field is required.') }}" id="amount">
                            <span class="text-danger">{{ $errors->first('amount') }}</span>
                        </div>
                    </div>

                    <!-- Maximum Amount -->
                    <div class="form-group row align-items-center" id="maximum-amount-div">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="maximum-amount">{{ __('Maximum Amount') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="maximum_amount" class="form-control f-14" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" value="{{ old('maximum_amount') }}" id="maximum-amount">
                            <span class="text-danger maximum-amount">{{ $errors->first('maximum_amount') }}</span>
                        </div>
                    </div>

                    <!-- Capital Return Term-->
                    <div class="form-group row align-items-center">
                        <label for="capital-return-term" class="col-sm-3 control-label f-14 fw-bold text-sm-end require">{{ __('Capital Return') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 sl_common_bx type select2" name="capital_return_term" required data-value-missing="{{ __('This field is required.') }}" id="capital-return-term">
                                {!! generateOptions(['Term Basis', 'After Matured'], old('capital_return_term')) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('capital_return_term') }}</span>
                        </div>
                    </div>

                    <!-- Maximum Investors -->
                    <div class="form-group row align-items-center">
                        <label for="maximum-investors" class="col-sm-3 require control-label f-14 fw-bold text-sm-end">{{ __('Maximum Invest Limit') }}</label>
                        <div class="col-sm-6">
                            <input type="number" name="maximum_investors" class="form-control f-14" value="{{ old('maximum_investors') }}" placeholder="{{ __('Maximum times users can invest in a single plan (eg - 100 times)') }}" required data-value-missing="{{ __('This field is required.') }}" id="maximum-investors" oninput="restrictNumberToPrefdecimalOnInput(this)">
                            <span class="text-danger">{{ $errors->first('maximum_investors') }}</span>
                        </div>
                    </div>

                    <!-- Maximum Limit For Investors -->
                    <div class="form-group row align-items-center">
                        <label for="maximum-limit-for-investor" class="col-sm-3 control-label f-14 fw-bold text-sm-end require">{{ __('Max Limit For A Investor') }}</label>
                        <div class="col-sm-6">
                            <input type="number" name="maximum_limit_for_investor" class="form-control f-14" value="{{ old('maximum_limit_for_investor') }}" placeholder="{{ __('Maximum number of investments per user (eg - 5 times)') }}" required data-value-missing="{{ __('This field is required.') }}" id="maximum-limit-for-investor" oninput="restrictNumberToPrefdecimalOnInput(this)">
                            <span class="text-danger investor-limit">{{ $errors->first('maximum_limit_for_investor') }}</span>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="form-group row align-items-center" id="payment-method-div">
                        <label for="payment-methods" class="col-sm-3 control-label f-14 fw-bold text-sm-end require">{{ __('Payment Methods') }}</label>
                        <div class="col-sm-6">
                            <select class="payment sl_common_bx form-control f-14" name="payment_methods[]" multiple="multiple" id="payment-methods" required data-value-missing="{{ __('This field is required.') }}">

                            </select>
                        </div>
                    </div>

                    <!-- Withdrawal Term After Matured -->
                    <div class="form-group row align-items-center" id="withdraw-after-matured-div">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="withdraw-after-matured">{{ __('Withdraw After Matured') }}</label>
                        <div class="col-sm-6">
                            <input type="checkbox" data-on="Yes" data-off="No" data-toggle="toggle" name="withdraw_after_matured" id="withdraw-after-matured">
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <!-- Is featured -->
                    <div class="form-group row align-items-center" id="is-featured-div">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="featured">{{ __('Is Featured') }}</label>
                        <div class="col-sm-6">
                            <input type="checkbox" data-on="Yes" data-off="No" data-toggle="toggle" name="is_featured" id="featured">
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end require" for="status">{{ __('Status') }}</label>
                        <div class="col-sm-6">
                            <select required data-value-missing="{{ __('This field is required.') }}" id="status" class="form-control f-14 sl_common_bx select2" name="status">
                                {!! generateOptions(['Active', 'Inactive', 'Draft'], old('status')) !!}
                            </select>
                            <span class="text-danger">{{ $errors->first('status') }}</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 offset-md-3 mb-3">
                            <a class="btn btn-theme-danger f-14 me-1" href="{{ route('investment_plans.list') }}">{{ __('Cancel') }}</a>
                            <button id="investment-plan-add-submit-btn" type="submit" class="btn btn-theme f-14">
                                <i class="fa fa-spinner fa-spin d-none"></i>
                                <span id="investment-plan-add-submit-btn-text">{{ __('Submit') }}</span>
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

    <script src="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/js/bootstrap-toggle.min.js') }}" type="text/javascript"></script> 
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        'use strict';
        var url = '{{ url('/') }}';
        var paymentMethodUrl = "{{ route('investment_plan.payment_methods') }}";
        var maxAmountError = "{{ __('Maximum amount should be greater than amount.') }}";
        var investorError = "{{ __('Maximum invest limit should be greater than or equal to max limit for a investor.') }}";
        var interestRateError = "{{ __('Interest rate should be less than amount.') }}";
        var submitButtonText = "{{ __('Submitting...') }}";
    </script>
    <script src="{{ asset('Modules/Investment/Resources/assets/js/admin/investment_plan.min.js') }}" type="text/javascript"></script>
@endpush