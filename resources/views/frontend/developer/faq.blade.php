@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
    $system_default    = $default_language_code;
    $faq_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::DEVELOPER_FAQ_SECTION);
    $faq = App\Models\Admin\SiteSections::getData( $faq_slug)->first();
@endphp

@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-30">{{ __($faq->value->language->$lang->heading ?? $faq->value->language->$system_default->heading) }}</h1>
        <div class="row">
            <div class="col-lg-8">
                @if(isset($faq->value->items))
                @php
                    $sl = 1;
                @endphp
                @foreach($faq->value->items ?? [] as $key => $item)
                <h4 class="mb-10">{{ $sl++ }}. {{ __($item->language->$lang->question ?? $item->language->$system_default->question) }}</h4>
                <p class="ps-4 mb-40">{{ __($item->language->$lang->answer ?? $item->language->$system_default->answer) }}</p>
                @endforeach
                @endif
            </div>
        </div>
        <code class="mt-60 d-block highlight">{{ __($faq->value->language->$lang->bottom_text ?? $faq->value->language->$system_default->bottom_text) }}</code>
    </div>
    <div class="page-change-area">
        <div class="navigation-wrapper">
            <a href="{{ setRoute('developer.examples') }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __("Examples") }}</a>
            <a href="{{setRoute('developer.support') }}" class="right">{{ __("Support") }} <i class="las la-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>
@endsection


@push("script")

@endpush
