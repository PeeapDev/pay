@extends('user.layouts.app')
@section('content')
    <div class="text-center" id="invest_list">
        <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20 text-uppercase">{{ __('Investment list') }}</p>
        <p class="mb-0 gilroy-medium text-gray-100 f-16 r-f-12 mt-2 tran-title p-inline-block">{{ __('List of all the investments you had or currently have ongoing') }}</p>
    </div>

    <div class="d-flex justify-content-between mt-24 r-mt-22 align-items-center">
        <div class="me-2 me-3">
            <div class="param-ref param-ref-withdraw filter-ref r-filter-ref w-135">
                <select name="status" class="select2 f-13" id="status" data-minimum-results-for-search="Infinity">
                    <option value="all" {{ $status == 'All' ? 'selected' : ''}}>{{ __('All') }}</option>
                    <option value="active" {{ $status == 'Active' ? 'selected' : ''}}>{{ __('Active') }}</option>
                    <option value="pending" {{ $status == 'Pending' ? 'selected' : ''}}>{{ __('Pending') }}</option>
                    <option value="completed" {{ $status == 'Completed' ? 'selected' : ''}}>{{ __('Completed') }}</option>
                    <option value="cancelled" {{ $status == 'Cancelled' ? 'selected' : ''}}>{{ __('Cancelled') }}</option>
                </select>
            </div>
        </div>

        <a href="{{ route('user.invest.create') }}" class="btn bg-primary text-light Add-new-btn w-176 addnew">
            <span class="f-14 gilroy-medium"> + {{ __('New Investment') }}</span>
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if($investments->count() > 0)
                <div class="table-responsive mt-23 table-scrolbar overflow-auto thin-scrollbar inv-table">
                    <table class="table investment-list-table yellow-table table-bordered border-bottom-last-child">
                        <thead>
                            <tr class="payment-parent-section-title td-p-20">
                                <th class="p-0 pb-10"><p class="mb-0 ml-20 f-13 leading-16 gilroy-regular text-gray-100">{{ __('Plan') }}</p></th>
                                <th class="p-0 pb-10"><p class="mb-0 ml-20 f-13 leading-16 gilroy-regular text-gray-100">{{ __('Invested Amount / Currency') }}</p></th>
                                <th class="p-0 pb-10"><p class="mb-0 ml-20 f-13 leading-16 gilroy-regular text-gray-100">{{ __('Start Date / End Date') }}</p></th>
                                <th class="p-0 pb-10"><p class="mb-0 ml-20 f-13 leading-16 gilroy-regular text-gray-100">{{ __('Net Profit / Total') }}</p></th>
                                <th class="p-0 pb-10"><p class="mb-0 ml-20 f-13 leading-16 gilroy-regular text-gray-100">{{ __('Status') }}</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($investments as $investment)
                                <tr class="bg-white">                                               
                                    <td>
                                        <div class="td-p-20">
                                            <p class="mb- d-flex"><a href="{{ route('user.investment.details', $investment->id) }}" class="mb-0 f-16 leading-20 text-dark gilroy-medium cursor-pointer">{{ getColumnValue($investment->investmentPlan, 'name', '') }} </a></p>
                                            <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-regular mt-2">
                                                {{  
                                                    getColumnValue($investment->investmentPlan, 'interest_time_frame', '') . ' ' . investmentInterestRateType($investment->investmentPlan) . __(' for ') . getColumnValue($investment->investmentPlan, 'term', '') . ' ' . getColumnValue($investment->investmentPlan, 'term_type', '') 
                                                }}
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="td-p-20">
                                        <p class="mb-0 f-16 leading-20 text-dark gilroy-medium l-sp64">{{ formatNumber($investment->amount, $investment->currency_id) }}</p>
                                        <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-regular mt-2">{{ getColumnValue($investment->currency, 'code', '') }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="td-p-20">
                                            <p class="mb-0 f-16 leading-20 text-dark gilroy-medium">{{ dateFormat($investment->start_time) }}</p>
                                            <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-regular mt-2">{{ dateFormat($investment->end_time) }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="td-p-20">
                                                <p class="mb-0 f-16 leading-20 text-dark gilroy-medium l-sp64">{{ formatNumber($investment->estimate_profit, $investment->currency_id) }}</p>
                                                <p class="mb-0 f-13 leading-16 text-gray-100 gilroy-regular mt-2 l-sp64">{{ formatNumber($investment->total, $investment->currency_id) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="td-p-20">
                                                <p class="mb-0 f-14 leading-14 gilroy-medium l-sp64 inv-status-badge bg-{{ getBgColor($investment->status) }} text-white">{{ $investment->status }}</p>
                                            </div>
                                            <div class="arrow-hover">
                                                <a href="{{ route('user.investment.details', $investment->id) }}" class="">
                                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.5312 1.52925C3.27085 1.7896 3.27085 2.21171 3.5312 2.47206L7.0598 6.00065L3.5312 9.52925C3.27085 9.7896 3.27085 10.2117 3.5312 10.4721C3.79155 10.7324 4.21366 10.7324 4.47401 10.4721L8.47401 6.47205C8.73436 6.21171 8.73436 5.7896 8.47401 5.52925L4.47401 1.52925C4.21366 1.2689 3.79155 1.2689 3.5312 1.52925Z" fill="#9998A0"/>
                                                    </svg>
                                                </a>
                                            </div>
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
            <div class="mt-3">
                {{ $investments->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('Modules/Investment/Resources/assets/js/user/invest.min.js') }}" type="text/javascript"></script>
@endpush

