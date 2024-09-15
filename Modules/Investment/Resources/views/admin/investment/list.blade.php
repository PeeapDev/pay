@extends('admin.layouts.master')

@section('title', __('Investments'))

@section('head_style')
    <link rel="stylesheet" href="{{ asset('public/dist/plugins/daterangepicker-3.14.1/daterangepicker.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('Modules/Investment/Resources/assets/css/admin/investment_list.min.css') }}">
@endsection

@section('page_content')
    <div class="box">
        <div class="box-body pb-20" id="investment_list">
            <form class="form-horizontal" action="{{ route('investment.list') }}" method="GET">

                <input id="startfrom" type="hidden" name="from" value="{{ isset($from) ? $from : '' }}">
                <input id="endto" type="hidden" name="to" value="{{ isset($to) ? $to : '' }}">
                <input id="user_id" type="hidden" name="user_id" value="{{ isset($user) ? $user : '' }}">

                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex flex-wrap justify-content-between">
                            <div class="d-flex flex-wrap">
                                <!-- Date and time range -->
                                <div class="pr-25">
                                    <label class="f-14 fw-bold mb-1" for="daterange-btn">{{ __('Date Range') }}</label><br>
                                    <button type="button" class="btn btn-default f-14" id="daterange-btn">
                                        <span id="drp"><i class="fa fa-calendar"></i></span>
                                        <i class="fa fa-caret-down"></i>
                                    </button>
                                </div>

                                <!-- Currency -->
                                <div class="pr-25">
                                    <label class="f-14 fw-bold mb-1" for="currency">{{ __('Currency') }}</label><br>
                                    <select class="form-control f-14 select2" name="currency" id="currency">
                                        <option value="all" {{ ($currency =='all') ? 'selected' : '' }} >{{ __('All') }}</option>
                                        @foreach($i_currencies as $invest)
                                            <option value="{{ $invest->currency_id }}" {{ ($invest->currency_id == $currency) ? 'selected' : '' }}>
                                                {{ optional($invest->currency)->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="pr-25">
                                    <label class="f-14 fw-bold mb-1" for="status">{{ __('Status') }}</label><br>
                                    <select class="form-control select2 f-14" name="status" id="status">
                                        <option value="all" {{ ($status =='all') ? 'selected' : '' }} >{{ __('All') }}</option>
                                        @foreach($i_status as $invest)
                                            <option value="{{ $invest->status }}" {{ ($invest->status == $status) ? 'selected' : '' }}>
                                                {{ ($invest->status == 'Blocked') ? 'Cancelled' : $invest->status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Payment Method -->
                                <div class="pr-25">
                                    <label class="f-14 fw-bold mb-1" for="payment_methods">{{ __('Payment Method') }}</label><br>
                                    <select class="form-control select2 f-14" name="payment_methods" id="payment_methods">
                                        <option value="all" {{ ($pm =='all') ? 'selected' : '' }} >{{ __('All') }}</option>
                                        @foreach($i_pm as $invest)
                                            <option value="{{ $invest->payment_method_id }}" {{ ($invest->payment_method_id == $pm) ? 'selected' : '' }}>
                                                {{ (optional($invest->paymentMethod)->name == "Mts") ? __('Wallet') : optional($invest->paymentMethod)->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- User -->
                                <div class="pr-25">
                                    <label class="f-14 fw-bold mb-1" for="user_input">{{ __('User') }}</label><br>
                                    <div class="input-group f-14">
                                        <input id="user_input" type="text" name="user" placeholder="{{ __('Enter Name') }}" class="form-control f-14" value="{{ !empty($getName) ? getColumnValue($getName) : null }}">
                                    </div>
                                    <span class="d-block f-12 mt-1" id="error-user"></span>
                                </div>
                            </div>

                            <div>
                                <div class="input-group filter-button">
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
            <h3 class="panel-title text-bold ml-5">{{ __('All Invests') }}</h3>
        </div>
        <div class="col-md-4">
            <div class="btn-group pull-right">
                @if (Common::has_permission(auth('admin')->user()->id, 'view_profit_approve'))
                    <a href="javascript:;" class="btn btn-sm btn-default btn-flat f-12" id="approve">{{ __('Adjust Profit') }}</a>
                @endif
                <a href="javascript:;" class="btn btn-sm btn-default btn-flat f-12" id="csv">{{ __('CSV') }}</a>
                <a href="javascript:;" class="btn btn-sm btn-default btn-flat f-12" id="pdf">{{ __('PDF') }}</a>
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
@endsection

@push('extra_body_scripts')

    <script type="text/javascript">
        'use strict';
        var sessionDate = "{{ Session::get('date_format_type') }}";
        var url = "{{ route('investment.search') }}";
        var approveUrl = "{{ route('investments.approved') }}"
        var startDate = "{!! $from !!}";
        var endDate = "{!! $to !!}";
        var userError = "{{ __('User Does Not Exist!') }}";
        var dateRange = "{{ __('Pick a date range') }}";
        var alertTitle = "{{ __('Are you sure?') }}";
        var alertConfirm = "{{ __('Yes, approve!') }}";
        var alertCancel = "{{ __('Cancel') }}";
        var failedText = "{{ __('Failed!') }}";
        var alertText = "{{ __('You wont be able to revert this!') }}";
        var waitText = "{{ __('Please Wait') }}";
        var waitingText = "{{ __('Waiting') }}";
    </script>
    <script src="{{ asset('public/dist/plugins/daterangepicker-3.14.1/moment.min.js') }}"></script>
    <script src="{{ asset('public/dist/plugins/daterangepicker-3.14.1/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('Modules/Investment/Resources/assets/js/admin/investment.min.js') }}" type="text/javascript"></script>

    {!! $dataTable->scripts() !!}
@endpush
