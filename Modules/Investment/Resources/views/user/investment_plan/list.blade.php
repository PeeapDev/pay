@extends('user.layouts.app')

@section('content')
    <div class="text-center inv-title px-326">
        <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20 text-uppercase">{{ __('Investment plans') }}</p>
        @if (count($plans) > 0)
            <p class="mb-0 gilroy-medium text-gray-100 f-16 leading-26 r-f-12 mt-2 inv-title tran-title ">{{ __('Here is our several investment plans. You can invest daily, weekly or monthly and get higher returns in your investment.') }}</p>
        @endif

    </div>
    <div class="row">
        @forelse ($plans as $key => $plan)
            <div class="col-xl-4 col-top">
                <div class="invest-plan plan-diamond bg-white bdr-8">
                    
                    <p class="text-dark d-title f-20 leading-24 gilroy-Semibold text-uppercase text-center">{{ $plan->name }}</p>
                     @if (settings('plan_description') == 'Yes')
                        <p class="mb-0 f-12 leading-17 gilroy-regular text-gray-100">{{ $plan->description }}</p>
                    @endif

                    <div class="mt-14 profit-duration d-flex justify-content-between bg-white-100">
                        <div class="daily-profit">
                            <p class="mb-0 f-13 leading-16 gilroy-medium text-dark">{{ $plan->interest_time_frame }} {{ __('Profit') }}</p>
                            <p class="mb-0 f-20 leading-24 text-primary gilroy-Semibold mt-2">{{ investmentInterestRateType($plan) }}</p> 
                        </div>
                        <div class="duration text-end">
                            <p class="mb-0 f-13 leading-16 gilroy-medium text-dark">{{ __('Duration') }}</p>
                            <p class="mb-0 f-20 leading-24 text-primary gilroy-Semibold mt-2">{{ $plan->term }} {{ Str::plural($plan->term_type, $plan->term) }}</p>
                        </div>
                    </div>

                    <div class="min-max-amount border-b-EF d-flex justify-content-between">
                        @if ($plan->investment_type == 'Fixed')
                            <div class="min-max-left">
                                <p class="mb-0 f-13 leading-16 gilroy-medium text-gray-100">{{ __('Investment Amount') }}</p>
                                <p class="mb-0 f-16 leading-20 gilroy-Semibold text-dark mt-2">{{ formatNumber($plan->amount, $plan->currency_id) }} {{ optional($plan->currency)->code }}</p>
                            </div>
                         @else
                            <div class="min-max-left">
                                <p class="mb-0 f-13 leading-16 gilroy-medium text-gray-100">{{ __('Min Amount') }}</p>
                                <p class="mb-0 f-16 leading-20 gilroy-Semibold text-dark mt-2">{{ formatNumber($plan->amount, $plan->currency_id) }} {{ optional($plan->currency)->code }}</p>
                            </div>
                            <div class="min-max-right">
                                <p class="mb-0 f-13 leading-16 gilroy-medium text-gray-100">{{ __('Max Amount') }}</p>
                                <p class="mb-0 f-16 leading-20 gilroy-Semibold text-dark mt-2">{{ formatNumber($plan->maximum_amount, $plan->currency_id) }} {{ optional($plan->currency)->code }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="terms mt-20">
                        <div class="d-flex justify-content-between">
                            <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Return Term') }}</p>
                            <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ $plan->term * termCount($plan->term_type, $plan->interest_time_frame) }}</p>
                        </div>
                        <div class="d-flex justify-content-between mt-16">
                            <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Withdraw After Matured') }}</p>
                            <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ $plan->withdraw_after_matured }}</p>
                        </div>
                        <div class="d-flex justify-content-between mt-16">
                            <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Capital Return') }}</p>
                            <p class="mb-0 f-14 leading-17 gilroy-medium text-dark">{{ str_replace('_', ' ', $plan->capital_return_term) }}</p>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="{{ route('user.invest.create', ['plan_id' => $plan->id]) }}" class="investment-btn green-btn cursor-pointer bg-primary d-flex justify-content-center mt-24 b-none">
                            <span class="f-14 leading-20 gilroy-regular inv-btn text-white">{{ __('Invest Now') }}</span>
                        </a>
                    </div> 
                </div>
            </div>
        @empty
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
        @endforelse
    </div>
@endsection
