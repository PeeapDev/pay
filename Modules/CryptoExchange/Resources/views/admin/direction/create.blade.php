@extends('admin.layouts.master')

@section('title', __('Add Direction'))

@section('head_style')
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/select2-4.1.0-rc.0/css/select2.min.css')}}">
@endsection

@section('page_content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info" id="crypto_direction_create">
                    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'add_crypto_direction'))
                    <div class="box-header with-border">
                      <h3 class="box-title">{{ __('Add Direction') }}</h3>
                    </div>
                    @endif

                    <form action="{{ route('admin.crypto_direction.store') }}" class="form-horizontal" id="exchange_direction_form" method="POST">
                        @csrf
                        <div class="box-body">
                            <!-- From -->

                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="direction_type">{{ __('Direction Type') }}</label>
                                <div class="col-sm-6">
                                    <select class="sl_common_bx select2" required data-value-missing="{{ __('This field is required.') }}" name="direction_type" id="direction_type">
                                        @if(transactionTypeCheck('crypto_swap'))
                                            <option value="crypto_swap" {{ old('direction_type') == 'crypto_swap' ? 'selected' : '' }}>{{ __('Crypto Swap') }}</option>
                                        @endif
                                        @if( transactionTypeCheck('crypto_buy_sell') )
                                            <option value="crypto_buy" {{ old('direction_type') == 'crypto_buy' ? 'selected' : '' }}>{{ __('Crypto Buy') }}</option>
                                            <option value="crypto_sell" {{ old('direction_type') == 'crypto_sell' ? 'selected' : '' }}>{{ __('Crypto Sell') }}</option>
                                        @endif

                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="from_currency_id">{{ __('From Currency') }}</label>
                                <div class="col-sm-6">
                                    <select class="form-control f-14 sl_common_bx select2" required data-value-missing="{{ __('This field is required.') }}" name="from_currency_id" id="from_currency_id">
                                        <option value="">{{ __('Select One') }}</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" {{ old('from_currency_id') == $currency->id ? 'selected' : '' }} data-type="{{ $currency->type }}">{{ $currency->code }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error">{{ $errors->first('from_currency_id') }}</span>
                                </div>
                            </div>

                            <!-- To -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="to_currency_id">{{ __('To Currency') }}</label>
                                <div class="col-sm-6">
                                    <select class="form-control f-14 sl_common_bx select2" required data-value-missing="{{ __('This field is required.') }}" name="to_currency_id" id="to_currency_id">
                                        <option value="">
                                            {{ __('Select One') }}
                                        </option>
                                    </select>
                                    <span class="error">{{ $errors->first('to_currency_id') }}</span>

                                </div>
                            </div>

                            <!-- Exchange from -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="exchange_from">{{ __('Exchange From') }}</label>
                                <div class="col-sm-6">
                                    <select class="sl_common_bx select2" required data-value-missing="{{ __('This field is required.') }}" name="exchange_from" id="exchange_from">
                                        <option value='local' {{ old('exchange_from') == 'local' ? 'selected' : '' }}>{{ __('Local') }}</option>
                                        <option value='api' {{ old('exchange_from') == 'api' ? 'selected' : '' }}>{{ __('API') }}</option>
                                    </select>
                                    <small class="rate-setup-text text-muted f-12 d-none">
                                        <strong><span class="text-danger">{{ __('Setup Currency Converter') }}</span>
                                            <a href=""data-bs-toggle="modal" data-bs-target="#currency-converter">{{ __('Click here.') }}</a>
                                        </strong>
                                    </small>
                                    <span class="error">{{ $errors->first('exchange_from') }}</span>

                                </div>
                            </div>

                            <!--  Exchange Rate -->
                            <div class="form-group row"  id="exchange_rate_div">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="exchange_rate">{{ __('Exchange Rate') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control f-14 exchange_rate" id="exchange_rate" name="exchange_rate"  placeholder="{{ __('Exchange Rate') }}" value="{{ old('exchange_rate') }}" data-value-missing="{{ __('This field is required.') }}"  onkeypress="return isNumberOrDecimalPointKey(this,event)" oninput="restrictNumberToPrefdecimalOnInputExchange(this)" required>
                                    <div class="clearfix"></div>
                                    <small class="form-text text-muted f-12">
                                        <strong>* {{ __('Equavalent with To Currency') }}</strong>
                                    </small>
                                    <span class="error">{{ $errors->first('exchange_rate') }}</span>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="form-group row" id="payment_method_direction">
                                <label class="col-sm-3 control-label mt-11 mt-11 f-14 fw-bold text-sm-end" for="payment_method">{{ __('Payment Method') }}</label>
                                <div class="col-sm-6">
                                    <select class="select2 form-control f-14" placeholder="{{ __('Select Gateway') }}" multiple="multiple" name="gateway[]" id="payment_method">

                                    </select>
                                    <small class="form-text text-muted f-12">
                                        <strong>* {{ __('Unauthenticated users will be able to buy cryptocurrency using this payment method.') }}</strong>
                                    </small>
                                    <span class="error">{{ $errors->first('payment_method') }}</span>
                                </div>
                            </div>

                            <!--  Exchange Fees -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="fees_percentage">{{ __('Charge Percentage') }}</label>
                                <div class="col-sm-6">
                                    <input class="form-control f-14 fees_percentage" required data-value-missing="{{ __('This field is required.') }}" placeholder="0.0000" name="fees_percentage" type="text" id="fees_percentage" value="{{ old('fees_percentage') }}" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)">
                                    <span class="error">{{ $errors->first('fees_percentage') }}</span>
                                </div>
                            </div>

                            <!--  Exchange Fees -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="fees_fixed">{{ __('Charge Fixed') }}</label>
                                <div class="col-sm-6">
                                    <input class="form-control f-14 fees_fixed" placeholder="0.0000" name="fees_fixed" type="text" id="fees_fixed" value="{{ old('fees_fixed') }}" required data-value-missing="{{ __('This field is required.') }}" onkeypress="return isNumberOrDecimalPointKey(this, event);"oninput="restrictNumberToPrefdecimalOnInput(this)">
                                    <span class="error">{{ $errors->first('fees_fixed') }}</span>
                                </div>
                            </div>

                            <!--  Min Amount -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="min_amount">{{ __('Min Amount') }}</label>
                                <div class="col-sm-6">
                                    <input class="form-control f-14 min_amount" placeholder="0.0000" name="min_amount" type="text" id="min_amount"
                                    required data-value-missing="{{ __('This field is required.') }}" value="{{ old('min_amount') }}" onkeypress="return isNumberOrDecimalPointKey(this, event);"oninput="restrictNumberToPrefdecimalOnInput(this)">
                                    <span class="error">{{ $errors->first('min_amount') }}</span>

                                </div>
                            </div>

                            <!--  Max Amount -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="max_amount">{{ __('Max Amount') }}</label>
                                <div class="col-sm-6">
                                    <div>
                                        <input class="form-control f-14 max_amount" placeholder="0.0000" name="max_amount" type="text" id="max_amount" value="{{ old('max_amount') }}" required data-value-missing="{{ __('This field is required.') }}" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)">
                                    </div>
                                    <span class="error">{{ $errors->first('max_amount') }}</span>
                                </div>
                            </div>

                            <!--  Payment Instruction -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end mt-11" for="payment_instruction">{{ __('Payment Instructions') }}</label>
                                <div class="col-sm-6">
                                    <textarea class="form-control f-14" placeholder="{{ __('Enter payment instructions') }}" name="payment_instruction" type="text" id="payment_instruction" rows="8">{{ old('payment_instruction') }}</textarea>
                                    <span class="error">{{ $errors->first('payment_instruction') }}</span>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="status">{{ __('Status') }}</label>
                                <div class="col-sm-6">
                                    <select class="form-control f-14 select2" required data-value-missing="{{ __('This field is required.') }}" name="status" id="status">
                                        <option value='Active' {{ old('status') == 'Active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value='Inactive' {{ old('status') == 'Inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                    </select>
                                    <span class="error">{{ $errors->first('status') }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 offset-md-3">
                                    <a class="btn btn-theme-danger f-14 me-1" href="{{ route('admin.crypto_direction.index') }}" id="users_cancel">{{ __('Cancel') }}</a>
                                    <button type="submit" class="btn btn-theme f-14" id="direction_create"><i class="fa fa-spinner fa-spin d-none"></i> <span id="direction_create_text">{{ __('Create') }}</span></button>
                                </div>
                            </div>
                        </div>
                    </form>
            </div>
        </div>
    </div>

    <div id="currency-converter" class="modal fade display_none" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content pB-27 M-50">
                <div class="modal-header header-background">
                    <h3 class="header-text f-24">{{ __('How to set up currrency conveter') }}</h3>
                </div>
                <div class="modal-body p-4 ps-5">
                    <div class="text-strong">
                        <p class="f-14">{{ __('Set up any currency converter api') }}</p>
                        <p class="blue-text f-18">{{ __('Crypto Conversion Api') }}</p>
                        <ul class="f-14">
                            <li><strong>{{ __('URL') }}: </strong><a target="_blank" href="https://min-api.cryptocompare.com">https://min-api.cryptocompare.com</a>
                            <li><strong>{{ __('Api Key') }}:</strong> {{ __('Get an api key from URL') }}</li>
                            <li>{{ __('After that set the value in the Api Key field.') }}</li>
                        </ul>
                        <a target="_blank" href="{{ url(\Config::get('adminPrefix') . '/settings/currency-conversion-rate-api') }}" class="currency-converter-link f-14">{{ __('Click this for Currency Converter Setup') }}</a>
                    </div>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-primary custom-btn-small f-14 float-end mt-2 me-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')

@include('common.restrict_number_to_pref_decimal')
@include('common.restrict_character_decimal_point')

<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
<script src="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/js/bootstrap-toggle.min.js') }}"></script>

<script type="text/javascript">
    'use strict';
    var createText = "{{ __('Creating...') }}";
    var selectGatewayText = "{{ __('Select Gateway') }}";
    var selectOneText = "{{ __('Select One') }}";
    var decimalPreferrence = "{{ preference('decimal_format_amount_crypto', 8) }}";
    var fromCurrencyIdOld = "{{ old('from_currency_id') }}";
    var maximumAmounText = "{{ __('Maximum amount should be greater than minimum amount.') }}";
    var getCurrencyUrl = "{{ route('admin.crypto_direction.currencies') }}";
    var gatewayListUrl = "{{ route('admin.crypto_direction.gateway') }}";
    var cryptoCompareEnabledApi = "{{ settings('crypto_compare_enabled_api') }}";
    var cryptoCompareApiKey = "{{ settings('crypto_compare_api_key') }}";
</script>

<script src="{{ asset('Modules/CryptoExchange/Resources/assets/js/admin_crypto_direction.min.js') }}"></script>

@endpush
