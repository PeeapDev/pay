@extends('frontend.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('Modules/CryptoExchange/Resources/assets/landing/css/style.min.css') }}">
    <link href="{{ asset('Modules/CryptoExchange/Resources/assets/landing/css/scrolling.min.css') }}" rel="stylesheet">
@endsection

@section('content')

<div class="crypto-first-section px-240p">
    <div class="container-fluid pt-215 pb-131">
        <div class="row px-vw">
        <div class="col-md-5 col-lg-4">
                <div class="d-flex step-div-parent mt-mid-40p">
                    <div class="step-div ml-11n">
                        <!--  status end for 1st_step_status-->
                        <!--check status start for 2nd_step_status-->

                        <div class="steper-div-2 d-flex">

                            @if($transInfo->status == 'Success')
                            <div class="first-circle border-set" id="first-circle">
                                <div class="second-circle border-set bg-set" id="second-circle">
                                    <div class="third-circle visible" id="third-circle">
                                        <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/success.svg') }}" alt="success"
                                            class="success-img img-fluid">
                                    </div>
                                    <div class="curent-second-circle curent-display" id="curent-second-circle">

                                    </div>
                                </div>
                            </div>

                            <div class=" d-flex align-items-center ml-28">
                                <p class="crypto-font-22 OpenSans-600 status-color-active mb-unset">{{ __('Admin Approval') }}</p>
                            </div>
                            @else
                            <div class="second-circle border-set bg-unset">
                                <div class="curent-second-circle curent-display-show">
                                </div>
                            </div>
                            <div class=" d-flex align-items-center ml-28">
                                <p class="crypto-font-22 OpenSans-600 status-color-active mb-unset">{{ __('Waiting for Admin Approval') }}</p>
                            </div>
                            @endif

                        </div>
                        <div class="exchange-stick-step ml-step-21"></div>
                        <!-- status end for 2nd_step_status-->
                        <!--check status start for 3rd_step_status-->
                        <div class="steper-div-2 d-flex">

                            @if($transInfo->status == 'Success')
                            <div class="first-circle border-set" id="first-circle">
                                <div class="second-circle border-set bg-set" id="second-circle">
                                    <div class="third-circle visible" id="third-circle">
                                        <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/success.svg') }}" alt="success"
                                            class="success-img img-fluid">
                                    </div>
                                    <div class="curent-second-circle curent-display" id="curent-second-circle">

                                    </div>
                                </div>
                            </div>

                            <div class=" d-flex align-items-center ml-28">
                                <p class="crypto-font-22 OpenSans-600 status-color-active mb-unset">
                                 {{ __('Transaction Success') }}
                                </p>
                            </div>


                            @else
                            <div class="second-circle border-set bg-unset">
                            </div>
                             <div class=" d-flex align-items-center ml-28">
                                <p class="crypto-font-22 OpenSans-600 status-color mb-unset">
                                 {{ __('Transaction Success') }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 col-lg-8 pl-203p">
                <div class="crypto-box mob-mt-40">
                    <div class="box-header">
                        <div class="d-flex justify-content-center">
                            <p class="font-20 OpenSans-600 c-white text-center mb-unset">
                                {{ __(ucwords(str_replace("_"," ", $transInfo->type))) }}
                            </p>
                        </div>
                    </div>
                    <div class="box-body box-border pr-28 mt-16">
                        <div class="d-flex justify-content-between">
                            <p class="font-18 OpenSans-400 c-blublack mb-0 p-0">{{ __('Transaction Status') }}</p>
                            <div class="d-flex justify-content-center">
                                <div>
                                    <span class="font-18 OpenSans-600 c-blublack">{{ getStatus($transInfo->status) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-18">
                            <p class="font-18 OpenSans-400 c-blublack mb-0 p-0">{{ __('Transaction ID') }}</p>
                            <div class="d-flex justify-content-center">
                                <div>
                                    <span class="font-18 OpenSans-600 c-blublack">{{ $transInfo->uuid }} </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-18">
                            <p class="font-18 OpenSans-400 c-blublack mb-0 p-0">{{ __('Exchange By') }}</p>
                            <div class="d-flex justify-content-center">
                                <div>
                                    <span class="font-18 OpenSans-600 c-blublack">
                                    {{ !empty($transInfo->email_phone) ? $transInfo->email_phone : getColumnValue($transInfo->user) }}
                                   </span>
                                </div>
                            </div>
                        </div>

                         <div class="d-flex justify-content-between mt-18 mtop-">
                            <p class="font-18 OpenSans-400 c-blublack mb-0 p-0">{{ __('Exchanged Time') }}</p>
                            <div class="d-flex justify-content-center">
                                <div>
                                    <span class="font-18 OpenSans-600 c-blublack">
                                        {{ dateFormat($transInfo->created_at) }}
                                    </span>
                                </div>
                            </div>
                         </div>

                        @if($transInfo->type !== 'crypto_sell' && $transInfo->receive_via == 'address')
                            <div class="d-flex justify-content-between mt-18">
                                <p class="font-18 OpenSans-400 c-blublack mb-0 p-0">{{ __('Addrees') }}</p>
                                <div class="d-flex justify-content-center">
                                    <span class="font-18 OpenSans-600 c-blublack">{{ $transInfo->receiver_address }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between mt-18">
                            <p class="font-18 OpenSans-400 c-blublack mb-0 p-0">{{ __('Fee') }}</p>
                            <div class="d-flex justify-content-center">
                                <div>
                                    <span class="font-18 OpenSans-600 c-blublack">{{  formatNumber($transInfo->fee, $transInfo->from_currency) }} {{ optional($transInfo->fromCurrency)->code }} </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column justify-content-center text-center total-div border mt-18">
                            <span class="font-16 OpenSans-400 c-blublack">{{ __('Send Amount') }}</span>
                            <div class="d-flex justify-content-center">
                                <div>
                                    <span class="font-28 OpenSans-600 c-blublack">{{  formatNumber($transInfo->amount, $transInfo->from_currency) }} {{ optional($transInfo->fromCurrency)->code }} </span>
                                </div>

                                <div class="ml-11 mt-2 align-items-center">
                                    <img class="c-dimension img-fluid" src="{{ image(optional($transInfo->fromCurrency)->logo, 'currency') }}" alt="{{ optional($transInfo->fromCurrency)->code }}">
                                </div>
                            </div>
                       </div>

                       <div class="divider-div-parent d-flex justify-content-center">
                            <div class="divider-div">
                            </div>
                       </div>

                       <div class="d-flex flex-column justify-content-center text-center total-div border">
                            <span class="font-16 OpenSans-400 c-blublack">{{ __('Getting Amount') }}</span>
                            <div class="d-flex justify-content-center">
                                <div><span class="font-28 OpenSans-600 c-blublack">{{ formatNumber($transInfo->get_amount, $transInfo->to_currency) }} {{ optional($transInfo->toCurrency)->code }}</span></div>

                                <div class="ml-11 mt-2 align-items-center">
                                    <img class="c-dimension img-fluid" src="{{ image(optional($transInfo->toCurrency)->logo, 'currency') }}" alt="{{ optional($transInfo->toCurrency)->code }}">
                                </div>
                            </div>
                        </div>

                       <div class="text-center mt-18">
                            <p class="font-18 OpenSans-400 c-blublack mb-0 p-0">{{ __('Exchange Rate') }}</p>
                            <div class="d-flex justify-content-center mt-9">
                                <div>
                                    <span class="font-18 OpenSans-600 c-blublack"> 1
                                    {{ optional($transInfo->fromCurrency)->code }} = {{ formatNumber($transInfo->exchange_rate, $transInfo->to_currency) }} {{ optional($transInfo->toCurrency)->code }} </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





