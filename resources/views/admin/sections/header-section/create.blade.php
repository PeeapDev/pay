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
        </div>
        <div class="card-body">
            <form class="card-form" action="{{ setRoute('admin.setup.header.sections.store',$slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($slug == global_const()::COMPANY)
                <div class="row py-2">
                    <div class="col-xl-12 col-lg-12">
                        <div class="form-group">
                            <label for="page">{{ __("Select Page") }}</label>
                            <select class="form--control nice-select" id="page" name="page">
                                <option value="about">{{ __("About") }}</option>
                                <option value="service">{{ __("Service") }}</option>
                                <option value="blog">{{ __("Web Journal") }}</option>
                                <option value="pricing">{{ __("Pricing") }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                @endif
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

@endsection

@push('script')
 <script src="{{ asset('public/backend/js/fontawesome-iconpicker.js') }}"></script>
    <script>
        // icon picker
        $('.icp-auto').iconpicker();
    </script>
@endpush
