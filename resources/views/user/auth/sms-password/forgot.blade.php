@extends('user.layouts.user_auth')

@push('css')

@endpush

@section('content')

<section class="account">

    <div class="account-area">
        <div class="account-wrapper">
            <div class="account-logo text-center">
               <a class="site-logo" href="{{ setRoute('index') }}">
                <img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                    alt="site-logo">
               </a>
            </div>
            <h5 class="title">{{ $page_title }}</h5>
            <p>{{ __("Please input your mobile number and get verify code  for recovering your account.") }}</p>
            <form class="account-form" action="{{ route('user.password.send.code') }}" method="POST">
                @csrf
                <div class="row ml-b-20">

                    <div class="col-xl-12 col-lg-12 form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text copytext">+{{ getDialCode() }}</span>
                            </div>
                            <input type="number" name="mobile" class="form--control mobile" placeholder="Enter Number" value="{{ old('mobile') }}">

                        </div>
                        <small class="text-danger exits"></small>
                    </div>
                    <div class="col-lg-12 form-group text-center">
                        <button type="submit"  class="btn--base w-100 btn-loading">{{__("Continue")}}</button>
                    </div>
                    <div class="col-lg-12 text-center">
                        <div class="account-item">
                            <label>{{ __("Already Have An Account?") }} <a href="{{ setRoute('user.login') }}" class="account-control-btn">{{ __("Login Now") }}</a></label>
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
@endsection

@push('script')
@endpush
