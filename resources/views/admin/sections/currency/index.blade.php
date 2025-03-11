@extends('admin.layouts.master')

@push('css')
    <style>
        .fileholder {
            min-height: 194px !important;
        }

        .fileholder-files-view-wrp.accept-single-file .fileholder-single-file-view,.fileholder-files-view-wrp.fileholder-perview-single .fileholder-single-file-view{
            height: 150px !important;
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
    ], 'active' => __("Setup Currency")])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            @includeUnless($default_currency,'admin.components.alerts.warning',['message' => "There is no default currency in your system."])
            <div class="table-header">
                <h5 class="title">{{ __("Setup Currency") }}</h5>
                <div class="table-btn-area">
                    @include('admin.components.search-input',[
                        'name'  => 'currency_search',
                    ])
                    @include('admin.components.link.add-default',[
                        'text'          => "Add Currency",
                        'href'          => "#currency-add",
                        'class'         => "modal-btn",
                        'permission'    => "admin.currency.store",
                    ])
                    <div class="user-action-btn">
                        <a href="#setupPrecison" class="btn--base modal-btn"><i class="las la-cog"></i> {{ __("Setup Precison") }}</a>
                    </div>
                    <div class="user-action-btn">
                        <a href="{{ setRoute('admin.live.exchange.rate.index') }}" class="btn--base"><i class="las la-cog"></i> {{ __("Exchange Rate Api") }}</a>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                @include('admin.components.data-table.currency-table',[
                    'data'  => $currencies
                ])
            </div>
        </div>
        {{ get_paginate($currencies) }}
    </div>

    {{-- Currency Edit Modal --}}
    @include('admin.components.modals.edit-currency')

    {{-- Currency Add Modal --}}
    @include('admin.components.modals.add-currency')
    {{-- SetupPrecison  modal --}}
    @include('admin.components.modals.setup-pricison')

@endsection

@push('script')
    <script>

        getAllCountries("{{ setRoute('global.countries') }}"); // get all country and place it country select input
        $(document).ready(function() {
            reloadAllCountries("select[name=country]");

            // Country Field On Change
            $(document).on("change",".country-select",function() {
                var selectedValue = $(this);
                var currencyName = $(".country-select :selected").attr("data-currency-name");
                var currencyCode = $(".country-select :selected").attr("data-currency-code");
                var currencySymbol = $(".country-select :selected").attr("data-currency-symbol");

                var currencyType = selectedValue.parents("form").find("input[name=type],input[name=currency_type]").val();
                var readOnly = true;
                if(currencyType == "CRYPTO") {
                    keyPressCurrencyView($(this));
                    readOnly = false;
                    console.log(readOnly);
                }

                selectedValue.parents("form").find("input[name=name],input[name=currency_name]").val(currencyName).prop("readonly",readOnly);
                selectedValue.parents("form").find("input[name=code],input[name=currency_code]").val(currencyCode).prop("readonly",readOnly);
                selectedValue.parents("form").find("input[name=symbol],input[name=currency_symbol]").val(currencySymbol).prop("readonly",readOnly);
                selectedValue.parents("form").find(".selcted-currency, .selcted-currency-edit").text(currencyCode);
            });

        });

        function keyPressCurrencyView(select) {
            var selectedValue = $(select);
            selectedValue.parents("form").find("input[name=code],input[name=currency_code]").keyup(function(){
                selectedValue.parents("form").find(".selcted-currency").text($(this).val());
            });
        }

        $("input[name=type],input[name=currency_type]").siblings(".switch").click(function(){
            setTimeout(() => {
                var currencyType = $(this).siblings("input[name=type],input[name=currency_type]").val();
                var readOnly = true;
                if(currencyType == "CRYPTO") {
                    readOnly = false;
                }
                readOnlyAddRemove($(this),readOnly);
            }, 200);
        });

        function readOnlyAddRemove (select,readOnly) {
            var selectedValue = $(select);
            selectedValue.parents("form").find("input[name=name],input[name=currency_name]").prop("readonly",readOnly);
            selectedValue.parents("form").find("input[name=code],input[name=currency_code]").prop("readonly",readOnly);
            selectedValue.parents("form").find("input[name=symbol],input[name=currency_symbol]").prop("readonly",readOnly);
            // selectedValue.parents("form").find(".selcted-currency").text(currencyCode);
        }

        $(".delete-modal-button").click(function(){
            var oldData = JSON.parse($(this).parents("tr").attr("data-item"));

            var actionRoute =  "{{ setRoute('admin.currency.delete') }}";
            var target      = oldData.code;
            var message     = `Are you sure to delete <strong>${oldData.code}</strong> currency?`;

            openDeleteModal(actionRoute,target,message);
        });

        itemSearch($("input[name=currency_search]"),$(".currency-search-table"),"{{ setRoute('admin.currency.search') }}",1);
    </script>
@endpush
