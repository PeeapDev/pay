@extends('admin.layouts.master')
@section('title', __('Woocommerce Configure'))

@section('page_content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Generate Woocommerce Brand') }}</h3>
                    @if (!empty($pluginName))
                        <a href="{{ url('public/uploads/woocommerce', $pluginName) }}" class="btn btn-primary btn-flat pull-right"><i class="fa fa-download"></i> {{ $pluginName }}</a>
                    @endif
                </div>
                <form action="{{ route('addon.woocommerce.store') }}" method="post" id="WoocommerceConfigureForm" class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                    <div class="box-body">
                        <!-- Brand -->
                        <div class="form-group row">
                            <label for="plugin_brand" class="col-sm-3 control-label mt-11 f-14 text-sm-end">{{ __('Plugin Brand') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="plugin_brand" class="form-control f-14" id="plugin_brand" value="{{ $pluginInfo->plugin_brand ?? '' }}" placeholder="{{ __('Ex: Paymoney') }}" maxlength="50" required data-value-missing="{{ __('This field is required.') }}" maxlength="50" data-max-length="{{ __(':x length should be maximum :y charcters.', ['x' => __('Brand name'), 'y' => __('50')]) }}">
                                <span class="text-danger">{{ $errors->first('plugin_brand') }}</span>
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="form-group row">
                            <label for="plugin_name" class="col-sm-3 control-label mt-11 f-14 text-sm-end">{{ __('Plugin Name') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="plugin_name" class="form-control f-14" id="plugin_name" value="{{ $pluginInfo->plugin_name ?? '' }}" placeholder="{{ __('Ex: PayMoney - WooCommerce Addon') }}" required data-value-missing="{{ __('This field is required.') }}" maxlength="90" data-max-length="{{ __(':x length should be maximum :y charcters.', ['x' => __('Plugin name'), 'y' => __('90')]) }}">
                                <span class="text-danger">{{ $errors->first('plugin_name') }}</span>
                            </div>
                        </div>

                        <!-- Plugin URI -->
                        <div class="form-group row">
                            <label for="plugin_uri" class="col-sm-3 control-label mt-11 f-14 text-sm-end">{{ __('Plugin URI') }}</label>
                            <div class="col-sm-6">
                                <input type="url" name="plugin_uri" class="form-control f-14" id="plugin_uri" value="{{ $pluginInfo->plugin_uri ?? '' }}" placeholder="{{ __('Ex: https://plugin-uri.com') }}" required data-value-missing="{{ __('This field is required.') }}" maxlength="191" data-max-length="{{ __(':x length should be maximum :y charcters.', ['x' => __('Plugin uri'), 'y' => __('191')]) }}">
                                <span class="text-danger">{{ $errors->first('plugin_uri') }}</span>
                            </div>
                        </div>

                        <!-- Author -->
                        <div class="form-group row">
                            <label for="plugin_author" class="col-sm-3 control-label mt-11 f-14 text-sm-end">{{ __('Plugin Author') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="plugin_author" class="form-control f-14" id="plugin_author" value="{{ $pluginInfo->plugin_author ?? '' }}" placeholder="{{ __('Ex: Techvillage') }}" required data-value-missing="{{ __('This field is required.') }}" maxlength="50" data-max-length="{{ __(':x length should be maximum :y charcters.', ['x' => __('Author name'), 'y' => __('50')]) }}">
                                <span class="text-danger">{{ $errors->first('plugin_author') }}</span>
                            </div>
                        </div>

                        <!-- Author URI -->
                        <div class="form-group row">
                            <label for="plugin_author_uri" class="col-sm-3 control-label mt-11 f-14 text-sm-end">{{ __('Plugin Author URI') }}</label>
                            <div class="col-sm-6">
                                <input type="url" name="plugin_author_uri" class="form-control f-14" id="plugin_author_uri" value="{{ $pluginInfo->plugin_author_uri ?? '' }}" placeholder="{{ __('Ex: https://author-uri.com') }}" required data-value-missing="{{ __('This field is required.') }}">
                                <span class="text-danger">{{ $errors->first('plugin_author_uri') }}</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group row">
                            <label for="plugin_description" class="col-sm-3 control-label mt-11 f-14 text-sm-end">{{ __('Plugin Description') }}</label>
                            <div class="col-sm-6">
                                <textarea name="plugin_description" rows="2" class="form-control f-14" id="plugin_description">{{ isset($pluginInfo) ? $pluginInfo->plugin_description : '' }}</textarea>
                                <span class="text-danger">{{ $errors->first('plugin_description') }}</span>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group row">
                            <label for="publication_status"
                                class="col-sm-3 control-label mt-11 f-14 text-sm-end">{{ __('Publication status') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control f-14 select2" name="publication_status" id="publication_status" required>
                                    <option value="Active" {{ !empty($publicationStatus) && $publicationStatus == 'Active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="Inactive" {{ !empty($publicationStatus) && $publicationStatus == 'Inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                                <span class="text-danger">{{ $errors->first('publication_status') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 offset-md-3">
                                <a class="btn btn-theme-danger f-14 me-1 cancel-btn" href="{{ url(config('adminPrefix') . '/module-manager/addons') }}" id="users_cancel">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-theme f-14 submit-btn">
                                    <i class="fa fa-spinner fa-spin d-none"></i>
                                    <span class="submit-btn-text">{{ __('Submit') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
    <script type="text/javascript">
        'use strict';
        let submitText = '{{ __('Processing...') }}';
    </script>
    <script src="{{ asset('Modules/Woocommerce/Resources/assets/js/woocommerce-configure.min.js') }}"></script>
@endpush
