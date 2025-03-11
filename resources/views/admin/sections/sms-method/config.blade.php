@extends('admin.layouts.master')

@push('css')

@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __( $page_title)])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __( $page_title) }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" method="POST" action="{{ setRoute('admin.setup.sms.update') }}">
                @csrf

                <div class="row mb-10-none">
                    <div class="col-xl-12 col-lg-12">
                        <div class="row align-items-end">
                            <div class="col-xl-10 col-lg-10 form-group">
                                <label>{{ __("name") }}*</label>
                                <select class="form--control nice-select" name="sms_method">
                                    <option disabled>{{ __("Select Name") }}</option>
                                    <option value="twilio" @if(@$general->sms_config->name == 'twilio') selected @endif>@lang('Twilio')</option>
                                    <option value="nexmo" @if(@$general->sms_config->name == 'nexmo') selected @endif>@lang('Nexmo')</option>
                                </select>

                            </div>
                            <div class="col-xl-2 col-lg-2 form-group">
                                <!-- Open Modal For Test code Send -->
                                @include('admin.components.link.custom',[
                                    'class'         => "btn--base modal-btn w-100",
                                    'href'          => "#test-sms",
                                    'text'          => "Send Test Code",
                                    'permission'    => "admin.setup.sms.test.code.send",
                                ])
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-none configForm row" id="twilio">
                        <div class="form-group col-md-4">
                            @include('admin.components.form.input',[
                                'label'         => __("Account SID")."*",
                                'name'          => "account_sid",
                                'type'          => "text",
                                'value'         => @$general->sms_config->account_sid,
                            ])
                        </div>
                        <div class="form-group col-md-4">
                            @include('admin.components.form.input',[
                                'label'         => __("Auth Token")."*",
                                'name'          => "auth_token",
                                'type'          => "text",
                                'value'         => @$general->sms_config->auth_token,
                            ])
                        </div>
                        <div class="form-group col-md-4">
                            @include('admin.components.form.input',[
                                'label'         => __("From Number")."*",
                                'name'          => "from",
                                'type'          => "text",
                                'value'         => @$general->sms_config->from,
                            ])
                        </div>
                    </div>
                    <div class="row mt-4 d-none configForm" id="nexmo">
                        <div class="form-group col-md-6">
                            @include('admin.components.form.input',[
                                'label'         => __("api Key")."*",
                                'name'          => "nexmo_api_key",
                                'type'          => "text",
                                'value'         => @$general->sms_config->nexmo_api_key,
                            ])
                        </div>
                        <div class="form-group col-md-6">
                            @include('admin.components.form.input',[
                                'label'         => __("Api Secret")."*",
                                'name'          => "nexmo_api_secret",
                                'type'          => "text",
                                'value'         => @$general->sms_config->nexmo_api_secret,
                            ])
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.button.form-btn',[
                            'class'         => "w-100 btn-loading",
                            'text'          => __("update"),
                            'permission'    => "admin.setup.sms.update",
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Test mail send modal --}}
    <div id="test-sms" class="mfp-hide medium">
        <div class="modal-data">
            <div class="modal-header px-0">
                <h5 class="modal-title">{{ __("Send Test Sms") }}</h5>
            </div>
            <div class="modal-form-data">
                <form class="modal-form" method="POST" action="{{ setRoute('admin.setup.sms.test.code.send') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-10-none mt-3">
                        <div class="col-xl-12 col-lg-12 form-group">
                            @include('admin.components.form.input',[
                                'label'         => __("Mobile Number")."*",
                                'name'          => "mobile",
                                'type'          => "text",
                                'value'         => old("mobile"),
                            ])
                        </div>

                        <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                            <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                            <button type="submit" class="btn btn--base">{{ __("send") }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        (function ($) {
            "use strict";

            var method = '{{ @$general->sms_config->name }}';


            if (!method) {
                method = 'twilio';
            }

            smsMethod(method);
            $('select[name=sms_method]').on('change', function() {
                var method = $(this).val();
                smsMethod(method);
            });

            function smsMethod(method){
                $('.configForm').addClass('d-none');
                if(method != 'php') {
                    $(`#${method}`).removeClass('d-none');
                }
            }

        })(jQuery);

    </script>
@endpush
