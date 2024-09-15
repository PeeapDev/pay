@extends('admin.layouts.master')

@section('title',  __('Investment Plans'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules/Investment/Resources/assets/css/admin/investment_plan.min.css') }}">
@endsection

@section('page_content')
    <div class="box box-default">
        <div class="box-body">
            <div class="top-section">
                <div>
                    <div class="top-bar-title investment-plan-title pull-left">{{ __('Investment Plans') }}</div>
                </div>
                <div class="button-section">
                    @if (Common::has_permission(auth('admin')->user()->id, 'add_investment_plan'))
                        <a href="{{ route('investment_plan.add') }}" class="btn btn-theme add-plan-button pull-right f-14 "><span class="fa fa-plus"> &nbsp;</span>{{ __('Add Plan') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="main-div">
        <div class="nav_menu-div">
            <div class="tab-wrapper">
                <ul class="tabs">
                        <li class="tab-link f-14 {{ $status == 'Active' ? 'active' : '' }}" data-tab="1"> <a class="menu-tag" href="{{ route('investment_plans.list', ['status' => 'active']) }}">{{ __('Active') }}</a></li>
                        <li class="tab-link f-14 {{ $status == 'Inactive' ? 'active' : '' }}" data-tab="2"> <a class="menu-tag" href="{{ route('investment_plans.list', ['status' => 'inactive']) }}">{{ __('Inactive') }}</a></li>
                        <li class="tab-link f-14 {{ $status == 'Draft' ? 'active' : '' }}" data-tab="3"> <a class="menu-tag" href="{{ route('investment_plans.list', ['status' => 'draft']) }}">{{ __('Draft') }}</a></li>
                </ul>
                <div class="navbar-border"></div>
            </div>
                @if (Common::has_permission(auth('admin')->user()->id, 'view_investment_plan'))
                <div class="header-btn f-14">
                    <a href="javascript:;" class="button-one {{ settings('admin_investment_plan_view') == 'List' ? 'active1' : 'active2' }}" data-view="List" id="list"><i class="fa fa-list" aria-hidden="true"></i></a>
                    <a href="javascript:;" class="button-two {{ settings('admin_investment_plan_view') == 'Grid' ? 'active1' : 'active2' }}" data-view="Grid" id="grid"><i class="fa fa-th " aria-hidden="true"></i></a>
                </div>
            @endif
        </div>
        @if ($plans->isNotEmpty())
            <div class="card-position">
                @foreach ($plans as $plan)
                    <div class="card-design">
                        <!-- Name -->
                        <div class="title">
                            <p>{{ $plan->name }}</p>
                            <div class="dropdown grid-drop">
                                <div class="dropdown-toggle drop-down-icon" id="drop" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="4" height="17" viewBox="0 0 4 17" fill="none">
                                        <circle cx="1.96154" cy="1.96154" r="1.96154" fill="#C4C4C4"/>
                                        <circle cx="1.96154" cy="8.49999" r="1.96154" fill="#C4C4C4"/>
                                        <circle cx="1.96154" cy="15.0384" r="1.96154" fill="#C4C4C4"/>
                                    </svg>
                                </div>
                                <ul class="dropdown-menu drop-down-list" aria-labelledby="drop">
                                    @if (Common::has_permission(auth('admin')->user()->id, 'edit_investment_plan'))
                                        <!-- Edit Button -->
                                        <li class="dropdown-menu-list"><a href="{{ route('investment_plan.edit', $plan->id) }}"  class=" list1 d-block px-2"><svg class="drop-down-img" xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 9 9" fill="none">
                                        <path d="M6.34032 0.172707C6.29193 0.186533 5.61272 0.8571 3.15686 3.31296C0.350161 6.11966 0.03216 6.44285 0.0148773 6.50852C0.00277948 6.55864 -0.00240531 6.92158 0.00105122 7.6751L0.00623601 8.76909L0.0477143 8.83476C0.0701818 8.87106 0.120301 8.92118 0.158323 8.9471L0.229182 8.99376L1.32317 8.99895C2.08015 9.00241 2.44136 8.99722 2.49148 8.98512C2.55715 8.96784 2.87861 8.64984 5.66284 5.86906C7.36691 4.165 8.77717 2.74782 8.79446 2.71844C8.84112 2.64585 8.85495 2.53006 8.82902 2.435C8.81001 2.36415 8.71496 2.26218 7.75231 1.29781C7.16989 0.715382 6.66869 0.222826 6.63931 0.203815C6.57364 0.162337 6.42674 0.146783 6.34032 0.172707ZM7.23902 1.76962L8.0081 2.5387L5.1219 5.4249L2.2357 8.3111H1.46316H0.6889V7.53857V6.7643L3.56992 3.88329C5.15646 2.29674 6.45612 1.00054 6.4613 1.00054C6.46649 1.00054 6.8156 1.3462 7.23902 1.76962Z" fill="#635BFF"/>
                                        <path d="M6.97838 5.63746C6.77272 5.74634 6.73643 6.01077 6.90234 6.17841C7.01295 6.28902 7.04579 6.29247 7.87881 6.28556C8.66344 6.28037 8.6427 6.2821 8.7464 6.17322C8.90886 6.00212 8.86392 5.73943 8.65307 5.63055C8.59258 5.59944 8.54246 5.59771 7.82178 5.59771H7.05443L6.97838 5.63746Z" fill="#635BFF"/>
                                        <path d="M5.67705 6.97183C5.57854 7.00639 5.52669 7.0496 5.48175 7.13601C5.43509 7.22242 5.42991 7.34859 5.4662 7.435C5.49558 7.5024 5.58891 7.594 5.65458 7.6182C5.68742 7.63202 6.17133 7.63721 7.14607 7.63721C8.53041 7.63721 8.5909 7.63548 8.65312 7.60437C8.86397 7.49549 8.9089 7.23279 8.74645 7.0617C8.63929 6.94763 8.73953 6.95454 7.17027 6.95109C5.97604 6.94763 5.73408 6.95109 5.67705 6.97183Z" fill="#635BFF"/>
                                        <path d="M4.30472 8.33012C4.22349 8.35432 4.11634 8.46665 4.09214 8.55652C4.05239 8.71034 4.11288 8.87107 4.24423 8.9523L4.3099 8.99377H6.46159H8.61328L8.67895 8.9523C8.71525 8.92983 8.76537 8.87971 8.79129 8.84169C8.83104 8.78293 8.83795 8.75182 8.83795 8.65676C8.83795 8.56171 8.83104 8.5306 8.79129 8.47184C8.76537 8.43382 8.71525 8.3837 8.67895 8.36123L8.61328 8.31975L6.48751 8.31629C5.17749 8.31457 4.33928 8.31975 4.30472 8.33012Z" fill="#635BFF"/>
                                        </svg> {{ __('Edit') }}</a></li>
                                        <!-- Inactive Button -->
                                        @if($plan->status == 'Active')
                                            <li class="dropdown-menu-list"><a href="javaacript:;" data-status="Inactive"  data-id="{{ $plan->id }}" class="list1 d-block px-2 planInactive" id="inactive" class=""><svg class="drop-down-img" xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 9 9" fill="none">
                                            <path d="M4.02569 0.174591C2.3268 0.33532 0.878517 1.44659 0.275353 3.05215C0.0835156 3.56199 -0.0167237 4.16688 0.00228724 4.71128C0.0109286 4.97744 0.0351242 5.05694 0.119809 5.11915C0.169929 5.15545 0.700506 5.27124 0.814571 5.27124C0.900984 5.27124 0.980484 5.22458 1.02715 5.14681C1.0548 5.09841 1.05826 5.06731 1.05307 4.95151C1.03752 4.7061 1.04616 4.24638 1.06863 4.1133C1.18615 3.36669 1.51452 2.7186 2.03818 2.19493C2.56185 1.67127 3.19612 1.34981 3.95655 1.22365C4.12765 1.19599 4.66687 1.19599 4.86389 1.22538C5.3841 1.30142 5.90604 1.50535 6.32428 1.7957C6.4539 1.88557 6.68721 2.07395 6.68721 2.08778C6.68721 2.09296 6.6129 2.17246 6.52303 2.26406C6.43316 2.35566 6.35193 2.45244 6.34156 2.47837C6.29835 2.59416 6.3381 2.72205 6.43661 2.78254C6.46599 2.79982 6.65265 2.8655 6.8514 2.92944C7.05188 2.99166 7.43728 3.11782 7.71034 3.20769C7.98168 3.29756 8.22709 3.37015 8.25475 3.37015C8.37227 3.37015 8.4846 3.24399 8.4846 3.11091C8.4846 3.01067 8.12685 1.22019 8.09229 1.14587C8.06291 1.08366 7.98687 1.03008 7.90391 1.0128C7.81577 0.993788 7.72763 1.04391 7.57381 1.19772L7.43036 1.33771L7.35778 1.26858C7.10199 1.02317 6.63364 0.718994 6.25515 0.553081C5.53964 0.236808 4.76365 0.103732 4.02569 0.174591Z" fill="#858585"/>
                                            <path d="M7.95443 3.89897C7.81098 3.94736 7.76951 4.05279 7.79716 4.29993C7.8179 4.50386 7.80407 4.89618 7.76778 5.10703C7.63989 5.83463 7.32188 6.45334 6.80859 6.96491C6.28493 7.48857 5.65065 7.81003 4.89022 7.93619C4.71912 7.96385 4.1799 7.96385 3.98288 7.93447C3.50242 7.86533 3.01332 7.68214 2.61755 7.42463C2.46719 7.32784 2.15956 7.08934 2.15956 7.07033C2.15956 7.06688 2.23388 6.98738 2.32375 6.89578C2.41361 6.80418 2.49484 6.7074 2.50521 6.68147C2.54842 6.56568 2.50694 6.4326 2.4067 6.37557C2.38078 6.36002 2.18376 6.29089 1.96945 6.22348C1.75515 6.15435 1.36629 6.02819 1.10359 5.94178C0.579927 5.76895 0.552275 5.76549 0.446851 5.85364C0.382905 5.90894 0.358709 5.96425 0.360437 6.05757C0.360437 6.15435 0.721645 7.92928 0.754482 7.99668C0.783862 8.0589 0.859906 8.11248 0.941134 8.12976C1.03792 8.1505 1.10532 8.11248 1.26086 7.95866L1.40949 7.81176L1.57195 7.95175C2.55879 8.79341 3.89992 9.1598 5.18575 8.93513C6.87945 8.64133 8.28107 7.34167 8.70622 5.67735C8.8272 5.20208 8.87559 4.65422 8.83239 4.27055C8.80646 4.05797 8.75634 4.01822 8.41415 3.94909C8.07195 3.87823 8.02874 3.87477 7.95443 3.89897Z" fill="#858585"/>
                                            </svg>{{ __('Inactive') }}</a></li>
                                            <!-- Active Button -->
                                        @elseif($plan->status == 'Inactive')
                                            <li class="dropdown-menu-list"><a href="javaacript:;" data-status="Active"  data-id="{{ $plan->id }}" class="list1 d-block px-2 planActive" id="active"><svg class="drop-down-img" xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 9 9" fill="none">
                                            <path d="M4.02569 0.174591C2.3268 0.33532 0.878517 1.44659 0.275353 3.05215C0.0835156 3.56199 -0.0167237 4.16688 0.00228724 4.71128C0.0109286 4.97744 0.0351242 5.05694 0.119809 5.11915C0.169929 5.15545 0.700506 5.27124 0.814571 5.27124C0.900984 5.27124 0.980484 5.22458 1.02715 5.14681C1.0548 5.09841 1.05826 5.06731 1.05307 4.95151C1.03752 4.7061 1.04616 4.24638 1.06863 4.1133C1.18615 3.36669 1.51452 2.7186 2.03818 2.19493C2.56185 1.67127 3.19612 1.34981 3.95655 1.22365C4.12765 1.19599 4.66687 1.19599 4.86389 1.22538C5.3841 1.30142 5.90604 1.50535 6.32428 1.7957C6.4539 1.88557 6.68721 2.07395 6.68721 2.08778C6.68721 2.09296 6.6129 2.17246 6.52303 2.26406C6.43316 2.35566 6.35193 2.45244 6.34156 2.47837C6.29835 2.59416 6.3381 2.72205 6.43661 2.78254C6.46599 2.79982 6.65265 2.8655 6.8514 2.92944C7.05188 2.99166 7.43728 3.11782 7.71034 3.20769C7.98168 3.29756 8.22709 3.37015 8.25475 3.37015C8.37227 3.37015 8.4846 3.24399 8.4846 3.11091C8.4846 3.01067 8.12685 1.22019 8.09229 1.14587C8.06291 1.08366 7.98687 1.03008 7.90391 1.0128C7.81577 0.993788 7.72763 1.04391 7.57381 1.19772L7.43036 1.33771L7.35778 1.26858C7.10199 1.02317 6.63364 0.718994 6.25515 0.553081C5.53964 0.236808 4.76365 0.103732 4.02569 0.174591Z" fill="#858585"/>
                                            <path d="M7.95443 3.89897C7.81098 3.94736 7.76951 4.05279 7.79716 4.29993C7.8179 4.50386 7.80407 4.89618 7.76778 5.10703C7.63989 5.83463 7.32188 6.45334 6.80859 6.96491C6.28493 7.48857 5.65065 7.81003 4.89022 7.93619C4.71912 7.96385 4.1799 7.96385 3.98288 7.93447C3.50242 7.86533 3.01332 7.68214 2.61755 7.42463C2.46719 7.32784 2.15956 7.08934 2.15956 7.07033C2.15956 7.06688 2.23388 6.98738 2.32375 6.89578C2.41361 6.80418 2.49484 6.7074 2.50521 6.68147C2.54842 6.56568 2.50694 6.4326 2.4067 6.37557C2.38078 6.36002 2.18376 6.29089 1.96945 6.22348C1.75515 6.15435 1.36629 6.02819 1.10359 5.94178C0.579927 5.76895 0.552275 5.76549 0.446851 5.85364C0.382905 5.90894 0.358709 5.96425 0.360437 6.05757C0.360437 6.15435 0.721645 7.92928 0.754482 7.99668C0.783862 8.0589 0.859906 8.11248 0.941134 8.12976C1.03792 8.1505 1.10532 8.11248 1.26086 7.95866L1.40949 7.81176L1.57195 7.95175C2.55879 8.79341 3.89992 9.1598 5.18575 8.93513C6.87945 8.64133 8.28107 7.34167 8.70622 5.67735C8.8272 5.20208 8.87559 4.65422 8.83239 4.27055C8.80646 4.05797 8.75634 4.01822 8.41415 3.94909C8.07195 3.87823 8.02874 3.87477 7.95443 3.89897Z" fill="#858585"/>
                                            </svg>{{ __('Active') }}</a></li>
                                        @endif
                                    @endif
                                    <!-- Delete Button -->
                                    @if (Common::has_permission(auth('admin')->user()->id, 'delete_investment_plan'))
                                    <li class="dropdown-menu-list"><a href="javascript:;" data-id="{{ $plan->id }}" id="delete" class=" list1 d-block px-2 delete"><svg class="drop-down-img" xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 9 9" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.7 0.45C2.7 0.201472 2.90147 0 3.15 0H5.85C6.09853 0 6.3 0.201472 6.3 0.45C6.3 0.698528 6.09853 0.9 5.85 0.9H3.15C2.90147 0.9 2.7 0.698528 2.7 0.45ZM1.34651 1.35H0.45C0.201472 1.35 0 1.55147 0 1.8C0 2.04853 0.201472 2.25 0.45 2.25H0.929001L1.21776 6.58145C1.24042 6.92139 1.25917 7.20271 1.29279 7.4317C1.3278 7.6701 1.38332 7.88796 1.49899 8.091C1.67905 8.40707 1.95066 8.66117 2.278 8.81981C2.48829 8.92172 2.70936 8.96263 2.94957 8.9817C3.18028 9.00001 3.46222 9.00001 3.80292 9H5.19708C5.53778 9.00001 5.81972 9.00001 6.05043 8.9817C6.29064 8.96263 6.51171 8.92172 6.722 8.81981C7.04934 8.66117 7.32095 8.40707 7.50101 8.091C7.61668 7.88796 7.6722 7.6701 7.70721 7.43169C7.74083 7.2027 7.75958 6.92136 7.78224 6.58139L8.071 2.25H8.55C8.79853 2.25 9 2.04853 9 1.8C9 1.55147 8.79853 1.35 8.55 1.35H7.65349C7.65086 1.34998 7.64824 1.34998 7.64562 1.35H1.35438C1.35176 1.34998 1.34914 1.34998 1.34651 1.35ZM7.169 2.25H1.831L2.11458 6.50375C2.13873 6.866 2.15543 7.11151 2.18325 7.30095C2.21026 7.48496 2.24327 7.57928 2.28099 7.6455C2.37103 7.80354 2.50683 7.93059 2.6705 8.0099C2.73908 8.04314 2.83539 8.0698 3.02079 8.08452C3.21166 8.09967 3.45773 8.1 3.82079 8.1H5.1792C5.54227 8.1 5.78834 8.09967 5.97921 8.08452C6.16461 8.0698 6.26092 8.04314 6.3295 8.0099C6.49317 7.93059 6.62897 7.80354 6.71901 7.6455C6.75673 7.57928 6.78974 7.48496 6.81675 7.30095C6.84457 7.11151 6.86127 6.866 6.88542 6.50375L7.169 2.25ZM3.6 3.375C3.84853 3.375 4.05 3.57647 4.05 3.825V6.075C4.05 6.32353 3.84853 6.525 3.6 6.525C3.35147 6.525 3.15 6.32353 3.15 6.075V3.825C3.15 3.57647 3.35147 3.375 3.6 3.375ZM5.4 3.375C5.64853 3.375 5.85 3.57647 5.85 3.825V6.075C5.85 6.32353 5.64853 6.525 5.4 6.525C5.15147 6.525 4.95 6.32353 4.95 6.075V3.825C4.95 3.57647 5.15147 3.375 5.4 3.375Z" fill="#858585"/>
                                        </svg>{{ __('Delete') }}</a></li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="des">{{ $plan->description }}</p>

                        <!-- Profit & Duration -->
                        <div class="profit-duration">
                            <div>
                                <div class="percentage-div">
                                    <p class="percentage">{{ formatNumber($plan->interest_rate, $plan->currency_id) }}</p>
                                    <p class="percentage-sign">
                                        @if ($plan->interest_rate_type == 'Percent')
                                            {{ '%' }}
                                        @elseif ($plan->interest_rate_type == 'APR') 
                                            {{'% (APR)' }}
                                        @else 
                                            {{ optional($plan->currency)->code }}
                                        @endif
                                    </p>
                                </div>
                                <p class="profit-duration-paragraph profit">
                                    {{ __('Profit') }}
                                </p>
                            </div>
                            <div>
                                <div class="percentage-div">
                                    <p class="percentage">{{ $plan->term }}</p>
                                    <p class="percentage-sign">{{ Str::plural($plan->term_type, $plan->term) }}</p>
                                </div>
                                <p class="profit-duration-paragraph">{{ __('Duration') }}</p>
                            </div>
                        </div>

                        <div class="amount-type-div">
                            <div>
                                <!-- Amount -->
                                <div>
                                    <p class="paragraph-5">{{ $plan->investment_type == 'Fixed' ? __('Amount') : __('Min Amount') }}</p>
                                    <p class="paragraph-6">{{ formatNumber($plan->amount, $plan->currency_id) .' '. optional($plan->currency)->code }}</p>
                                </div>
                                <!-- Capital return term -->
                                <div class="second-part">
                                    <p class="paragraph-5">{{ __('Capital Return') }}</p>
                                    <p class="paragraph-6">{{ $plan->capital_return_term }}</p>
                                </div>
                            </div>

                            <div>
                                <!-- Type -->
                                <div>
                                    @if ($plan->investment_type == 'Fixed')
                                        <p class="paragraph-5">{{ __('Type') }}</p>
                                        <p class="paragraph-6">{{ $plan->investment_type }}</p>
                                    @else
                                        <p class="paragraph-5">{{ __('Max Amount') }}</p>
                                        <p class="paragraph-6">{{ formatNumber($plan->maximum_amount, $plan->currency_id) .' '. optional($plan->currency)->code}}</p>
                                    @endif
                                    
                                </div>

                                <!-- Profit Adjust -->
                                <div class=" second-part">
                                    <p class="paragraph-5">{{ __('Profit Adjust') }}</p>
                                    <p class="paragraph-6">{{ $plan->interest_time_frame }}</p>
                                </div>
                            </div>
                        </div>
                    </div> 
                @endforeach
            </div>
        @else
            <div class="box mt-2">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-info">
                                <div class="panel-body text-center">
                                    <img src="{{ asset('public/dist/images/not-found.png') }}" alt="notfound">
                                    <p class="mt-2">{{ __('Sorry! No :x investment plan found.', ['x' => strtolower($status)]) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        {{ $plans->links() }}
    </div>
@endsection

@push('extra_body_scripts')

    <script src="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js') }}" type="text/javascript"></script>
    <script>
        'use strict';
        var alertTitle = "{{__('Are you sure?') }}";
        var alertText = "{{__('You will not be able to revert this.') }}";
        var alertConfirm = "{{__('Yes, change status.') }}";
        var deleteConfirm = "{{__('Yes, delete this plan.') }}";
        var alertCancel = "{{__('Cancel') }}";
        var failedText = "{{ __('Failed') }}";
        var waitText = "{{ __('Please Wait') }}";
        var LoadingText = "{{ __('Loading...') }}";
        var statusChangeUrl = "{{ route('investment_plan.status') }}";
        var viewChangeUrl = "{{ route('investment_plan.view_change') }}";
    </script>
    <script src="{{ asset('Modules/Investment/Resources/assets/js/admin/investment_plan.min.js')}}" type="text/javascript"></script>
@endpush
