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
            <form class="card-form" action="{{ setRoute('admin.setup.sections.section.update',$slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row justify-content-center mb-10-none">
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.form.input-file',[
                            'label'             => __("Image").":",
                            'name'              => "image",
                            'class'             => "file-holder",
                            'old_files_path'    => files_asset_path("site-section"),
                            'old_files'         => $data->value->images->image ?? "",
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
                                            'label'     => __( "Transfer Money Title")."*",
                                            'name'      => $default_lang_code . "_transfer_title",
                                            'value'     => old($default_lang_code . "_transfer_title",$data->value->language->$default_lang_code->transfer_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                           'label'     => __( "Transfer Money Sub Title")."*",
                                            'name'      => $default_lang_code . "_transfer_sub_title",
                                            'value'     => old($default_lang_code . "_transfer_sub_title",$data->value->language->$default_lang_code->transfer_sub_title ?? "")
                                        ])
                                    </div>

                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Bill Pay Title")."*",
                                            'name'      => $default_lang_code . "_bill_pay_title",
                                            'value'     => old($default_lang_code . "_bill_pay_title",$data->value->language->$default_lang_code->bill_pay_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                           'label'     => __( "Bill Pay Sub Title")."*",
                                            'name'      => $default_lang_code . "_bill_pay_sub_title",
                                            'value'     => old($default_lang_code . "_bill_pay_sub_title",$data->value->language->$default_lang_code->bill_pay_sub_title ?? "")
                                        ])
                                    </div>

                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Top-Up Title")."*",
                                            'name'      => $default_lang_code . "_mobile_topup_title",
                                            'value'     => old($default_lang_code . "_mobile_topup_title",$data->value->language->$default_lang_code->mobile_topup_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                           'label'     => __( "Top-Up Sub Title")."*",
                                            'name'      => $default_lang_code . "_mobile_topup_sub_title",
                                            'value'     => old($default_lang_code . "_mobile_topup_sub_title",$data->value->language->$default_lang_code->mobile_topup_sub_title ?? "")
                                        ])
                                    </div>

                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Virtual Card Title")."*",
                                            'name'      => $default_lang_code . "_virtual_card_title",
                                            'value'     => old($default_lang_code . "_virtual_card_title",$data->value->language->$default_lang_code->virtual_card_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                           'label'     => __( "Virtual Card Sub Title")."*",
                                            'name'      => $default_lang_code . "_virtual_card_sub_title",
                                            'value'     => old($default_lang_code . "_virtual_card_sub_title",$data->value->language->$default_lang_code->virtual_card_sub_title ?? "")
                                        ])
                                    </div>

                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Remittance Title")."*",
                                            'name'      => $default_lang_code . "_remittance_title",
                                            'value'     => old($default_lang_code . "_remittance_title",$data->value->language->$default_lang_code->remittance_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                            'label'     => __( "Remittance Sub Title")."*",
                                            'name'      => $default_lang_code . "_remittance_sub_title",
                                            'value'     => old($default_lang_code . "_remittance_sub_title",$data->value->language->$default_lang_code->remittance_sub_title ?? "")
                                        ])
                                    </div>

                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Make Payment Title")."*",
                                            'name'      => $default_lang_code . "_make_payment_title",
                                            'value'     => old($default_lang_code . "_make_payment_title",$data->value->language->$default_lang_code->make_payment_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                            'label'     => __( "Make Payment Sub Title")."*",
                                            'name'      => $default_lang_code . "_make_payment_sub_title",
                                            'value'     => old($default_lang_code . "_make_payment_sub_title",$data->value->language->$default_lang_code->make_payment_sub_title ?? "")
                                        ])
                                    </div>

                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Request Money Title")."*",
                                            'name'      => $default_lang_code . "_request_money_title",
                                            'value'     => old($default_lang_code . "_request_money_title",$data->value->language->$default_lang_code->request_money_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                            'label'     => __( "Request Money Sub Title")."*",
                                            'name'      => $default_lang_code . "_request_money_sub_title",
                                            'value'     => old($default_lang_code . "_request_money_sub_title",$data->value->language->$default_lang_code->request_money_sub_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Pay-Link Title")."*",
                                            'name'      => $default_lang_code . "_pay_link_title",
                                            'value'     => old($default_lang_code . "_pay_link_title",$data->value->language->$default_lang_code->pay_link_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                            'label'     => __( "Pay-Link Sub Title")."*",
                                            'name'      => $default_lang_code . "_pay_link_sub_title",
                                            'value'     => old($default_lang_code . "_pay_link_sub_title",$data->value->language->$default_lang_code->pay_link_sub_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Money Out Title")."*",
                                            'name'      => $default_lang_code . "_money_out_title",
                                            'value'     => old($default_lang_code . "_money_out_title",$data->value->language->$default_lang_code->money_out_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                            'label'     => __( "Money Out Sub Title")."*",
                                            'name'      => $default_lang_code . "_money_out_sub_title",
                                            'value'     => old($default_lang_code . "_money_out_sub_title",$data->value->language->$default_lang_code->money_out_sub_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Money In Title")."*",
                                            'name'      => $default_lang_code . "_money_in_title",
                                            'value'     => old($default_lang_code . "_money_in_title",$data->value->language->$default_lang_code->money_in_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                            'label'     => __( "Money In Sub Title")."*",
                                            'name'      => $default_lang_code . "_money_in_sub_title",
                                            'value'     => old($default_lang_code . "_money_in_sub_title",$data->value->language->$default_lang_code->money_in_sub_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Reload Card Title")."*",
                                            'name'      => $default_lang_code . "_reload_card_title",
                                            'value'     => old($default_lang_code . "_reload_card_title",$data->value->language->$default_lang_code->reload_card_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                            'label'     => __( "Reload Card Sub Title")."*",
                                            'name'      => $default_lang_code . "_reload_card_sub_title",
                                            'value'     => old($default_lang_code . "_reload_card_sub_title",$data->value->language->$default_lang_code->reload_card_sub_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Gift Card Title")."*",
                                            'name'      => $default_lang_code . "_gift_card_title",
                                            'value'     => old($default_lang_code . "_gift_card_title",$data->value->language->$default_lang_code->gift_card_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                            'label'     => __( "Gift Card Sub Title")."*",
                                            'name'      => $default_lang_code . "_gift_card_sub_title",
                                            'value'     => old($default_lang_code . "_gift_card_sub_title",$data->value->language->$default_lang_code->gift_card_sub_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => __( "Money Exchange Title")."*",
                                            'name'      => $default_lang_code . "_money_exchange_title",
                                            'value'     => old($default_lang_code . "_money_exchange_title",$data->value->language->$default_lang_code->money_exchange_title ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.textarea',[
                                            'label'     => __( "Money Exchange Sub Title")."*",
                                            'name'      => $default_lang_code . "_money_exchange_sub_title",
                                            'value'     => old($default_lang_code . "_money_exchange_sub_title",$data->value->language->$default_lang_code->money_exchange_sub_title ?? "")
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
                                                'value'     => old($lang_code . "_heading",$data->value->language->$lang_code->heading ?? "")
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
                                                'label'     => __( "Transfer Money Title")."*",
                                                'name'      => $lang_code . "_transfer_title",
                                                'value'     => old($lang_code . "_transfer_title",$data->value->language->$lang_code->transfer_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                               'label'     => __( "Transfer Money Sub Title")."*",
                                                'name'      => $lang_code . "_transfer_sub_title",
                                                'value'     => old($lang_code . "_transfer_sub_title",$data->value->language->$lang_code->transfer_sub_title ?? "")
                                            ])
                                        </div>

                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Bill Pay Title")."*",
                                                'name'      => $lang_code . "_bill_pay_title",
                                                'value'     => old($lang_code . "_bill_pay_title",$data->value->language->$lang_code->bill_pay_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                               'label'     => __( "Bill Pay Sub Title")."*",
                                                'name'      => $lang_code . "_bill_pay_sub_title",
                                                'value'     => old($lang_code . "_bill_pay_sub_title",$data->value->language->$lang_code->bill_pay_sub_title ?? "")
                                            ])
                                        </div>

                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Top-Up Title")."*",
                                                'name'      => $lang_code . "_mobile_topup_title",
                                                'value'     => old($lang_code . "_mobile_topup_title",$data->value->language->$lang_code->mobile_topup_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                               'label'     => __( "Top-Up Sub Title")."*",
                                                'name'      => $lang_code . "_mobile_topup_sub_title",
                                                'value'     => old($lang_code . "_mobile_topup_sub_title",$data->value->language->$lang_code->mobile_topup_sub_title ?? "")
                                            ])
                                        </div>

                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Virtual Card Title")."*",
                                                'name'      => $lang_code . "_virtual_card_title",
                                                'value'     => old($lang_code . "_virtual_card_title",$data->value->language->$lang_code->virtual_card_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                               'label'     => __( "Virtual Card Sub Title")."*",
                                                'name'      => $lang_code . "_virtual_card_sub_title",
                                                'value'     => old($lang_code . "_virtual_card_sub_title",$data->value->language->$lang_code->virtual_card_sub_title ?? "")
                                            ])
                                        </div>

                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Remittance Title")."*",
                                                'name'      => $lang_code . "_remittance_title",
                                                'value'     => old($lang_code . "_remittance_title",$data->value->language->$lang_code->remittance_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                                'label'     => __( "Remittance Sub Title")."*",
                                                'name'      => $lang_code . "_remittance_sub_title",
                                                'value'     => old($lang_code . "_remittance_sub_title",$data->value->language->$lang_code->remittance_sub_title ?? "")
                                            ])
                                        </div>

                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Make Payment Title")."*",
                                                'name'      => $lang_code . "_make_payment_title",
                                                'value'     => old($lang_code . "_make_payment_title",$data->value->language->$lang_code->make_payment_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                                'label'     => __( "Make Payment Sub Title")."*",
                                                'name'      => $lang_code . "_make_payment_sub_title",
                                                'value'     => old($lang_code . "_make_payment_sub_title",$data->value->language->$lang_code->make_payment_sub_title ?? "")
                                            ])
                                        </div>

                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Request Money Title")."*",
                                                'name'      => $lang_code . "_request_money_title",
                                                'value'     => old($lang_code . "_request_money_title",$data->value->language->$lang_code->request_money_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                                'label'     => __( "Request Money Sub Title")."*",
                                                'name'      => $lang_code . "_request_money_sub_title",
                                                'value'     => old($lang_code . "_request_money_sub_title",$data->value->language->$lang_code->request_money_sub_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Pay-Link Title")."*",
                                                'name'      => $lang_code . "_pay_link_title",
                                                'value'     => old($lang_code . "_pay_link_title",$data->value->language->$lang_code->pay_link_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                                'label'     => __( "Pay-Link Sub Title")."*",
                                                'name'      => $lang_code . "_pay_link_sub_title",
                                                'value'     => old($lang_code . "_pay_link_sub_title",$data->value->language->$lang_code->pay_link_sub_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Money Out Title")."*",
                                                'name'      => $lang_code . "_money_out_title",
                                                'value'     => old($lang_code . "_money_out_title",$data->value->language->$lang_code->money_out_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                                'label'     => __( "Money Out Sub Title")."*",
                                                'name'      => $lang_code . "_money_out_sub_title",
                                                'value'     => old($lang_code . "_money_out_sub_title",$data->value->language->$lang_code->money_out_sub_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Money In Title")."*",
                                                'name'      => $lang_code . "_money_in_title",
                                                'value'     => old($lang_code . "_money_in_title",$data->value->language->$lang_code->money_in_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                                'label'     => __( "Money In Sub Title")."*",
                                                'name'      => $lang_code . "_money_in_sub_title",
                                                'value'     => old($lang_code . "_money_in_sub_title",$data->value->language->$lang_code->money_in_sub_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Reload Card Title")."*",
                                                'name'      => $lang_code . "_reload_card_title",
                                                'value'     => old($lang_code . "_reload_card_title",$data->value->language->$lang_code->reload_card_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                                'label'     => __( "Reload Card Sub Title")."*",
                                                'name'      => $lang_code . "_reload_card_sub_title",
                                                'value'     => old($lang_code . "_reload_card_sub_title",$data->value->language->$lang_code->reload_card_sub_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Gift Card Title")."*",
                                                'name'      => $lang_code . "_gift_card_title",
                                                'value'     => old($lang_code . "_gift_card_title",$data->value->language->$lang_code->gift_card_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                                'label'     => __( "Gift Card Sub Title")."*",
                                                'name'      => $lang_code . "_gift_card_sub_title",
                                                'value'     => old($lang_code . "_gift_card_sub_title",$data->value->language->$lang_code->gift_card_sub_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __( "Money Exchange Title")."*",
                                                'name'      => $lang_code . "_money_exchange_title",
                                                'value'     => old($lang_code . "_money_exchange_title",$data->value->language->$lang_code->money_exchange_title ?? "")
                                            ])
                                        </div>
                                        <div class="form-group">
                                            @include('admin.components.form.textarea',[
                                                'label'     => __( "Money Exchange Sub Title")."*",
                                                'name'      => $lang_code . "_money_exchange_sub_title",
                                                'value'     => old($lang_code . "_money_exchange_sub_title",$data->value->language->$lang_code->money_exchange_sub_title ?? "")
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
                            'permission'    => "admin.setup.sections.section.update"
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection

@push('script')

@endpush
