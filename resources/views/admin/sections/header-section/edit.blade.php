@php
    $default_lang_code = language_const()::NOT_REMOVABLE;
    $system_default_lang = get_default_language_code();
    $languages_for_js_use = $languages->toJson();

@endphp

@extends('admin.layouts.master')

@push('css')
    <link rel="stylesheet" href="{{ asset('public/backend/css/fontawesome-iconpicker.css') }}">
    <style>
        .fileholder {
            min-height: 374px !important;
        }

        .fileholder-files-view-wrp.accept-single-file .fileholder-single-file-view,.fileholder-files-view-wrp.fileholder-perview-single .fileholder-single-file-view{
            height: 330px !important;
        }
    </style>
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
    ], 'active' => __("Setup Section")])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __($page_title) }}</h6>
            <div class="table-btn-area">
                @include('admin.components.link.custom',[
                    'href'          => route('admin.setup.header.sections.index',$slug),
                    'class'         => "btn btn--base",
                    'text'          =>__("Back"),
                    'permission'    => "admin.setup.header.sections.index",
                ])
            </div>
        </div>
        <div class="card-body">
            <form class="card-form" action="{{ setRoute('admin.setup.header.sections.update',[$slug,$data->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method("PUT")
                {{-- only for company type --}}
                <input type="hidden" name="page" value="{{ $data->slug ?? null }}">
                {{-- only for company type --}}
                <div class="row justify-content-center mb-10-none">
                    <div class="col-xl-12 col-lg-12">
                        <div class="product-tab">
                            <nav>
                                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                    <button class="nav-link @if (get_default_language_code() == language_const()::NOT_REMOVABLE) active @endif" id="english-tab" data-bs-toggle="tab" data-bs-target="#english" type="button" role="tab" aria-controls="english" aria-selected="false">English</button>
                                    @foreach ($languages as $item)
                                        <button class="nav-link @if (get_default_language_code() == $item->code) active @endif" id="{{$item->name}}-tab" data-bs-toggle="tab" data-bs-target="#{{$item->name}}" type="button" role="tab" aria-controls="{{ $item->name }}" aria-selected="true">{{ $item->name }}</button>
                                    @endforeach
                                </div>
                            </nav>
                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane @if (get_default_language_code() == language_const()::NOT_REMOVABLE) fade show active @endif" id="english" role="tabpanel" aria-labelledby="english-tab">
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "titleS")." *",
                                            'name'      => $default_lang_code . "_title",
                                            'value'     => old($default_lang_code . "_title",$data->title->language->$default_lang_code->title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __("Icon")."*",
                                            'name'      => $default_lang_code . "_icon",
                                            'value'     => old($default_lang_code . "_icon",$data->icon->language->$default_lang_code->icon ?? ""),
                                            'class'     => "form--control icp icp-auto iconpicker-element iconpicker-input",
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                           'label'     => __( "web_sub_title")." *",
                                           'name'      => $default_lang_code . "_sub_title",
                                           'value'     => old($default_lang_code . "_sub_title",$data->sub_title->language->$default_lang_code->sub_title ?? "")
                                        ])
                                    </div>

                                </div>

                                @foreach ($languages as $item)
                                    @php
                                        $lang_code = $item->code;
                                    @endphp
                                    <div class="tab-pane @if (get_default_language_code() == $item->code) fade show active @endif" id="{{ $item->name }}" role="tabpanel" aria-labelledby="english-tab">
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "titleS")." *",
                                                'name'      => $lang_code . "_title",
                                                'value'     => old($lang_code . "_title",$data->title->language->$lang_code->title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __("Icon")."*",
                                                'name'      => $lang_code . "_icon",
                                                'value'     => old($lang_code . "_icon",$data->icon->language->$lang_code->icon ?? ""),
                                                'class'     => "form--control icp icp-auto iconpicker-element iconpicker-input",
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                               'label'     => __( "web_sub_title")." *",
                                               'name'      => $lang_code . "_sub_title",
                                               'value'     => old($lang_code . "_sub_title",$data->sub_title->language->$lang_code->sub_title ?? "")
                                            ])
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.button.form-btn',[
                            'class'         => "w-100 btn-loading",
                            'text'          => __("Submit"),
                            'permission'    => "admin.setup.header.sections.store"
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>
    @if($slug != global_const()::COMPANY)
        <div class="custom-card mt-5">
            <div class="card-header">
                <h6 class="title">{{ __("Page Contents") }}</h6>
            </div>
            <div class="card-body">
                <div class="dashboard-area">
                    <div class="dashboard-item-area">

                        <div class="row">
                                <div class="col-lg-6 col-md-6 col-12 mb-15">
                                    <a href="{{ setRoute('admin.setup.header.sections.page.index',[$slug,$data->id,slug($data->title->language->$default_lang_code->title)]) }}" class="d-block">
                                        <div class="dashbord-item border">
                                            <div class="dashboard-content">
                                                <div class="left">
                                                    <h6 class="title">{{ __("Page Contents") }}</h6>
                                                    <div class="user-info">
                                                        <h2 class="user-count">{{ @$onboard_screens_user }}</h2>
                                                    </div>
                                                </div>
                                                <div class="right">
                                                    <div class="chart" id="page_content" data-percent="100"><span>100%</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12 mb-15">
                                    <a href="{{ setRoute('admin.setup.header.sections.faq.index',[$slug,$data->id,slug($data->title->language->$default_lang_code->title)]) }}" class="d-block">
                                        <div class="dashbord-item border">
                                            <div class="dashboard-content">
                                                <div class="left">
                                                    <h6 class="title">{{ __("FAQ Contents") }}</h6>
                                                    <div class="user-info">
                                                        <h2 class="user-count">{{ @$onboard_screens_agent }}</h2>
                                                    </div>
                                                </div>
                                                <div class="right">
                                                    <div class="chart" id="faq_content" data-percent="100"><span>100%</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script')
 <script src="{{ asset('public/backend/js/fontawesome-iconpicker.js') }}"></script>
<script>
    // icon picker
    $('.icp-auto').iconpicker();
</script>

<script>
    $(function() {
        $('#page_content').easyPieChart({
            size: 80,
            barColor: '#10c469',
            scaleColor: false,
            lineWidth: 5,
            trackColor: '#10c4695a',
            lineCap: 'circle',
            animate: 3000
        });
        $('#faq_content').easyPieChart({
            size: 80,
            barColor: '#10c469',
            scaleColor: false,
            lineWidth: 5,
            trackColor: '#10c4695a',
            lineCap: 'circle',
            animate: 3000
        });
        $('#merchant_onboard').easyPieChart({
            size: 80,
            barColor: '#10c469',
            scaleColor: false,
            lineWidth: 5,
            trackColor: '#10c4695a',
            lineCap: 'circle',
            animate: 3000
        });
    });
</script>
@endpush
