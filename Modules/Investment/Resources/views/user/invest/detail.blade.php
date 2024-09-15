@extends('user.layouts.app')
@section('content')
    <div class="text-center">
        <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20 text-uppercase">{{ __('Investment Details') }}</p>
        <p class="mb-0 gilroy-medium text-gray-100 f-16 r-f-12 mt-2 tran-title p-inline-block">{{ __('Everything you need to know about your investment') }}</p>
    </div>

    <div class="d-flex align-items-center back-direction mt-24">
        <a href="{{ route('user.investment.list', 'active') }}" class="text-gray-100 f-16 leading-20 gilroy-medium d-inline-flex align-items-center position-relative back-btn">
            {!! svgIcons('left_angle') !!}
            <span class="ms-1 back-btn">{{ __('Back to list') }}</span>
        </a>
    </div> 

    <div class="invested-Profit-plan bg-white mt-12">
        <div class="plan_profit">
            <div class="row col-gap-20">
                <div class="col-xl-4">
                    <div class="inv-plan">
                        <p class="mb-0 f-16 leading-20 text-gray gilroy-medium">{{ __('Invest Plan') }}</p>
                        <div class="mb-0 d-flex gilroy-Semibold mt-2 gap-12">
                            <span class="f-26 leading-32 text-dark platinum">{{ optional($investment->investmentPlan)->name }}</span>
                            <span class="inv-status-badge f-11 leading-14 bg-{{ getBgColor($investment->status) }} text-white d-flex justify-content-center align-items-center align-self-center">{{ $investment->status }}</span>
                        </div>
                        <p class="mb-0 f-16 leading-20 text-gray-100 gilroy-medium mt-2">
                            {{ 
                                optional($investment->investmentPlan)->interest_time_frame . ' ' . investmentInterestRateType($investment->investmentPlan) . __(' for ') . optional($investment->investmentPlan)->term . ' ' . optional($investment->investmentPlan)->term_type 
                            }}
                        </p>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="invest_profit bg-white-50">
                        <p class="mb-0 f-14 leading-17 text-gray-100 gilroy-medium">{{ __('Invested & Profit') }}</p>
                        <p class="mb-0 f-22 leading-24 text-primary gilroy-Semibold mt-2">{{ formatNumber($investment->amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}</p>
                        <P class="mb-0 f-16 leading-20 text-dark l-sp mt-5p gilroy-medium">+{{ formatNumber($investment->estimate_profit, $investment->currency_id) . ' ' . optional($investment->currency)->code }}</P>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="invest_capital bg-white h-100 d-flex flex-column">
                        <p class="mb-0 f-14 leading-17 text-gray-100 gilroy-medium text-start">{{ optional($investment->investmentPlan)->capital_return_term == 'Term Basis' ? __('Received amount (with capital)') : __('Received amount') }}</p>
                        <p class="mb-0 f-22 leading-24 text-dark gilroy-Semibold mt-2 text-start">{{ formatNumber($investment->received_amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-24 inv-row-gaps">
        <div class="col-xl-4">
            <div class="inv-terms bg-white">
                <div class="inv-terms-header border-b-EF">
                    <p class="mb-0 f-18 leading-24 gilroy-Semibold text-dark">{{ __('Investment Terms') }}</p>
                </div>
                <div class="d-flex justify-content-between mt-24">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Capital return term') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ optional($investment->investmentPlan)->capital_return_term }}</p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Term duration') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ optional($investment->investmentPlan)->term . ' ' . Str::plural(optional($investment->investmentPlan)->term_type, optional($investment->investmentPlan)->term) }}</p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Interest') }} ({{ optional($investment->investmentPlan)->interest_time_frame }})</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ investmentInterestRateType($investment->investmentPlan) }}</p>
                </div>
                @if (optional($investment->investmentPlan)->investment_type == 'Range')
                    <div class="d-flex justify-content-between mt-20">
                        <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Minimum amount') }}</p>
                        <p class="mb-0 f-14 leading-17 gilroy-medium text-dark"> {{ formatNumber(optional($investment->investmentPlan)->amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}</p>
                    </div>
                    <div class="d-flex justify-content-between mt-20">
                        <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Maximum amount') }}</p>
                        <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ formatNumber(optional($investment->investmentPlan)->maximum_amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-xl-4">
            <div class="inv-terms bg-white">
                <div class="inv-terms-header border-b-EF">
                    <p class="mb-0 f-18 leading-24 gilroy-Semibold text-dark">{{ __('Investment Details') }}</p>
                </div>
                <div class="d-flex justify-content-between mt-24">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Invested Amount') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ formatNumber($investment->amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}</p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Net profit') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ formatNumber($investment->estimate_profit, $investment->currency_id) . ' ' . optional($investment->currency)->code }}</p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Payment method') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ optional($investment->paymentMethod)->name == 'Mts' ? __('Wallet') : optional($investment->paymentMethod)->name }}</p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Invest start at') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ dateFormat($investment->start_time) }}</p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Invest end at') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ dateFormat($investment->end_time) }}</p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Total Term') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ $investment->term_total }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="inv-terms bg-white">
                <div class="inv-terms-header border-b-EF">
                    <p class="mb-0 f-18 leading-24 gilroy-Semibold text-dark">{{ __('Investment Expenses') }}</p>
                </div>
                <div class="d-flex justify-content-between mt-24">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Invested at') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ dateFormat($investment->created_at) }}</p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">
                        {{ optional($investment->investmentPlan)->interest_time_frame }}
                        {{ 
                            optional($investment->investmentPlan)->capital_return_term == 'Term Basis'
                                ? __('profit (with capital)')
                                : __('profit') 
                        }}
                    </p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">
                        {{ 
                            optional($investment->investmentPlan)->capital_return_term == 'Term Basis'
                                ? formatNumber($investment->total / $investment->term_total, $investment->currency_id)
                                : formatNumber($investment->estimate_profit / $investment->term_total, $investment->currency_id) 
                        }}
                        {{ optional($investment->currency)->code }}
                    </p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Received amount') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ formatNumber($investment->received_amount, $investment->currency_id) . ' ' . optional($investment->currency)->code }}</p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Adjust profit') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ $investment->term_count . '/' . $investment->term_total }}</p>
                </div>
                <div class="d-flex justify-content-between mt-20">
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Payment reference') }}</p>
                    <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ $investment->uuid }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="settings-wrapper inv-details wrapper">
                <input type="radio" name="slider" checked="" id="cryp_exchange">
                <input type="radio" name="slider" id="cryp_buy">
                <input type="radio" name="slider" id="code">
                <nav>
                <label for="cryp_exchange" class="cryp_exchange"><span class="mb-0 text-center f-14 leading-17 gilroy-medium">{{ __('Profits') }}</span></label>
                <label for="cryp_buy" class="cryp_buy"><span class="mb-0 text-center f-14 leading-17 gilroy-medium">{{ __('Transfers') }}</span></label>
                <div class="settings-slider slider"></div>
                </nav>
                <div class="sliding-content-parent mt-3">
                    <div class="content content-1">
                        <div class="tab-pane fade show active" id="crypto_exchange1" role="tabpanel">
                            <div class="table-responsive table-scrolbar overflow-auto thin-scrollbar">
                                <table class="table table-p table-bordered">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="details">
                                                    <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-medium">{{ __('Details') }}</p>
                                                    <p class="mb-0 f-15 leading-18 text-primary gilroy-medium mt-2">{{ __('Investments') }}</p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="details pl-8rem">
                                                    <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-medium">{{ __('Date & Time') }}</p>
                                                    <p class="mb-0 f-15 leading-18 text-primary gilroy-medium mt-2">{{ dateFormat($investment->start_time) }}</p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="details">
                                                    <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-medium text-end">{{ __('Amount') }}</p>
                                                    <p class="mb-0 f-15 leading-18 text-primary gilroy-medium mt-2 text-end l-sp64">
                                                        + {{ formatNumber($investment->amount, $investment->currency_id) }}
                                                        {{ optional($investment->currency)->code }}
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                        @foreach ($profits as $profit)
                                            <tr>
                                                <td>
                                                    <div class="details">
                                                        <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-regular">{{ __('Details') }}</p>
                                                        <p class="mb-0 f-15 leading-18 text-dark gilroy-medium mt-2">{{ __('Profit Earn') }}
                                                    {{ investmentInterestRateType($investment->investmentPlan) }}</p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="details pl-8rem">
                                                        <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-regular">{{ __('Date & Time') }}</p>
                                                        <p class="mb-0 f-15 leading-18 text-dark gilroy-medium mt-2">{{ dateFormat($profit->calculated_at) }}</p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="details">
                                                        <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-regular text-end">{{ __('Amount') }}</p>
                                                        <p class="mb-0 f-15 leading-18 text-dark gilroy-medium mt-2 text-end l-sp64">
                                                            +{{ formatNumber($profit->amount, $investment->currency_id) }}
                                                            {{ optional($investment->currency)->code }}
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content content-2">
                        <div class="tab-pane fade show active" id="crypto_exchange2" role="tabpanel">
                            @if ($transfers->count() > 0)
                                <div class="table-responsive table-scrolbar overflow-auto thin-scrollbar">
                                    <table class="table table-p table-bordered">
                                        <tbody>
                                            @foreach ($transfers as $transfer)
                                                <tr>
                                                    <td>
                                                        <div class="details">
                                                            <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-medium">{{ __('Details') }}</p>
                                                            <p class="mb-0 f-15 leading-18 text-primary gilroy-medium mt-2">{{ $transfer->description }}</p>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="details pl-8rem">
                                                            <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-medium">{{ __('Date & Time') }}</p>
                                                            <p class="mb-0 f-15 leading-18 text-primary gilroy-medium mt-2">{{ dateFormat($transfer->created_at) }}</p>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="details">
                                                            <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-medium text-end">{{ __('Amount') }}</p>
                                                            <p class="mb-0 f-15 leading-18 text-primary gilroy-medium mt-2 text-end l-sp64">
                                                                +{{ formatNumber($transfer->amount, $investment->currency_id) }}
                                                                {{ optional($investment->currency)->code }}
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="notfound mt-16 bg-white p-4">
                                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-26">
                                        <div class="image-notfound">
                                            <img src="{{ asset('public/dist/images/not-found.png') }}" class="img-fluid">
                                        </div>
                                        <div class="text-notfound">
                                            <p class="mb-0 f-20 leading-25 gilroy-medium text-dark">{{ __('Sorry!') }} {{ __('No data found.') }}</p>
                                            <p class="mb-0 f-16 leading-24 gilroy-regular text-gray-100 mt-12">{{ __('The requested data does not exist for this feature overview.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
