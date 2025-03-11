@extends('agent.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("agent.dashboard"),
        ]
    ], 'active' => __("Wallets")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        @include('agent.components.wallets.fiat',compact("fiat_wallets"))
    </div>
    <div class="dashboard-area mt-20">
        @include('agent.components.wallets.crypto',compact("crypto_wallets"))
    </div>
</div>
@endsection

@push('script')

@endpush
