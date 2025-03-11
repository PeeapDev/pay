@php
    $default_lang_code = language_const()::NOT_REMOVABLE;
    $system_default_lang = get_default_language_code();
    $languages_for_js_use = $languages->toJson();
@endphp

@extends('admin.layouts.master')

@push('css')
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
                    'href'          => route('admin.setup.header.sections.edit',[$type,$parent->id,slug($parent->title->language->$default_lang_code->title)]),
                    'class'         => "btn btn--base",
                    'text'          =>__("Back"),
                    'permission'    => "admin.setup.header.sections.edit",
                ])
            </div>
        </div>
        <div class="card-body">
            <form class="card-form" action="{{ setRoute('admin.setup.header.sections.page.update',[$type,$parent->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row justify-content-center mb-10-none">
                    <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.input-file',[
                            'label'             => __("Section Image").":",
                            'name'              => "section_image",
                            'class'             => "file-holder",
                            'old_files_path'    => files_asset_path("header-section"),
                            'old_files'         => $data->value->images->section_image ?? "",
                        ])
                    </div>
                    <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.input-file',[
                            'label'             => __("Setp Image").":",
                            'name'              => "step_image",
                            'class'             => "file-holder",
                            'old_files_path'    => files_asset_path("header-section"),
                            'old_files'         => $data->value->images->step_image?? "",
                        ])
                    </div>

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
                                            'label'     => __( "Heading*"),
                                            'name'      => $default_lang_code . "_heading",
                                            'value'     => old($default_lang_code . "_heading",$data->value->language->$default_lang_code->heading ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                            'label'     => __( "Sub Heading*"),
                                            'name'      => $default_lang_code . "_sub_heading",
                                            'value'     => old($default_lang_code . "_sub_heading",$data->value->language->$default_lang_code->sub_heading ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Process Step Title")."*",
                                            'name'      => $default_lang_code . "_process_step_title",
                                            'value'     => old($default_lang_code . "_process_step_title",$data->value->language->$default_lang_code->process_step_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Button Name *"),
                                            'name'      => $default_lang_code . "_button_name",
                                            'value'     => old($default_lang_code . "_button_name",$data->value->language->$default_lang_code->button_name ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        <label for="">{{ __("Button Link") }}</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">{{ url('/') }}/</span>
                                            <input type="text" class="form--control" placeholder="{{ __("Write Here..") }}" name="{{ $default_lang_code}}_button_link" value="{{ old($default_lang_code . "_button_link",$data->value->language->$default_lang_code->button_link ?? "") }}">
                                          </div>
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Step Title")."*",
                                            'name'      => $default_lang_code . "_step_title",
                                            'value'     => old($default_lang_code . "_step_title",$data->value->language->$default_lang_code->step_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Step Sub Title")."*",
                                            'name'      => $default_lang_code . "_step_sub_title",
                                            'value'     => old($default_lang_code . "_step_sub_title",$data->value->language->$default_lang_code->step_sub_title ?? "")
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
                                                'label'     => __( "Heading*"),
                                                'name'      => $lang_code . "_heading",
                                                'value'     => old($default_lang_code . "_heading",$data->value->language->$lang_code->heading ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                                'label'     => __( "Sub Heading*"),
                                                'name'      => $lang_code . "_sub_heading",
                                                'value'     => old($lang_code . "_sub_heading",$data->value->language->$lang_code->sub_heading ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Process Step Title")."*",
                                                'name'      => $lang_code . "_process_step_title",
                                                'value'     => old($lang_code . "_process_step_title",$data->value->language->$lang_code->process_step_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Button Name *"),
                                                'name'      => $lang_code . "_button_name",
                                                'value'     => old($lang_code . "_button_name",$data->value->language->$lang_code->button_name ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            <label for="">{{ __("Button Link") }}*</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">{{ url('/') }}/</span>
                                                <input type="text" class="form--control" placeholder="{{ __("Write Here..") }}" name="{{$item->code}}_button_link" value="{{ old($item->code . "_button_link",$data->value->language->$lang_code->button_link ?? "") }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Step Title")."*",
                                                'name'      => $lang_code . "_step_title",
                                                'value'     => old($lang_code . "_step_title",$data->value->language->$lang_code->step_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Step Sub Title")."*",
                                                'name'      => $lang_code . "_step_sub_title",
                                                'value'     => old($lang_code . "_step_sub_title",$data->value->language->$lang_code->step_sub_title ?? "")
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
                            'text'          => __("update"),
                            'type'          => 'submit',
                            'permission'    => "admin.setup.header.sections.page.update"
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="table-area mt-15">
        <div class="table-wrapper">
            <div class="table-header justify-content-end">
                <div class="table-btn-area">
                    <a href="#step-add" class="btn--base modal-btn"><i class="fas fa-plus me-1"></i> {{ __("Add Step") }}</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __("name") }}</th>
                            <th>{{ __("action") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data->value->items ?? [] as $key => $item)
                            <tr data-item="{{ json_encode($item) }}">
                                <td>{{ $item->language->$system_default_lang->name ?? "" }}</td>
                                <td>
                                    <button class="btn btn--base edit-modal-button"><i class="las la-pencil-alt"></i></button>
                                    <button class="btn btn--base btn--danger delete-modal-button" ><i class="las la-trash-alt"></i></button>
                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty',['colspan' => 2])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('admin.components.modals.site-section.add-step-item')
    <div id="step-edit" class="mfp-hide large">
        <div class="modal-data">
            <div class="modal-header px-0">
                <h5 class="modal-title">{{ __("Edit Item") }}</h5>
            </div>
            <div class="modal-form-data">
                <form class="modal-form" method="POST" action="{{ setRoute('admin.setup.header.sections.page.item.update',[$type,$parent->id]) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="target" value="{{ old('target') }}">
                    <div class="row mb-10-none mt-3">
                        <div class="language-tab">
                            <nav>
                                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                    <button class="nav-link @if (get_default_language_code() == language_const()::NOT_REMOVABLE) active @endif" id="edit-modal-english-tab" data-bs-toggle="tab" data-bs-target="#edit-modal-english" type="button" role="tab" aria-controls="edit-modal-english" aria-selected="false">English</button>
                                    @foreach ($languages as $item)
                                        <button class="nav-link @if (get_default_language_code() == $item->code) active @endif" id="edit-modal-{{$item->name}}-tab" data-bs-toggle="tab" data-bs-target="#edit-modal-{{$item->name}}" type="button" role="tab" aria-controls="edit-modal-{{ $item->name }}" aria-selected="true">{{ $item->name }}</button>
                                    @endforeach

                                </div>
                            </nav>
                            <div class="tab-content" id="nav-tabContent">

                                <div class="tab-pane @if (get_default_language_code() == language_const()::NOT_REMOVABLE) fade show active @endif" id="edit-modal-english" role="tabpanel" aria-labelledby="edit-modal-english-tab">
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                        'label'     =>__("name")."*",
                                            'name'      => $default_lang_code . "_name_edit",
                                            'value'     => old($default_lang_code . "_name_edit",$data->value->language->$default_lang_code->name ?? "")
                                        ])
                                    </div>
                                </div>

                                @foreach ($languages as $item)
                                    @php
                                        $lang_code = $item->code;
                                    @endphp
                                    <div class="tab-pane @if (get_default_language_code() == $item->code) fade show active @endif" id="edit-modal-{{ $item->name }}" role="tabpanel" aria-labelledby="edit-modal-{{$item->name}}-tab">
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                               'label'     =>__("name")."*",
                                                'name'      => $lang_code . "_name_edit",
                                                'value'     => old($lang_code . "_name_edit",$data->value->language->$lang_code->name ?? "")
                                            ])
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                            <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                            <button type="submit" class="btn btn--base">{{ __("update") }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script')
<script>
     openModalWhenError("step-add","#step-add");
     openModalWhenError("step-edit","#step-edit");

    var default_language = "{{ $default_lang_code }}";
    var system_default_language = "{{ $system_default_lang }}";
    var languages = "{{ $languages_for_js_use }}";
    languages = JSON.parse(languages.replace(/&quot;/g,'"'));

    $(".edit-modal-button").click(function(){
        var oldData = JSON.parse($(this).parents("tr").attr("data-item"));
        var editModal = $("#step-edit");
        editModal.find("form").first().find("input[name=target]").val(oldData.id);
        editModal.find("input[name="+default_language+"_name_edit]").val(oldData.language[default_language].name);
        $.each(languages,function(index,item) {
            editModal.find("input[name="+item.code+"_name_edit]").val(oldData.language[item.code]?.name);
        });
        openModalBySelector("#step-edit");
    });
    $(".delete-modal-button").click(function(){
        var oldData = JSON.parse($(this).parents("tr").attr("data-item"));
        var actionRoute =  "{{ setRoute('admin.setup.header.sections.page.item.delete',[$type,$parent->id]) }}";
        var target = oldData.id;
        var message     = `Are you sure to <strong>delete</strong> item?`;
        openDeleteModal(actionRoute,target,message);
    });


</script>
@endpush
