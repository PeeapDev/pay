@extends('admin.layouts.master')

@section('title', __('Investment Plans'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules/Investment/Resources/assets/css/admin/investment_plan.min.css') }}">
@endsection

@section('page_content')
    <div class="box box-default">
        <div class="box-body">
            <div class="top-section">
                <div class="">
                    <div class="top-bar-title padding-bottom pull-left">{{ __('Investment Plans') }}</div>
                </div>
                <div class="button-section">
                    @if (Common::has_permission(auth('admin')->user()->id, 'view_investment_plan'))
                        <div class="header-btn f-14">
                            <a href="javascript:;" class="button-one {{ settings('admin_investment_plan_view') == 'List' ? 'active1' : 'active2' }}" data-view="List" id="list"><i class="fa fa-list" aria-hidden="true"></i></a>
                            <a href="javascript:;" class="button-two {{ settings('admin_investment_plan_view') == 'Grid' ? 'active1' : 'active2' }}" data-view="Grid" id="grid"><i class="fa fa-th " aria-hidden="true"></i></a>
                        </div>
                    @endif
                    @if (Common::has_permission(auth('admin')->user()->id, 'add_investment_plan'))
                        <a href="{{ route('investment_plan.add') }}" class="btn btn-theme pull-right f-14 d-flex align-items-center"><span class="fa fa-plus"> &nbsp;</span>{{ __('Add Plan') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-body">
            <!-- Main content -->
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
    <script>
        'use strict';
        var waitText = "{{ __('Please Wait') }}";
        var LoadingText = "{{ __('Loading...') }}";
        var viewChangeUrl = "{{ route('investment_plan.view_change') }}";
    </script>
    <script src="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('Modules/Investment/Resources/assets/js/admin/investment_plan.min.js') }}" type="text/javascript"></script>

    {!! $dataTable->scripts() !!}
@endpush