@extends('frontend.layouts.master')
@php
    $lang = selectedLang();
    $system_default    = $default_language_code;
    $pricing_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::PRICING_SECTION);
    $pricing = App\Models\Admin\SiteSections::getData($pricing_slug)->first();
    $trx_tables = App\Models\Admin\TransactionSetting::where('status',1)->get();


@endphp

@push("css")

@endpush

@section('content')

    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            Banner
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
    <section class="pricing-page ptb-80">
        <div class="container">
            <div class="row align-items-center mb-40-none">
                <div class="col-lg-6 col-md-6 mb-40">
                    <div class="pricung-page-banner">
                        <h2 class="title">{{ __($pricing->value->language->$lang->heading ?? $pricing->value->language->$system_default->heading) }}</h2>
                        <p>{{ __($pricing->value->language->$lang->sub_heading ?? $pricing->value->language->$system_default->sub_heading) }}</p>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 mb-40">
                    <div class="banner-img">
                        <img src="{{ get_image(@$pricing->value->images->image,'site-section') }}" alt="img">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            End Header
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
    @foreach ($trx_tables ?? [] as $item)
        @php
            // Replace hyphens with underscores in the 'slug' key
            $slug = str_replace('-', '_', $item->slug);
            $title_name = $slug."_"."title";
            $sub_title_name = $slug."_"."sub_title";
        @endphp


        <section class="charge-deatiks-section @if($loop->last) ptb-60 @else pt-60 @endif">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 col-lg-10">
                        <div class="charge-title">
                            <h3 class="title">{{ __($pricing->value->language->$lang->$title_name ?? $pricing->value->language->$system_default->$title_name) }}</h3>
                            <p>{{ __($pricing->value->language->$lang->$sub_title_name ?? $pricing->value->language->$system_default->$sub_title_name) }}:</p>
                        </div>
                    </div>
                </div>
                <div class="charge-tabale">
                    <div class="row">
                        @php
                        if($item->slug == 'pay-link' || $item->slug == 'gift_card'){
                            $col = 'col-xl-6 col-lg-6';
                        }else{
                            $col = $item->agent_profit == true ? 'col-xl-6 col-lg-6': 'col-xl-4 col-lg-4 ';
                        }
                    @endphp
                        <div class="{{$col}} mb-20">
                            <div class="item-title">
                                <h4 class="title text--base mb-3">{{ __("Charges") }}</h4>
                                <p class="item-name">{{ __("Fixed Charge") }}: <span class="item-value">{{ get_amount($item->fixed_charge,null,get_wallet_precision()) }}</span></p>
                                <p class="item-name">{{ __("Percent Charge") }}: <span class="item-value">{{ get_amount($item->percent_charge,null,get_wallet_precision()) }}%</span></p>
                            </div>
                        </div>
                        <div class="{{$col}} mb-20">
                            <div class="item-title">
                                <h4 class="title text--base mb-3">{{ __("Range") }}</h4>
                                <p class="item-name">{{ __("Minimum amount") }}: <span class="item-value">{{ get_amount($item->min_limit,null,get_wallet_precision()) }}</span></p>
                                <p class="item-name">{{ __("Maximum amount") }}: <span class="item-value">{{ get_amount($item->max_limit,null,get_wallet_precision()) }}</span></p>
                            </div>
                        </div>
                        @if($item->slug != 'pay-link' && $item->slug != 'gift_card')
                        <div class="{{ $item->agent_profit == true ? 'col-xl-6 col-lg-6': 'col-xl-4 col-lg-4 '}} mb-20">
                            <div class="item-title">
                                <h4 class="title text--base mb-3">{{ __("Limit") }} <span class="small text--base">(<small>{{ __("Execute if the value is greater than zero") }}</small>)</span></h4>
                                <p class="item-name">{{ __("Daily Limit") }}: <span class="item-value">{{get_amount($item->daily_limit,null,get_wallet_precision()) }}</span></p>
                                <p class="item-name">{{ __("Monthly Limit") }}: <span class="item-value">{{ get_amount($item->monthly_limit,null,get_wallet_precision()) }}</span></p>
                            </div>
                        </div>
                        @endif
                        @if( $item->agent_profit == true)
                        <div class="col-xl-6 col-lg-6 mb-20">
                            <div class="item-title">
                                <h4 class="title text--base mb-3">{{ __("Agent Profits") }}</h4>
                                <p class="item-name">{{ __("Fixed Commissions") }}: <span class="item-value">{{ get_amount($item->agent_fixed_commissions,null,get_wallet_precision()) }}</span></p>
                                <p class="item-name">{{ __("Percent Commissions") }}: <span class="item-value">{{ get_amount($item->agent_percent_commissions,null,get_wallet_precision()) }}</span></p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endforeach
@endsection


@push("script")

@endpush
