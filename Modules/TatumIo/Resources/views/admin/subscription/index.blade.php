@extends('admin.layouts.master')

@section('title', __('Webhook Notification'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/daterangepicker-3.1/daterangepicker.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules/TatumIo/Resources/assets/admin/css/crypto_transaction.min.css') }}">
@endsection

@section('page_content')
<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <div class="col-md-8">
                <div class="top-bar-title padding-bottom pull-left">{{ __('Webhook Notification Subscriptions of :x', ['x' => $network]) }}</div>
            </div>
            @if (Common::has_permission(auth('admin')->user()->id, 'add_currency'))
                <div class="col-md-4">

                   
                    <a href="{{ url(config('adminPrefix') . '/tatumio/webhook/create/'.$network) }}" class="btn btn-theme pull-right f-14"><span class="fa fa-plus"></span> {{ __('Create Subscription') }}</a>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="box">
    <div class="box-body">
        <!-- Main content -->
        <div class="row">
            <div class="col-md-12 f-14">
                <div class="panel panel-info">
                    <div class="panel-body">
                        <div class="table-responsive">
                            {!! $dataTable->table(['class' => 'table table-striped table-hover dt-responsive', 'width' => '100%', 'cellspacing' => '0']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/daterangepicker-3.1/daterangepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.js') }}" type="text/javascript"></script>

{!! $dataTable->scripts() !!}

<script type="text/javascript">
    'use strict';
    var sessionDateFormat = '{{ Session::get("date_format_type") }}';
</script>

@endpush


