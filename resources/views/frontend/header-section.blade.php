@extends('frontend.layouts.master')

@php
    $lang               = selectedLang();
    $system_default     = $default_language_code;
@endphp
@section('content')
    <!-- banner section -->
<section class="details-page-banner">
    <div class="container">
        <div class="banner-element">
            <div class="banner-element-one">
            </div>
            <div class="banner-element-two">
            </div>
        </div>
        <div class="row align-items-center mb-40-none">
            <div class="col-lg-6 mb-40">
                <div class="details-banner-wrapper">

                    <span class="section-sub-titel"><i class="{{ $parent->icon?->language?->$lang?->icon ?? $parent->icon?->language?->$system_default?->icon }}"></i> {{ $parent->title?->language?->$lang?->title ?? $parent->title?->language?->$system_default?->title}}</span>
                    <h2 class="title">{{ __($page_content->value->language->$lang->heading ?? $page_content->value->language->$system_default->heading) }}</h2>
                    <p class="sub-title">{{ __($page_content->value->language->$lang->sub_heading ?? $page_content->value->language->$system_default->sub_heading) }}</p>
                    <ul class="process-steps">
                        <h3 class="title">{{ __($page_content->value->language->$lang->process_step_title ?? $page_content->value->language->$system_default->process_step_title) }}</h3>
                        @php
                            $i = 1;
                        @endphp
                        @foreach ($page_content->value->items ?? [] as $item)
                            <li><span>{{  $i++ }}.</span> {{ $item->language->$lang->name ?? $item->language->$system_default->name}}</li>
                        @endforeach
                    </ul>
                    @php
                        $base_url = url('/')."/";
                        $section_url = $page_content->value->language->$lang->button_link ?? $page_content->value->language->$system_default->button_link;
                        $full_url = $base_url.$section_url;
                    @endphp

                    <div class="view-page-btn  pt-4">
                        <a href="{{$full_url}}" class="btn--base">{{ $page_content->value->language->$lang->button_name ?? $page_content->value->language->$system_default->button_name }}</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-40">
                <div class="details-banner-img text-center">
                    <img src="{{ get_image(@$page_content->value->images->section_image,'header-section') }}" alt="img">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Step Section -->
<section class="view-demo pb-80">
    <div class="container">
        <div class="view-demo-area">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="demo-header text-center">
                        <div class="banner-title">
                            <h2 class="title">{{ $page_content->value->language->$lang->step_title ?? $page_content->value->language->$system_default->step_title }}</h2>
                        </div>
                        <div class="sub-title text-center">
                            <h4>{{ $page_content->value->language->$lang->step_sub_title ?? $page_content->value->language->$system_default->step_sub_title }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="demo-img-wrapper">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="demo-element-1">
                            <img src="{{ get_image(@$page_content->value->images->step_image,'header-section') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>

<!-- Faq -->
<section class="faq-section pb-80">
    <div class="container">
        <div class="faq-element">
            <div class="faq-element-two">
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10 text-center">
                <div class="section-header">
                    <h2 class="section-title">{{ $faq_content->value->language->$lang->heading ?? $faq_content->value->language->$system_default->heading }}</h2>
                    <p>{{ $faq_content->value->language->$lang->sub_heading ?? $faq_content->value->language->$system_default->sub_heading }}</p>
                </div>
            </div>
        </div>
        @php
        $items = @$faq_content->value->items;
           $itemData   = (array) $items;
           if ($itemData != []) {
               $data = array_chunk($itemData, ceil(count($itemData) / 2));
               $part1 = $data[0];
               $part2 = $data[1];
           }
       @endphp
        <div class="row justify-content-center mb-30-none">
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="faq-wrapper">
                    @foreach ($part1 as $item)
                        <div class="faq-item">
                            <h6 class="faq-title"><span class="title">{{ __(@$item->language->$lang->question??@$item->language->$system_default->question) }}</span><span class="right-icon"></span></h6>
                            <div class="faq-content">
                                <p>{{ __(@$item->language->$lang->answer??@$item->language->$system_default->answer) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="faq-wrapper">
                    @foreach ($part2 as $item)
                        <div class="faq-item">
                            <h6 class="faq-title"><span class="title">{{ __(@$item->language->$lang->question??@$item->language->$system_default->question) }}</span><span class="right-icon"></span></h6>
                            <div class="faq-content">
                                <p>{{ __(@$item->language->$lang->answer??@$item->language->$system_default->answer) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

@endsection


@push("script")

@endpush
