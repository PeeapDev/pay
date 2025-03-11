@extends('admin.auth.layouts.auth-master')

@push('css')
    <style>
        .sub-title {
            margin-top: 0px !important;
        }
    </style>
@endpush

@section('section')
    <div class="account-wrapper">
        <div class="account-header">
            <div class="site-logo">
                <img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                    data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                        alt="site-logo">
            </div>
            <h6 class="sub-title">{{ __("Password ") }} <span>{{ __("Reset") }}</span></h6>
            <span>{{ __("Enter your account email address or username") }}</span>
        </div>
        <form class="account-form" action="{{ setRoute('admin.password.forgot.request') }}" method="POST">
            @csrf
            <div class="form-group">
                <input type="text" class="@error('credential') is-invalid @enderror" title="Enter Username" required name="credential" value="{{ old('credential') }}" autofocus>
                <label>{{ __("Username OR Email Address") }}</label>

                @error('credential')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

            </div>
            <x-security.google-recaptcha-field />
            <button type="submit" class="btn--base w-100 btn-loading">{{ __("send") }}</button>
        </form>
    </div>
@endsection
