@extends('merchant.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('merchant.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("merchant.dashboard"),
        ]
    ], 'active' => __("Wallets")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        @include('merchant.components.wallets.sandbox_fiat',compact("sandbox_fiat_wallets"))
    </div>
    <div class="dashboard-area mt-20">
        @include('merchant.components.wallets.sandbox_crypto',compact("sandbox_crypto_wallets"))
    </div>
</div>
@endsection

@push('script')

@endpush
