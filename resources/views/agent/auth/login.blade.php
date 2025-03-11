
@extends('agent.layouts.user_auth')

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
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start acount
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<section class="account">
    <div class="account-area">
        <div class="account-wrapper">
            <div class="account-logo text-center">
                <a href="{{ setRoute('index') }}" class="site-logo">
                    <img src="{{ get_logo_agent($basic_settings) }}"  data-white_img="{{ get_logo_agent($basic_settings,'white') }}"
                            data-dark_img="{{ get_logo_agent($basic_settings,'dark') }}"
                                alt="site-logo">
                </a>
            </div>
            <h5 class="title">{{ __("Log in and Stay Connected") }}</h5>
            <p>{{ __(@$auth_text->value->language->$lang->login_text) }}</p>
            <form class="account-form" action="{{ setRoute('agent.login.submit') }}" method="POST">
                @csrf
                <div class="row ml-b-20">
                    <div class="col-xl-12 col-lg-12  form-group">
                        <label>{{ __("Login Type") }} <span class="text--base">*</span></label>
                        <select class="form--control nice-select" name="login_type">
                            <option value="" disabled  selected>{{ __("Select One") }}</option>
                            <option value="{{ global_const()::PHONE }}">{{ global_const()::PHONE }}</option>
                            <option value="{{ global_const()::EMAIL }}">{{ global_const()::EMAIL }}</option>
                        </select>
                    </div>
                    <div class="credentials"> </div>
                    <div class="password-field"> </div>
                    <div class="col-lg-12 form-group">
                        <div class="forgot-item">
                            <label><a href="{{ setRoute('agent.password.forgot') }}">{{ __("Forgot Password") }}?</a></label>
                        </div>
                    </div>
                    <div class="col-lg-12 form-group text-center">
                        <x-security.google-recaptcha-field />
                        <button type="submit" class="btn--base w-100 btn-loading">{{ __("Login Now") }} <i class="las la-arrow-right"></i></button>
                    </div>
                    @if($basic_settings->agent_registration)
                    <div class="or-area">
                        <span class="or-line"></span>
                        <span class="or-title">{{ __("Or") }}</span>
                        <span class="or-line"></span>
                    </div>
                    <div class="col-lg-12 text-center">
                        <div class="account-item">
                            <label>{{ __("Don't Have An Account?") }} <a href="{{ setRoute('agent.register') }}" class="account-control-btn">{{ __("Register Now") }}</a></label>
                        </div>
                    </div>
                    @endif
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
    $countries = freedom_countries(global_const()::AGENT);
@endphp
@endsection

@push('script')
<script>
    $(document).on("click","#show_hide_password a",function(event){
        event.preventDefault();
        if($('#show_hide_password input').attr("type") == "text"){
            $('#show_hide_password input').attr('type', 'password');
            $('#show_hide_password i').addClass( "fa-eye-slash" );
            $('#show_hide_password i').removeClass( "fa-eye" );
        }else if($('#show_hide_password input').attr("type") == "password"){
            $('#show_hide_password input').attr('type', 'text');
            $('#show_hide_password i').removeClass( "fa-eye-slash" );
            $('#show_hide_password i').addClass( "fa-eye" );
        }
    });
</script>
<script>
    $(document).ready(function(){
        setLoginType();
    });
    $("select[name=login_type]").change(function(){
        setLoginType();
    });
     function acceptVar() {
        var selectedVal = $("select[name=login_type] :selected");
        return {
            selectedVal:selectedVal,

        };
    }
    function setLoginType(){
        var type = acceptVar().selectedVal.val();
        if(type == "{{ global_const()::EMAIL }}"){
            $('.credentials').html('');
            $('.password-field').html('');
            $('.credentials').html(`
                    <div class="col-xl-12 col-lg-12 form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text copytext">{{ __("Email")}}</span>
                            </div>
                              <input type="email" name="credentials" class="form--control" placeholder="{{ __("enter Email Address") }}" value="{{ old('credentials') }}">
                        </div>
                    </div>
                    `);
            $('.password-field').html(`
                        <div class="col-lg-12 form-group" id="show_hide_password">
                            <input type="password" required class="form-control form--control" name="password" placeholder="{{ __('Enter Password') }}">
                            <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                        </div>
                    `);
        }else if(type == "{{ global_const()::PHONE }}"){
            $('.credentials').html('');
            $('.password-field').html('');
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
            $('.password-field').html(`
                <div class="col-lg-12 form-group" id="show_hide_password">
                    <input type="password" required class="form-control form--control" name="password" placeholder="{{ __('Enter Password') }}">
                    <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                </div>
            `);
        }else{
            $('.credentials').html('');
            $('.password-field').html('');
            return false;
        }
    }
</script>
@endpush
