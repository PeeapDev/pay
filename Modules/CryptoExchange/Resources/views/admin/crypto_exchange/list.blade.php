@extends('admin.layouts.master')

@section('title', __('Crypto Transaction'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/daterangepicker-3.14.1/daterangepicker.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.css') }}">
@endsection

@section('page_content')
<div id="crypto_exchange_list">
    <div class="box">
        <div class="box-body pb-20">
            <form class="form-horizontal" action="{{ route('admin.crypto_exchanges.index') }}" method="GET">

                <input id="startfrom" type="hidden" name="from" value="{{ isset($from) ? $from : '' }}">
                <input id="endto" type="hidden" name="to" value="{{ isset($to) ? $to : '' }}">
                <input id="user_id" type="hidden" name="user_id" value="{{ isset($user) ? $user : '' }}">

                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="d-flex flex-wrap">
                                <!-- Date and time range -->
                                <div class="pr-25">
                                    <span class="f-14 fw-bold mb-1">{{ __('Date Range') }}</span><br>
                                    <button type="button" class="btn btn-default f-14" id="daterange-btn">
                                        <span id="drp">
                                            <i class="fa fa-calendar"></i>
                                        </span>
                                        <i class="fa fa-caret-down"></i>
                                    </button>
                                </div>

                                <!-- Currency -->
                                <div class="pr-25">
                                    <span class="f-14 fw-bold mb-1" for="currency">{{ __('Currency') }}</span><br>
                                    <select class="form-control f-14 select2" name="currency" id="currency">
                                        <option value="all" {{ $currency == 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                                        @foreach ($exchanges_currency as $exchange)
                                            <option value="{{ $exchange->from_currency }}"
                                                {{ $exchange->from_currency == $currency ? 'selected' : '' }}>
                                                {{ optional($exchange->fromCurrency)->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="pr-25">
                                    <span class="f-14 fw-bold mb-1" for="status">{{ __('Status') }}</span><br>
                                    <select class="form-control f-14 select2" name="status" id="status">
                                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                                        @foreach ($exchanges_status as $exchange)
                                            <option value="{{ $exchange->status }}"
                                                {{ $exchange->status == $status ? 'selected' : '' }}>
                                                {{  getStatus($exchange->status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="pr-25">
                                    <span class="f-14 fw-bold mb-1" for="user">{{ __('User') }}</span><br>
                                    <input id="user" type="text" name="user" placeholder="{{ __('Enter Name') }}" class="form-control f-14" value="{{ !empty($getName) ? getColumnValue($getName) : null }}">
                                    <span class="f-12" id="error-user"></span>
                                </div>
                            </div>

                            <div>
                                <div class="input-group mt-3">
                                    <button type="submit" name="btn" class="btn btn-theme f-14" id="btn">{{ __('Filter') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <h3 class="panel-title text-bold ml-5">{{ __('All Exchanges') }}</h3>
        </div>
        <div class="col-md-4">
            <div class="btn-group pull-right">
                <a href="" class="btn btn-sm btn-default btn-flat f-14" id="csv">{{ __('CSV') }}</a>
                <a href="" class="btn btn-sm btn-default btn-flat f-14" id="pdf">{{ __('PDF') }}</a>
            </div>
        </div>
    </div>

    <div class="box mt-20">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-info">
                        <div class="panel-body">
                            <div class="table-responsive f-14">
                                {!! $dataTable->table(['class' => 'table table-striped table-hover dt-responsive', 'width' => '100%', 'cellspacing' => '0']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('extra_body_scripts')

    <script src="{{ asset('public/dist/plugins/daterangepicker-3.14.1/daterangepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.js') }}" type="text/javascript"></script>

    {!! $dataTable->scripts() !!}

    <script type="text/javascript">
        'use strict';
        var dateFormateType = "{{ Session::get('date_format_type') }}";
        var formDate = "{!! $from !!}";
        var toDate = "{!! $to !!}";
        var ajaxUrl = "{{ route('admin.crypto_exchanges.user_search') }}";
        var csvUrl = "{{ route('admin.crypto_exchanges.csv') }}";
        var pdfUrl = "{{ route('admin.crypto_exchanges.pdf') }}";
        var pickDateRange = "{{ __('Pick a date range') }}";
        var userDoesntExist = "{{ __('User Does Not Exist') }}";

    </script>
    <script src="{{ asset('Modules/CryptoExchange/Resources/assets/js/admin_transaction.min.js') }}"></script>

@endpush
