@extends('merchant.layouts.user_auth')

@php
    $lang = selectedLang();
    $auth_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::AUTH_SECTION);
    $auth_text = App\Models\Admin\SiteSections::getData( $auth_slug)->first();
    $type =  Illuminate\Support\Str::slug(App\Constants\GlobalConst::USEFUL_LINKS);
    $policies = App\Models\Admin\SetupPage::orderBy('id')->where('type', $type)->where('slug',"terms-and-conditions")->where('status',1)->first();
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
            <h5 class="title">{{ __("Register for an Account Today") }}</h5>
            <p>{{ __(@$auth_text->value->language->$lang->register_text) }}</p>
            <form class="account-form" action="{{ route('merchant.send.code') }}" method="POST">
                @csrf
                <div class="row ml-b-20">
                    <div class="col-xl-12 col-lg-12  form-group">
                        <label>{{ __("Registration Type") }} <span class="text--base">*</span></label>
                        <select class="form--control nice-select" name="register_type">
                            <option value="" disabled  selected>{{ __("Select One") }}</option>
                            <option value="{{ global_const()::PHONE }}">{{ global_const()::PHONE }}</option>
                            <option value="{{ global_const()::EMAIL }}">{{ global_const()::EMAIL }}</option>
                        </select>
                    </div>
                    <div class="credentials"> </div>

                    @if($basic_settings->merchant_agree_policy)
                    <div class="agree-fields"></div>
                    @endif
                    <div class="col-lg-12 form-group text-center">
                        <x-security.google-recaptcha-field />
                        <button type="submit"  class="btn--base w-100  btn-loading   ">{{ __("Continue") }} </button>
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
    $("select[name=register_type]").change(function(){
        setRegisterType();
    });
     function acceptVar() {
        var selectedVal = $("select[name=register_type] :selected");
        return {
            selectedVal:selectedVal,

        };
    }
    function setRegisterType(){
        var type = acceptVar().selectedVal.val();
        if(type == "{{ global_const()::EMAIL }}"){
            $('.credentials').html('');
            $('.agree-fields').html('');
            $('.credentials').html(`
                <div class="col-xl-12 col-lg-12 form-group">
                    <input type="email" name="credentials" class="form--control checkUser email" placeholder="{{ __("enter Email Address") }}" value="{{ old('credentials') }}">
                </div>
            `);
            $('.agree-fields').html(`
                <div class="col-lg-12 form-group">
                    <div class="custom-check-group">
                        <input type="checkbox" id="agree" name="agree" required>
                        <label for="agree">{{ __("I have agreed with") }} <a href="{{  $policies != null? setRoute('useful.link',$policies->slug):"javascript:void(0)" }}" target="_blank">{{__("Terms Of Use & Privacy Policy")}}</a></label>
                    </div>
                </div>
            `);
        }else if(type == "{{ global_const()::PHONE }}"){
            $('.agree-fields').html('');
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
                options += `<option value="${country.name}" ${selected}>${country.name}(${country.mobile_code})</option>`;
            });

            $('.credentials').html(`
                <div class="col-xl-12 col-lg-12 form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <select class="input-group-text copytext nice-select" name="mobile_code" id="">
                                ${options}
                            </select>
                        </div>
                        <input type="number" name="credentials" class="form--control" placeholder="{{ __("Enter Phone Number") }}" value="{{ old('credentials') }}">

                    </div>
                    <small class="text-danger exits"></small>
                </div>
            `);
            $("select[name=mobile_code]").niceSelect();

            $('.agree-fields').html(`
                <div class="col-lg-12 form-group">
                    <div class="custom-check-group">
                        <input type="checkbox" id="agree" name="agree" required>
                        <label for="agree">{{ __("I have agreed with") }} <a href="{{  $policies != null? setRoute('useful.link',$policies->slug):"javascript:void(0)" }}" target="_blank">{{__("Terms Of Use & Privacy Policy")}}</a></label>
                    </div>
                </div>
            `);
        }else{
            $('.credentials').html('');
            $('.agree-fields').html('');
            return false;
        }
    }
</script>
@endpush
