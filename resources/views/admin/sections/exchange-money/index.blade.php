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
    ], 'active' => __("Exchange Money Logs")])
@endsection

@section('content')
<div class="table-area">
    <div class="table-wrapper">
        <div class="table-header">
            <h5 class="title">{{ $page_title }}</h5>
            @if(count($transactions) > 0)
            <div class="table-btn-area">
                <a href="{{ setRoute('admin.exchange.money.export.data') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
            </div>
        @endif
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __("TRX ID") }}</th>
                        <th>{{ __("User Type") }}</th>
                        <th>{{ __("User Email") }}</th>
                        <th>{{ __("From Country") }}</th>
                        <th>{{ __("To Country") }}</th>
                        <th>{{ __("Exchange Amount") }}</th>
                        <th>{{ __("Exchange Rate") }}</th>
                        <th>{{ __("Exchangeable Amount") }}</th>
                        <th>{{ __("charge") }}</th>
                        <th>{{ __("Payable") }}</th>
                        <th>{{ __(("Status")) }}</th>
                        <th>{{ __("Time") }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions ?? []  as $key => $item)
                        <tr>
                            <td>{{ $item->trx_id }}</td>
                            <td>
                                @if($item->user_id != null)
                                     {{ __("USER") }}
                                @elseif($item->agent_id != null)
                                     {{ __("AGENT") }}
                                @elseif($item->merchant_id != null)
                                     {{ __("MERCHANT") }}
                                @endif
                            </td>
                            <td>
                                @if($item->user_id != null)
                                <a href="{{ setRoute('admin.users.details',$item->creator->username) }}">{{ $item->creator->email }}</a>
                                @elseif($item->agent_id != null)
                                <a href="{{ setRoute('admin.agents.details',$item->creator->username) }}">{{ $item->creator->email }}</a>
                                @elseif($item->merchant_id != null)
                                <a href="{{ setRoute('admin.merchants.details',$item->creator->username) }}">{{ $item->creator->email }}</a>
                                @endif
                            </td>
                            <td>
                               <span>{{ $item->details->charges->from_wallet_country}}</span>
                            </td>
                            <td>
                               <span>{{ $item->details->charges->to_wallet_country}}</span>
                            </td>
                            <td>{{ get_amount($item->details->charges->request_amount,$item->details->charges->request_currency,get_wallet_precision($item->creator_wallet->currency)) }}</td>
                            <td>{{ get_amount(1,$item->details->charges->request_currency) ." = ".get_amount($item->details->charges->exchange_rate,$item->details->charges->exchange_currency,$item->details->charges->r_precision_digit??2) }}</td>
                            <td>{{ get_amount($item->details->charges->exchange_amount,$item->details->charges->exchange_currency,$item->details->charges->r_precision_digit??2) }}</td>
                            <td>{{ get_amount($item->charge->total_charge,$item->details->charges->request_currency,get_wallet_precision($item->creator_wallet->currency)) }}</td>
                            <td>{{ get_amount($item->details->charges->payable,$item->details->charges->request_currency,get_wallet_precision($item->creator_wallet->currency)) }}</td>
                            <td>
                                <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span>
                            </td>
                            <td>{{ $item->created_at->format('d-m-y h:i:s A') }}</td>

                        </tr>
                    @empty
                         @include('admin.components.alerts.empty',['colspan' => 12])
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ get_paginate($transactions) }}
    </div>
</div>
@endsection

@push('script')

@endpush
