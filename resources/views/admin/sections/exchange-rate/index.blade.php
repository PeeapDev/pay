@extends('admin.layouts.master')

@push('css')
    <style>
        .btn-excnage-rate{
            padding: 12px 50px;
        }
    </style>
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
    ], 'active' => __($page_title)])
@endsection

@section('content')
<div class="table-area">
    <form action="" method="POST">
        @csrf
        @method('PUT')
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __($page_title) }}</h5>
                  <div class="table-btn-area">
                    @include('admin.components.search-input',[
                        'name'  => 'provider_name',
                    ])
                </div>
            </div>
            <div class="table-responsive">
                @include('admin.components.data-table.exchange-rate-table', compact('providers'))
            </div>
        </div>
    </form>
    {{ get_paginate($providers) }}
</div>
@endsection

@push('script')
<script>
    itemSearch($("input[name=provider_name]"),$(".provider_name-search-table"),"{{ setRoute('admin.live.exchange.rate.search') }}",1);
</script>
@endpush
