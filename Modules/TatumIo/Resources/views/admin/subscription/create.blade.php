@extends('admin.layouts.master')

@section('title', __('Webhook Subscription'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules/TatumIo/Resources/assets/admin/css/crypto_sent_receive.min.css') }}">
@endsection

@section('page_content')
    <div class="row">
        <div class="col-md-2">
            <h3 class="f-24">{{ __('Webhook Subscription') }}</h3>
        </div>
        <div class="col-md-6"></div>
        <div class="col-md-4">
            <div class="pull-right">
                <h3 class="f-24"></h3>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="box box-info" id="crypto-send-create">
                <form action="{{ route('admin.tatumio_asset.webhookstore') }}" class="form-horizontal" id="admin-crypto-send-form" method="POST">
                    <input type="hidden" value="{{ csrf_token() }}" name="_token" id="token">

                    <div class="box-body">

                        <!-- Network -->
                        <div class="form-group row align-items-center" id="network-div">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="user">{{ __('Network') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14 network" data-type="{{ $currency->type }}" name="network" type="text" value="{{ $network }}" id="network" readonly>
                            </div>
                        </div>

                        <!-- User -->
                        <div class="form-group row align-items-center" id="user-div">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="user">{{ __('User') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control f-14 sl_common_bx select2" name="user_id" id="user_id" required data-value-missing="{{ __("This field is required.") }}">
                                    <option value="">{{ __('Please select a user') }}</option>
                                    @foreach ($users as $key => $user)
                                        <option value='{{ $user->id }}'>{{ getColumnValue($user) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')
    <script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"  type="text/javascript" ></script>

    @include('common.restrict_number_to_pref_decimal')
    @include('common.restrict_character_decimal_point')

    <script type="text/javascript">
        'use strict';
        var addressBalanceUrl = '{{ route("admin.tatum.crypto.address_balance") }}';
        var backButtonUrl = '{{ route("admin.tatumio_asset.webhooklist", encrypt($network)) }}';
        var network = '{{ $network }}';
        var pleaseWait = '{{ __("Please Wait") }}';
        var loading = '{{ __("Loading...") }}';
        var errorText = '{{ __("Error!") }}'
        var merchantCryptoAddress = '{{ __("Merchant Address") }}';
        var merchantCryptoBalance = '{{ __("Merchant Balance") }}';
        var userCryptoAddress = '{{ __("User Address") }}';
        var backButton = '{{ __("Back") }}';
        var confirmButton = '{{ __("Confirm") }}';
    </script>

    <script src="{{ asset('Modules/TatumIo/Resources/assets/admin/js/subscription.min.js') }}"  type="text/javascript"></script>
@endpush
