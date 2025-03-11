@extends('admin.layouts.master')

@push('css')

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
    ], 'active' => __("Referral Settings")])
@endsection

@section('content')

    <div class="custom-card">

        <div class="card-header">
            <h6 class="title">{{ __("New Registration Bonus") }}</h6>
        </div>

        <div class="card-body">

            <div class="card-title mb-2">
                <p class="f-sm fw-bold text--info">{{ __("Please click update button to make any changes") }}</p>
            </div>

            <form class="card-form" method="POST" action="{{ setRoute('admin.settings.referral.update') }}">
                @csrf
                <div class="row">
                    <div class="col-3 mb-4 form-group">
                        <label>{{ __("BONUS") }} ({{ __("Amount") }})<span>*</span></label>
                        <div class="input-group">
                            <input type="text" class="form--control number-input" name="bonus" value="{{ old('bonus',$referral_settings->bonus ?? "") }}" placeholder="{{ __("Enter New User Bonus") }}">
                            <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                        </div>

                    </div>

                    <div class="col-3 mb-4">
                        @include('admin.components.form.switcher',[
                            'label'         => __('Mail Notification'),
                            'name'          => 'mail',
                            'value'         => old('mail', $referral_settings->mail ?? 0),
                            'options'   => [__("Enable") => 1, __("Disabled") => 0],
                        ])
                    </div>
                    <div class="col-3 mb-4">
                        @include('admin.components.form.switcher',[
                            'label'         => __('Sms Notification'),
                            'name'          => 'sms',
                            'value'         => old('sms', $referral_settings->sms ?? 0),
                            'options'   => [__("Enable") => 1, __("Disabled") => 0],
                        ])
                    </div>
                    <div class="col-3 mb-4">
                        @include('admin.components.form.switcher',[
                            'label'         => __('Status'),
                            'name'          => 'status',
                            'value'         => old('status', $referral_settings->status ?? 0),
                            'options'   => [__("Enable") => 1, __("Disabled") => 0],
                        ])
                    </div>
                </div>

                <div class="col-xl-12 col-lg-12">
                    @include('admin.components.button.form-btn',[
                        'class'         => "w-100 btn-loading",
                        'text'          => __("update"),
                    ])
                </div>
            </form>
        </div>

    </div>


    {{-- Level Package List --}}
    <div class="table-area mt-5">
        <div class="table-wrapper">

            <div class="table-header">
                <h5 class="title">{{ __("Level Packages") }}</h5>
                <div class="table-btn-area">
                    @include('admin.components.link.add-default',[
                        'text'          => __("Add New"),
                        'href'          => "#package-add",
                        'class'         => "modal-btn",
                        'permission'    => "admin.settings.referral.package.store",
                    ])
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __("SL NO") }}</th>
                            <th>{{ __("titleS") }}</th>
                            <th>{{ __("Require Refers") }}</th>
                            <th>{{ __("Require Deposit Amount") }}</th>
                            <th>{{ __("Commission") }}</th>
                            <th>{{__("Status") }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($referral_level_packages ?? [] as $key => $item)
                            <tr data-item='@json($item)'>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    {{ $item->title }}
                                    @if ($item->default)
                                        <span class="badge badge--success ms-1">{{ __("Default") }}</span>
                                    @endif
                                </td>
                                <td>{{ $item->refer_user }}
                                <td>{{ get_amount($item->deposit_amount, $default_currency->code) }}</td>
                                <td>
                                    {{ get_amount($item->commission, $default_currency->code) }}
                                </td>
                                <td>
                                    @include('admin.components.form.switcher',[
                                        'name'          => 'status',
                                        'value'         => $item->default,
                                        'options'       => [__("Default") => 1,__("Selectable") => 0],
                                        'onload'        => true,
                                        'data_target'   => $item->id,
                                    ])

                                <td>
                                    @include('admin.components.link.edit-default',[
                                        'href'          => "javascript:void(0)",
                                        'class'         => "edit-modal-button",
                                        'permission'    => "admin.settings.referral.package.update",
                                    ])
                                     {{-- @if (!$item->isDefault())
                                        @include('admin.components.link.delete-default',[
                                            'href'          => "javascript:void(0)",
                                            'class'         => "delete-modal-button",
                                            'permission'    => "admin.currency.delete",
                                        ])
                                    @endif --}}
                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty',['colspan' => 7])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Package Add Modal --}}
    @if (admin_permission_by_name("admin.settings.referral.package.store"))

        <div id="package-add" class="mfp-hide large">
            <div class="modal-data">
                <div class="modal-header px-0">
                    <h5 class="modal-title">{{ __("Add New Package For") }} {{ __("Level") }} {{ $referral_level_packages->count() . " - " . $referral_level_packages->count() + 1 }} </h5>
                </div>
                <div class="modal-form-data">
                    <form class="modal-form" method="POST" action="{{ setRoute('admin.settings.referral.package.store') }}">
                        @csrf
                        <div class="row mb-10-none">

                            <div class="col-6 form-group">
                                @include('admin.components.form.input',[
                                    'label'         => __('titleS'),
                                    'label_after'   => '<span>*</span>',
                                    'name'          => 'title',
                                    'value'         => old('title'),
                                    'placeholder'   => __('ex: Level One'),

                                ])
                            </div>
                            <div class="col-6 form-group">
                                <label>{{ __("Refer Commission") }}<span>*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control number-input" value="{{ old('commission') }}" name="commission" placeholder ="{{ __("ex_web") }} {{ "2" }}">
                                    <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                </div>

                            </div>

                            <div class="col-6 form-group">
                                <label>{{ __("Require Refers") }}<span>*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control number-input" value="{{ old('refers') }}" placeholder ="{{ __("ex_web") }} {{ "50" }}" name="refers">
                                    <span class="input-group-text">{{ __("Users") }}</span>
                                </div>
                            </div>

                            <div class="col-6 form-group">
                                <label>{{ __("Require Deposit Amount") }}<span>*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control number-input" value="{{ old('deposit_amount') }}" placeholder ="{{ __("ex_web") }} {{ "1000" }}" name="deposit_amount">
                                    <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group">
                                @include('admin.components.form.switcher',[
                                    'label'         => __("options").'*',
                                    'name'          => 'option',
                                    'value'         => 0,
                                    'options'       => [__("Selectable") => 0,__("Default") => 1],
                                ])
                            </div>

                            <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                                <button type="submit" class="btn btn--base w-100">{{ __("Add") }}</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endif

    {{-- Package Update Modal --}}
    @if (admin_permission_by_name("admin.settings.referral.package.update"))

        <div id="package-edit" class="mfp-hide large">
            <div class="modal-data">
                <div class="modal-header px-0">
                    <h5 class="modal-title">{{ __("Update Package Information") }}</h5>
                </div>
                <div class="modal-form-data">
                    <form class="modal-form" method="POST" action="{{ setRoute('admin.settings.referral.package.update') }}">
                        @csrf
                        <input type="hidden" name="target" value="{{ old('target') }}">
                        <div class="row mb-10-none">

                            <div class="col-6 form-group">
                                @include('admin.components.form.input',[
                                    'label'         => __('titleS'),
                                    'label_after'   => '<span>*</span>',
                                    'name'          => 'edit_title',
                                    'value'         => old('edit_title'),

                                ])
                            </div>
                            <div class="col-6 form-group">
                                <label>{{ __("Refer Commission") }}<span>*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control number-input" value="{{ old('edit_commission') }}" name="edit_commission" placeholder ="{{ __("ex_web") }} {{ "2" }}">
                                    <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                </div>

                            </div>

                            <div class="col-6 form-group">
                                <label>{{ __("Require Refers") }}<span>*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control number-input" value="{{ old('edit_refers') }}"  placeholder ="{{ __("ex_web") }} {{ "50" }}" name="edit_refers">
                                    <span class="input-group-text">{{ __("Users") }}</span>
                                </div>
                                @error('edit_refers')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-6 form-group">
                                <label>{{ __("Require Deposit Amount") }}<span>*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control number-input" value="{{ old('edit_deposit_amount') }}"  placeholder ="{{ __("ex_web") }} {{ "1000" }}" name="edit_deposit_amount">
                                    <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                </div>

                            </div>

                            <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                                <button type="submit" class="btn btn--base w-100">{{ __("update") }}</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endif

@endsection

@push('script')

    <script>
        openModalWhenError('package-add','#package-add');

        $(".edit-modal-button").click(function() {
            var oldData = JSON.parse($(this).parents("tr").attr("data-item"));
            var editModal = $("#package-edit");


            editModal.find(".invalid-feedback").remove();
            editModal.find(".form--control").removeClass("is-invalid");

            editModal.find("form").first().find("input[name=target]").val(oldData.id);
            editModal.find("input[name=edit_title]").val(oldData.title);
            editModal.find("input[name=edit_commission]").val(oldData.commission);
            editModal.find("input[name=edit_refers]").val(oldData.refer_user);
            editModal.find("input[name=edit_deposit_amount]").val(oldData.deposit_amount);

            openModalBySelector("#package-edit");
        });

        $(".delete-modal-button").click(function(){
            var oldData = JSON.parse($(this).parents("tr").attr("data-item"));

            var btnText = '{{ __("Delete") }}';
            var actionRoute =  "{{ setRoute('admin.settings.referral.package.delete') }}";
            var target      = oldData.id;
            var message     = '{{ __("delete_text") }}'+'?';

            openDeleteModal(actionRoute,target,message,btnText);
        });
         // Switcher
          switcherAjaxReload("{{ setRoute('admin.settings.referral.package.status.update') }}");

        function switcherAjaxReload(hitUrl,method = "PUT") {
            $(document).on("click",".event-ready",function(event) {
            var inputName = $(this).parents(".switch-toggles").find("input").attr("name");
            if(inputName == undefined || inputName == "") {
                return false;
            }
            $(this).parents(".switch-toggles").find(".switch").removeClass("event-ready");
            var input = $(this).parents(".switch-toggles").find("input[name="+inputName+"]");
            var eventElement = $(this);
            if(input.length == 0) {
                alert("Input field not found.");
                $(this).parents(".switch-toggles").find(".switch").addClass("event-ready");
                $(this).find(".btn-ring").hide();
                return false;
            }
            var CSRF = $("head meta[name=csrf-token]").attr("content");
            var dataTarget = "";
            if(input.attr("data-target")) {
                dataTarget = input.attr("data-target");
            }
            var inputValue = input.val();
            var data = {
                _token: CSRF,
                _method: method,
                data_target: dataTarget,
                status: inputValue,
                input_name: inputName,
            };
            $.post(hitUrl,data,function(response) {
                // console.log(response);
                throwMessage('success',response.message.success);
                // Remove Loading animation
                $(event.target).find(".btn-ring").hide();
            }).done(function(response){
                // console.log(response);
                $(eventElement).parents(".switch-toggles").find(".switch").addClass("event-ready");
                $(eventElement).parents(".switch-toggles.btn-load").toggleClass('active');
                var dataValue = $(eventElement).parents(".switch-toggles").find(".switch").last().attr("data-value");
                if($(eventElement).parents(".switch-toggles").hasClass("active")) {
                dataValue = $(eventElement).parents(".switch-toggles").find(".switch").first().attr("data-value");
                $(eventElement).parents(".switch-toggles").find(".switch").first().find(".btn-ring").hide();
                }
                $(eventElement).parents(".switch-toggles.btn-load").find("input").val(dataValue);
                $(eventElement).parents(".switch-toggles").find(".switch").find(".btn-ring").hide();
                location.reload();

            }).fail(function(response) {
                var response = JSON.parse(response.responseText);
                throwMessage(response.type,response.message.error);
                $(eventElement).parents(".switch-toggles").find(".switch").addClass("event-ready");
                $(eventElement).parents(".switch-toggles").find(".btn-ring").hide();
                return false;
            });
            });
        }
    </script>

@endpush
