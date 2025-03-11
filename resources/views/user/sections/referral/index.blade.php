@extends('user.layouts.master')
@section('content')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Dashboard
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<div class="body-wrapper">
        <div class="row justify-content-center mb-30-none">
            <div class="col-xxl-5 col-xl-4 col-lg-8 col-md-12 mb-30">
                <div class="my-status-card">
                    <div class="thumb-area-wrapper">
                        <div class="thumb-area">
                            <img src="{{ auth()->user()->userImage ?? "" }}" alt="image">
                            <div class="avatar-level-badge">
                                <span>{{ $auth_user->referLevel?->title ?? "" }}</span>
                            </div>
                        </div>
                    </div>
                    <ul class="my-status-list">
                        <li>{{ __("Total Refers") }}: <span>{{ $auth_user->referUsers->count() }}</span></li>
                        <li>{{ __("Total Deposit") }}: <span>{{ get_amount(totalDeposit($auth_user->depositAmount),$default_currency?->code ?? "") }}</span></li>
                        <li>{{ __("Current Position") }}: <span>{{ $auth_user->referLevel?->title ?? "" }}</span></li>
                        <input type="hidden" value="{{ $auth_user->referral_id }}" id="copy-share-link2" >
                        <li>{{ __("Refer Code") }}:
                            <div>
                                <span id="referralCode">{{ $auth_user->referral_id }}</span>
                                <button class="copytext"><i class="las la-copy"></i></button>
                            </div>
                        </li>
                    </ul>

                </div>
            </div>
            <div class="col-xxl-7 col-xl-8 col-md-12 mb-30">
                <div class="my-status-card">
                    <div class="account-level-wrapper h-100">
                        <h3 class="title">{{ __("Account Level") }}</h3>
                        <div class="row mb-30-none">
                            @php
                                $auth_user_earned_levels_ids = $auth_user->earnedLevels->pluck("referral_level_package_id")->toArray();
                            @endphp
                            @foreach ($account_level as $key => $item)
                            @php
                                $current_refer_id = $auth_user->referLevel?->id ?? "";
                            @endphp
                                <div class="col-lg-4 col-md-4 col-sm-6 mb-30">
                                    <div class="account-level-item   @if ($current_refer_id == $item->id) curent @endif
                                    @if (!in_array($item->id,$auth_user_earned_levels_ids) && $current_refer_id != $item->id)
                                        off
                                    @endif ">
                                        <div class="account-level-header">
                                            <span>{{ @$item->title }}</span>
                                        </div>
                                        <div class="content">
                                            <h6 class="level-title">{{ __("Requirement") }}</h6>
                                            <ul>
                                                <li>{{ __("Require Refers") }}: <span>{{ @$item->refer_user }}</span></li>
                                                <li>{{ __("deposit") }}: <span>{{ get_amount($item->deposit_amount) }} {{ $default_currency->code }}</span></li>
                                            </ul>
                                            <h6 class="level-title">{{ __("Commission") }}</h6>
                                            <ul>
                                                <li>{{ __("Per Refer") }}: <span>{{ get_amount($item->commission) }} {{ $default_currency->code }}</span></li>
                                            </ul>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-area ptb-30">
            <div class="table-wrapper">
                <div class="dashboard-header-wrapper">
                    <h4 class="title">{{ __("Referral Users") }}</h4>
                    <div class="level-search-area">
                        <input type="search"  name="user_search" class="form--control" placeholder="{{ __("search")."..."}}">
                        <button type="button"><i class="las la-search"></i></button>
                    </div>
                </div>
                <div class="table-responsive">
                    @include('user.components.data-table.user-table',compact('refer_users'))
                </div>
                {{ get_paginate($refer_users) }}
            </div>
        </div>
</div>


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Dashboard
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

@endsection
@push('script')
<script>
    itemSearch($("input[name=user_search]"),$(".user-search-table"),"{{ setRoute('user.refer.level.search') }}");
    $(document).ready(function () {
    $('.copytext').on('click', function() {
        var referralCode = document.getElementById("referralCode").innerText;
        var tempInput = document.createElement("input");
        document.body.appendChild(tempInput);
        tempInput.setAttribute("value", referralCode);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        throwMessage('success', ["{{ __('Copied') }}"+": " + referralCode]);
    });
});


</script>
@endpush
