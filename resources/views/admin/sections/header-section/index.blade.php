@php
    $default_lang_code = language_const()::NOT_REMOVABLE;
    $system_default_lang = get_default_language_code();
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

    <div class="table-area mt-15">
        <div class="table-wrapper">
            <div class="table-header">
                <h6 class="title">{{ __($page_title) }}</h6>
                {{-- <div class="table-btn-area">
                    <a href="{{ setRoute('admin.setup.header.sections.create',$slug) }}" class="btn--base"><i class="fas fa-plus me-1"></i> {{ __("Add") }}</a>
                </div> --}}
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __("Icon") }}</th>
                            <th>{{ __("titleS") }}</th>
                            <th>{{__("Status") }}</th>
                            <th>{{ __("action") }}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @forelse ($data ?? [] as $key => $item)
                            <tr data-item="{{ json_encode($item) }}">
                                <td>
                                   <i class=" {{ $item->icon->language->$system_default_lang->icon ?? "" }}"></i>
                                </td>
                                <td>
                                    {{ $item->title->language->$system_default_lang->title ?? "" }}
                                </td>
                                <td>
                                    @include('admin.components.form.switcher',[
                                        'name'          => 'status',
                                        'value'         => $item->status,
                                        'options'       => [__("Enable") => 1,__("Disable") => 0],
                                        'onload'        => true,
                                        'data_target'   => $item->id,
                                        'permission'    => "admin.setup.header.sections.status.update",
                                    ])
                                </td>
                                <td>
                                    <a href="{{ setRoute('admin.setup.header.sections.edit',[$slug,$item->id,slug($item->title->language->$default_lang_code->title)]) }}" class="btn btn--base"><i
                                        class="las la-pencil-alt"></i></a>
                                    {{-- <button class="btn btn--base btn--danger delete-modal-button" ><i class="las la-trash-alt"></i></button> --}}
                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty',['colspan' => 4])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
         $(".delete-modal-button").click(function(){
            var oldData = JSON.parse($(this).parents("tr").attr("data-item"));

            var actionRoute =  "{{ setRoute('admin.setup.header.sections.delete') }}";
            var target = oldData.id;

            var message     = `Are you sure to <strong>delete</strong> item?`;

            openDeleteModal(actionRoute,target,message);
        });
        // Switcher
        switcherAjax("{{ setRoute('admin.setup.header.sections.status.update') }}");

    </script>
@endpush
