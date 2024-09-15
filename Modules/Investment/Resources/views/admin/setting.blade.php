@extends('admin.layouts.master')
@section('title', __('Investment Settings'))

@section('head_style')
  <link rel="stylesheet" href="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/css/bootstrap-toggle.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('Modules/Investment/Resources/assets/css/admin/investment_settings.min.css') }}">
@endsection

@section('page_content')

<!-- Main content -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">{{ __('Investment Settings') }}</h3>
            </div>
            <form action="{{ route('investment_setting.store') }}" method="POST" class="form-horizontal" id="investment_settings_form">
                @csrf
                <div class="box-body">
                    <!-- Schema Display -->
                    <div class="form-group row">
                        <label class="col-sm-4 control-label f-14 fw-bold text-sm-end mt-11" for="schema_display">{{ __('Plan Display Order') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 schema_display select2" name="schema_display" id="schema_display">
                                {!! generateOptions(['Default', 'Latest', 'Most popular', 'Featured', 'Random'], $result['schema_display']) !!}
                            </select>
                            <small class="form-text text-muted f-12">
                                <strong>*{{ __('Plan will appear on user panel based on this term.') }}</strong>
                            </small>
                        </div>
                    </div>

                    <!-- Plan Description -->
                    <div class="form-group row">
                        <label class="col-sm-4 control-label f-14 fw-bold text-sm-end mt-11" for="plan_description">{{ __('Plan Description') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 plan_description select2" name="plan_description" id="plan_description">
                            {!! generateOptions(['No' => 'No', 'Yes' => 'Yes'], $result['plan_description']) !!}
                            </select>
                            <small class="form-text text-muted f-12">
                                <strong>*{{ __('If set to Yes, plan description will appear on plan card.') }}</strong>
                            </small>
                        </div>
                    </div>

                    <!-- Admin Investment Plan View -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="admin_investment_plan_view">{{ __('Admin Investment Plan View') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 admin_investment_plan_view select2" name="admin_investment_plan_view" id="admin_investment_plan_view">
                            {!! generateOptions(['List' => 'List', 'Grid' => 'Grid'], $result['admin_investment_plan_view']) !!}
                            </select>
                        </div>
                    </div>

                    <!-- KYC -->
                    <div class="form-group row" id="kyc_div">
                        <input type="hidden" name="kyc"  value="{{ $result['kyc'] }}" id="kyc">
                        <label class="col-sm-4 control-label f-14 fw-bold text-sm-end kyc mt-11"  for="isEnabled">{{ __('KYC') }}</label>
                        <div class="col-sm-6">
                            <input type="checkbox" data-on="Yes" data-off="No" data-toggle="toggle" name="isEnabled" id="isEnabled">
                            <div class="clearfix"></div>
                            <small class="text-muted f-12">
                                <strong>*{{ __('If set to Yes, identity and address verification is required for investment.') }}</strong>
                            </small>
                        </div>
                    </div>

                    <!-- Invest start on admin approval -->
                    <div class="form-group row " id="invest-start-on-admin-approval-div">
                        <input type="hidden" name="invest_start_on_admin_approval"  value="{{ $result['invest_start_on_admin_approval'] }}" id="invest-start-on-admin-approval">
                        <label class="col-sm-4 control-label f-14 fw-bold text-sm-end mt-11" for="invest-on-admin-approval">{{ __('Admin Approval') }} </label>
                        <div class="col-sm-6">
                            <input type="checkbox" data-on="Yes" data-off="No" data-toggle="toggle" name="invest_on_admin_approval" id="invest-on-admin-approval">
                            <div class="clearfix"></div>
                            <small class="text-muted f-12">
                                <strong>*{{ __('If set to yes, investment will start after Admin approval, otherwise start immediate after the investment.') }}</strong>
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="update-button col-md-6 offset-md-4">
                            <a id="cancel_anchor" href="{{ route('investment_setting.add') }}" class="btn btn-theme-danger f-14 me-1">{{ __('Cancel') }}</a>
                            @if (Common::has_permission(auth('admin')->user()->id, 'edit_investment_setting'))
                                <button type="submit" class="btn btn-theme f-14" id="investment-settings-submit-btn">
                                <i class="fa fa-spinner fa-spin d-none"></i> <span id="investment-settings-submit-btn-text">{{ __('Update') }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('extra_body_scripts')

    <script type="text/javascript">
        'use strict';
        var submitButtonText = "{{ __('Updating...') }}";
    </script>
    <script src="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/js/bootstrap-toggle.min.js') }}" type="text/javascript"></script> 
    <script src="{{ asset('Modules/Investment/Resources/assets/js/admin/investment_settings.min.js') }}" type="text/javascript"></script>

@endpush





