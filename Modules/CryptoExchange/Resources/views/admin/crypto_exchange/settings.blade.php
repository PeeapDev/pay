@extends('admin.layouts.master')

@section('title', __('Crypto Settings'))

@section('page_content')
  <!-- Main content -->
  <div class="row">

      <div class="col-md-12">
          <div class="box box-info" id="crypto_module_settings">
              <div class="box-header with-border">
                <h3 class="box-title">{{ __('Crypto Exchange Settings') }}</h3>
              </div>
              <form action="{{route('admin.crypto_setting_store')}}" method="post" id="crypto_settings" class="form-horizontal">
                {!! csrf_field() !!}
                <div class="box-body">

                  <div class="form-group row">
                      <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="auth">{{ __('Available For') }} :</label>
                      <div class="col-sm-6" >
                        <div class="form-check fw-bold f-14">
                          <input class="form-check-input" type="radio" id="auth" name="available" {{ (isset($prefData['crypto_exchange']['available']) && $prefData['crypto_exchange']['available'] == 'auth_user') ? 'checked':"" }} value="auth_user">
                          <label for="auth">{{ __('Auth User') }}</label>
                        </div>

                        <div class="form-check fw-bold f-14">
                          <input class="form-check-input" type="radio" id="guest" name="available" {{ (isset($prefData['crypto_exchange']['available']) && $prefData['crypto_exchange']['available'] == 'guest_user') ? 'checked':"" }} value="guest_user" >
                          <label for="guest">{{ __('Guest User') }}</label>
                        </div>

                        <div class="form-check fw-bold f-14">
                          <input class="form-check-input" type="radio" id="both" name="available" {{ (isset($prefData['crypto_exchange']['available']) && $prefData['crypto_exchange']['available'] == 'all') ? 'checked':"" }} value="all" >
                          <label for="both">{{ __('Both') }}</label>
                        </div>

                      </div>
                  </div>
                  <hr>

                  <div class="form-group row">
                      <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="email">{{ __('Verification Via') }} :</label>
                      <div class="col-sm-6" >
                        <div class="form-check fw-bold f-14">
                          <input class="form-check-input" type="radio" id="email" name="verification" {{ (isset($prefData['crypto_exchange']['verification']) && $prefData['crypto_exchange']['verification'] == 'email') ? 'checked':"" }} value="email">
                          <label for="email">{{ __('Email') }}</label> 
                        </div>
                        <div class="form-check fw-bold f-14">
                            <input class="form-check-input" type="radio" id="phone" name="verification" {{ (isset($prefData['crypto_exchange']['verification']) && $prefData['crypto_exchange']['verification'] == 'phone') ? 'checked':"" }} value="phone" >
                            <label for="phone">{{ __('Phone') }}</label>
                        </div>

                      </div>
                  </div>
                  <hr>

                  <div class="form-group row">
                      <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="crypto_swap">{{ __('Transaction Type') }}:</label>
                      <div class="col-sm-6" >
                        <div class="form-check fw-bold f-14">
                          <input class="form-check-input" type="radio" id="crypto_swap" name="transaction_type" {{ (isset($prefData['crypto_exchange']['transaction_type']) && $prefData['crypto_exchange']['transaction_type'] == 'crypto_swap') ? 'checked':"" }} value="crypto_swap">  
                          <label for="crypto_swap">{{ __('Crypto Swap') }}</label>
                        </div>
                        <div class="form-check fw-bold f-14">
                          <input class="form-check-input" type="radio" id="crypto_buy_sell" name="transaction_type" {{ (isset($prefData['crypto_exchange']['transaction_type']) && $prefData['crypto_exchange']['transaction_type'] == 'crypto_buy_sell') ? 'checked':"" }} value="crypto_buy_sell" >
                          <label for="crypto_buy_sell">{{ __('Crypto Buy / Sell') }}</label>
                        </div>

                        <div class="form-check fw-bold f-14">
                          <input class="form-check-input" type="radio" id="all_transaction" name="transaction_type" {{ (isset($prefData['crypto_exchange']['transaction_type']) && $prefData['crypto_exchange']['transaction_type'] == 'all') ? 'checked':"" }} value="all" >
                          <label for="all_transaction">{{ __('Both') }}</label>
                        </div>
                      </div>
                  </div>

                  @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_crypto_exchange_settings'))
                  <div class="box-footer">
                      <div class="col-md-10">
                        <button type="submit" class="btn btn-theme pull-right f-14" id="preference-submit">
                          <i class="fa fa-spinner fa-spin d-none"></i> <span id="preference-submit-text">{{ __('Save Settings') }}</span>
                        </button>
                      </div>
                  </div>
                  @endif
                </div>
              </form>
          </div>
      </div>
  </div>
  <!-- /.box -->
@endsection

@push('extra_body_scripts')
<script type="text/javascript">
    'use strict';
    var updateText = "{{ __('Updating...') }}";
</script>
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('Modules/CryptoExchange/Resources/assets/js/admin_transaction.min.js') }}"></script>
@endpush
