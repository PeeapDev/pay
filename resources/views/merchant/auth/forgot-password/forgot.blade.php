
@extends('merchant.layouts.user_auth')

@php
    $lang = selectedLang();
    $auth_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::AUTH_SECTION);
    $auth_text = App\Models\Admin\SiteSections::getData( $auth_slug)->first();
@endphp
@push('css')
    <style>
        .input-group-text span {
            max-width: 20ch !important;
        }
    </style>
@endpush
@section('content')

<section class="account">

    <div class="account-area">
        <div class="account-wrapper">
            <div class="account-logo text-center">
               <a class="site-logo" href="{{ setRoute('index') }}">
                <img src="{{ get_logo_merchant($basic_settings) }}"  data-white_img="{{ get_logo_merchant($basic_settings,'white') }}"
                data-dark_img="{{ get_logo_merchant($basic_settings,'dark') }}"
                    alt="site-logo">
               </a>
            </div>
            <h5 class="title">{{ __("Reset Your Forgotten Password") }}</h5>
            <p>{{ __(@$auth_text->value->language->$lang->forget_text) }}</p>
            <form class="account-form" action="{{ setRoute('merchant.password.forgot.send.code') }}" method="POST">
                @csrf
                <div class="row ml-b-20">
                    <div class="col-xl-12 col-lg-12  form-group">
                        <label>{{ __("select TypeS") }} <span class="text--base">*</span></label>
                        <select class="form--control nice-select" name="type">
                            <option value="" disabled  selected>{{ __("Select One") }}</option>
                            <option value="{{ global_const()::PHONE }}">{{ global_const()::PHONE }}</option>
                            <option value="{{ global_const()::EMAIL }}">{{ global_const()::EMAIL }}</option>
                        </select>
                    </div>
                    <div class="credentials"> </div>

                    <div class="col-lg-12 form-group text-center">
                        <x-security.google-recaptcha-field />
                        <button type="submit"  class="btn--base w-100 btn-loading">{{ __("Continue") }} </button>
                    </div>
                    <div class="col-lg-12 text-center">
                        <div class="account-item">
                            <label>{{ __("already Have An Account") }} <a href="{{ setRoute('merchant.login') }}" class="account-control-btn">{{ __("Login Now") }}</a></label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End acount
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<ul class="bg-bubbles">
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
</ul>

@php
    // Fetch the countries
    $countries = freedom_countries(global_const()::MERCHANT);
@endphp
@endsection

@push('script')
<script>
    $(document).ready(function(){
        setRegisterType();
    });
    $("select[name=type]").change(function(){
        setRegisterType();
    });
     function acceptVar() {
        var selectedVal = $("select[name=type] :selected");
        return {
            selectedVal:selectedVal,

        };
    }
    function setRegisterType(){
        var type = acceptVar().selectedVal.val();
        if(type == "{{ global_const()::EMAIL }}"){
            $('.credentials').html('');
            $('.credentials').html(`
                        <div class="col-xl-12 col-lg-12 form-group">
                            <input type="email" name="credentials" class="form--control checkUser email" placeholder="{{ __("enter Email Address") }}" value="{{ old('credentials') }}">
                        </div>
                    `);
        }else if(type == "{{ global_const()::PHONE }}"){
            $('.credentials').html('');
            var countries = {!! json_encode($countries) !!};
            var localInfo = {!! json_encode(location_info()) !!};
            var options = '';
            countries.forEach(function(country) {
                if(country.mobile_code == localInfo.dial_code && country.name == localInfo.info.country){
                    var selected = 'selected';
                }else{
                    var selected = '';
                }
                options += `<option value="${country.mobile_code}" ${selected}>${country.name}(${country.mobile_code})</option>`;
            });

            $('.credentials').html(`
                       <div class="col-xl-12 col-lg-12 form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <select class="input-group-text copytext nice-select" name="mobile_code">
                                    ${options}
                                </select>
                            </div>
                            <input type="text" name="credentials" class="form--control" placeholder="{{ __("Enter Phone Number") }}" value="{{ old('credentials') }}">

                        </div>
                        <small class="text-danger exits"></small>
                    </div>
                    `);
                    $("select[name=mobile_code]").niceSelect();
        }else{
            $('.credentials').html('');
            return false;
        }
    }
</script>
@endpush
