@extends('admin.layouts.master')
@section('title', __('Investment Detail'))
@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('Modules/Investment/Resources/assets/css/admin/investment_detail.min.css') }}">
@endsection
@section('page_content')
    <div class="my-30">
        <div class="row">
            <!-- Page  title start -->
            <div class="col-md-12 col-xl-12" id="investment_detail">
                <div class="box">
                    <div class="box-body p-box-body">
                        <div class="panel">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-10 col-lg-8">
                                        <div class="d-flex-column">
                                            <div class="d-flex-row">
                                                <p class="w-600 font-24 c-i-black mb-0">
                                                    {{ optional($investment->investmentPlan)->name }}</p>

                                                @if ($investment->status == 'Active')
                                                    <div class="active-d ml-12 text-center">
                                                        <span class="w-600 font-12 c-i-green bg-active-status">{{ $investment->status }}</span>
                                                    </div>

                                                @elseif ($investment->status == 'Cancelled')
                                                    <div class="bg-cancel-status ml-12 text-center">
                                                        <span class="w-600 font-12 bg-active-status">{{ $investment->status }}</span>
                                                    </div>

                                                @elseif ($investment->status == 'Pending')
                                                    <div class="bg-pending-status ml-12 text-center">
                                                        <span class="w-600 font-12 bg-active-status">{{ $investment->status }}</span>
                                                    </div>
                                                @else
                                                    <div class="bg-complete-status ml-12 text-center"><span
                                                            class="w-600 font-12 bg-active-status">{{ $investment->status }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="c-i-gray font-16 w-600 mt-1">
                                                    {{ optional($investment->investmentPlan)->interest_time_frame .
                                                        ' ' .
                                                        investmentInterestRateType($investment->investmentPlan) .
                                                        __(' for ') .
                                                        optional($investment->investmentPlan)->term .
                                                        ' ' .
                                                        optional($investment->investmentPlan)->term_type }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($investment->status == 'Pending')
                                        <div class="col-md-2 col-lg-4">
                                            <div class="d-flex-row right-btn-side">
                                                <a href="" id="approve" data-id="{{ $investment->id }}"
                                                    data-status="Active"
                                                    class="font-14 approve-p bg-blue"><span>{{ __('Approve') }}</span></a>
                                                <a href="" id="cancel" data-id="{{ $investment->id }}"
                                                    data-status="Cancelled"
                                                    class="font-14 approve-p ml-12 btn-theme-danger btn"><span>{{ __('Cancel') }}</span></a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex-row d-justify-content-between">
                                            <div class="mt-27 box-dm bg-boxone">
                                                <p class="font-15 w-600 c-i-black mb-0 text-center">
                                                    {{ __('Invested & Profit') }}</p>
                                                <p class="mb-0 mt-1 text-center"><span
                                                        class="w-600 font-30 c-i-blue">{{ formatNumber($investment->amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}</span><span
                                                        class="w-600 font-20 c-i-light-blue">+
                                                        {{ formatNumber($investment->estimate_profit, $investment->currency_id) . ' ' . optional($investment->currency)->code }}
                                                    </span></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex-row d-justify-content-between">
                                            <div class="mt-27 box-dm bg-boxtwo">
                                                <p class="font-15 w-600 c-i-black mb-0 text-center">
                                                    {{ optional($investment->investmentPlan)->capital_return_term == 'Term Basis' ? __('Received amount (with capital)') : __('Received amount') }}
                                                </p>
                                                <p class="mb-0 mt-1 text-center"><span
                                                        class="w-600 font-30 c-i-blue">{{ formatNumber($investment->received_amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex-row row mb-20">
                    <div class="col-md-4 col-xs-12">
                        <div class="thumbnail bg-white rounded">
                            <div class="caption">
                                <div class="box-div-one">
                                    <p class="mb-unset font-16 w-600 c-i-black">{{ __('Investment Terms') }}</p>
                                    <div class="div-content d-flex-row d-justify-content-between mt-24">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Capital return term') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">{{ optional($investment->investmentPlan)->capital_return_term }}</p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Term duration') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ optional($investment->investmentPlan)->term . ' ' . Str::plural(optional($investment->investmentPlan)->term_type, optional($investment->investmentPlan)->term) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Interest') }}
                                                ({{ optional($investment->investmentPlan)->interest_time_frame }})</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ investmentInterestRateType($investment->investmentPlan) }}</p>
                                        </div>
                                    </div>
                                    @if (optional($investment->investmentPlan)->investment_type == 'Range')
                                        <div class="row mt-9">
                                            <div class="col-md-12 col-xs-12">
                                                <div class="borders-bottom"></div>
                                            </div>
                                        </div>
                                        <div class="div-content d-flex-row d-justify-content-between mt-9">
                                            <div>
                                                <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Minimum amount') }}</p>
                                            </div>
                                            <div>
                                                <p class="mb-unset font-14 c-i-black w-600">
                                                    {{ formatNumber(optional($investment->investmentPlan)->amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row mt-9">
                                            <div class="col-md-12 col-xs-12">
                                                <div class="borders-bottom"></div>
                                            </div>
                                        </div>
                                        <div class="div-content d-flex-row d-justify-content-between mt-9">
                                            <div>
                                                <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Maximum amount') }}</p>
                                            </div>
                                            <div>
                                                <p class="mb-unset font-14 c-i-black w-600">
                                                    {{ formatNumber(optional($investment->investmentPlan)->maximum_amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <div class="thumbnail bg-white rounded">
                            <div class="caption">
                                <div class="box-div-one">
                                    <p class="mb-unset font-16 w-600 c-i-black">{{ __('Investment Details') }}</p>
                                    <div class="div-content d-flex-row d-justify-content-between mt-24">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Invested Amount') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ formatNumber($investment->amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Net profit') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ formatNumber($investment->estimate_profit, $investment->currency_id) . ' ' . optional($investment->currency)->code }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Payment method') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ optional($investment->paymentMethod)->name == 'Mts' ? __('Wallet') : optional($investment->paymentMethod)->name }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Invest start at') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ dateFormat($investment->start_time) }}</p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Invest end at') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ dateFormat($investment->end_time) }}</p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Total Term') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">{{ $investment->term_total }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <div class="thumbnail bg-white rounded">
                            <div class="caption">
                                <div class="box-div-one">
                                    <p class="mb-unset font-16 w-600 c-i-black">{{ __('Investment Expenses') }}</p>
                                    <div class="div-content d-flex-row d-justify-content-between mt-24">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Invested at') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ dateFormat($investment->created_at) }}</p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">
                                                {{ optional($investment->investmentPlan)->interest_time_frame }}
                                                {{ optional($investment->investmentPlan)->capital_return_term == 'Term Basis'
                                                    ? __('profit (with capital)')
                                                    : __('profit') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ optional($investment->investmentPlan)->capital_return_term == 'Term Basis'
                                                    ? formatNumber($investment->total / $investment->term_total, $investment->currency_id)
                                                    : formatNumber($investment->estimate_profit / $investment->term_total, $investment->currency_id) }}
                                                {{ optional($investment->currency)->code }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Received amount') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ formatNumber($investment->received_amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Adjust profit') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">
                                                {{ $investment->term_count . '/' . $investment->term_total }}</p>
                                        </div>
                                    </div>
                                    <div class="row mt-9">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="borders-bottom"></div>
                                        </div>
                                    </div>
                                    <div class="div-content d-flex-row d-justify-content-between mt-9">
                                        <div>
                                            <p class="mb-unset font-14 w-600 c-i-gray">{{ __('Payment reference') }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-unset font-14 c-i-black w-600">{{ $investment->uuid }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="tabbable-panel">
                        <div class="tabbable-line">
                            <ul class="nav nav-pills d-flex-row d-justify-content-center" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                  <button class="nav-link active" id="Profits-tab" data-bs-toggle="pill" data-bs-target="#Profits" type="button" role="tab" aria-controls="Profits" aria-selected="true">{{ __('Profits') }}</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                  <button class="nav-link" id="Transfers-tab" data-bs-toggle="pill" data-bs-target="#Transfers" type="button" role="tab" aria-controls="Transfers" aria-selected="false">{{ __('Transfers') }}</button>
                                </li>
                            </ul>
                            <ul class="d-flex-row d-justify-content-center">
                                <div class="borders-bottoms-tab"></div>
                            </ul>
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="Profits" role="tabpanel" aria-labelledby="Profits-tab">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="t-heads d-flex-row d-justify-content-between">
                                                <div>
                                                    <p class="mb-unset font-14 w-600">{{ __('Details') }}</p>
                                                </div>
                                                <div>
                                                    <p class="mb-unset font-14 w-600">{{ __('Date & Time') }}</p>
                                                </div>
                                                <div>
                                                    <p class="mb-unset font-14 w-600">{{ __('Amount') }}</p>
                                                </div>
                                            </div>
                                            <div class="t-bodys d-flex-row d-justify-content-between">
                                                <div>
                                                    <p class="mb-unset font-14 w-600 c-i-black">{{ __('Investments') }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="mb-unset font-14 w-600 c-i-black ml-active">
                                                        {{ dateFormat($investment->start_time) }}</p>
                                                </div>
                                                <div>
                                                    <p class="mb-unset font-14 w-600 c-i-green">
                                                        +{{ formatNumber($investment->amount, $investment->currency_id) }}
                                                        {{ optional($investment->currency)->code }}</p>
                                                </div>
                                            </div>
                                            @foreach ($profits as $profit)
                                                <div class="borders-bottom"></div>
                                                <div class="t-bodys d-flex-row d-justify-content-between">
                                                    <div>
                                                        <p class="mb-unset font-14 c-i-gray">{{ __('Profit Earn') }}
                                                            {{ investmentInterestRateType($investment->investmentPlan) }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p class="mb-unset font-14 c-i-gray">
                                                            {{ dateFormat($profit->calculated_at) }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="mb-unset font-14 c-i-gray">
                                                            +{{ formatNumber($profit->amount, $investment->currency_id) }}
                                                            {{ optional($investment->currency)->code }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="Transfers" role="tabpanel" aria-labelledby="Transfers-tab">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="t-heads d-flex-row d-justify-content-between">
                                                <div>
                                                    <p class="mb-unset font-14 w-600">{{ __('Details') }}</p>
                                                </div>
                                                <div>
                                                    <p class="mb-unset font-14 w-600">{{ __('Date & Time') }}</p>
                                                </div>
                                                <div>
                                                    <p class="mb-unset font-14 w-600">{{ __('Amount') }}</p>
                                                </div>
                                            </div>
                                            @forelse($transfers as $transfer)
                                                <div class="t-bodys d-flex-row d-justify-content-between">
                                                    <div>
                                                        <p class="mb-unset font-14 c-i-gray">{{ $transfer->description }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p class="mb-unset font-14 c-i-gray">
                                                            {{ dateFormat($transfer->created_at) }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="mb-unset font-14 c-i-gray">
                                                            +{{ formatNumber($transfer->amount, $investment->currency_id) }}
                                                            {{ optional($investment->currency)->code }}</p>
                                                    </div>
                                                </div>
                                                @if (!$loop->last)
                                                    <div class="borders-bottom"></div>
                                                @endif
                                            @empty
                                                <div class="t-bodys text-center">
                                                    <img src="{{ asset('public/dist/images/not-found.png') }}"
                                                        alt="notfound">
                                                    <p class="mt-4">{{ __('Sorry!No transfers found.') }} </p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
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
    <script type="text/javascript">
        'use strict';
        var url = "{{ route('investment.update') }}";
        var alertTitle = "{{ __('Are you sure?') }}";
        var alertConfirm = "{{ __('Yes, change status!') }}";
        var alertCancel = "{{ __('Cancel') }}";
        var failedText = "{{ __('Failed!') }}";
        var waitText = "{{ __('Please Wait') }}";
        var waitingText = "{{ __('Waiting') }}";
        var userId = "{{ $investment->user_id }}";
        var currencyId = "{{ $investment->currency_id }}";
    </script>
    <script src="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('Modules/Investment/Resources/assets/js/admin/investment.min.js') }}" type="text/javascript"></script>
@endpush
