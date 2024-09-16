@extends('frontend.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('Modules/CryptoExchange/Resources/assets/landing/css/style.min.css') }}">
    <link href="{{ asset('Modules/CryptoExchange/Resources/assets/landing/css/scrolling.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="crypto-first-section px-240p">
    <div class="container-fluid pt-215 pb-131">
        <div class="row px-vw">
            <div class="col-md-5 col-lg-4 mt-mid-40p">
                <div class="d-flex step-div-parent">
                    <div class="step-div ml-11n">
                        <div class="steper-div-2 d-flex">
                            <div class="first-circle border-set" id="first-circle">
                                <div class="second-circle border-set bg-set" id="second-circle">
                                    <div class="third-circle visible" id="third-circle">
                                        <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/success.svg') }}" alt="success" class="success-img img-fluid">
                                    </div>
                                    <div class="curent-second-circle curent-display" id="curent-second-circle"></div>
                                </div>
                            </div>
                            <div class="text-center d-flex align-items-center ml-19"><p class="align-items-center crypto-font-22 OpenSans-600 status-color mb-unset">{{ __('Start Exchange') }}</p></div>
                        </div>

                        <div class="cp-exchange-stick-step complete-ml-step-success"></div>

                        <div class="steper-div-2 d-flex">
                            <div class="first-circle border-set" id="first-circle_step2">
                                <div class="second-circle border-set bg-set" id="second-circle_step2">
                                    <div class="third-circle visible" id="third-circle_step2">
                                        <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/success.svg') }}" alt="success" class="success-img img-fluid">
                                    </div>
                                    <div class="curent-second-circle curent-display" id="curent-second-circle_step2">

                                    </div>
                                </div>
                            </div>
                            <div class="text-center d-flex align-items-center ml-19"> <p class="crypto-font-22 OpenSans-600 status-color mb-unset">{{ __('Verify your Identity') }}</p></div>
                        </div>

                        <div class="cp-exchange-stick-step complete-ml-step-success"></div>

                        <div class="steper-div-2 d-flex">
                            <div class="first-circle border-set" id="first-circle_step3">
                                <div class="second-circle border-set bg-set" id="second-circle_step3">
                                    <div class="third-circle visible" id="third-circle_step3">
                                        <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/success.svg') }}" alt="success"
                                            class="success-img img-fluid">
                                    </div>
                                    <div class="curent-second-circle curent-display"
                                        id="curent-second-circle_step3">
                                    </div>
                                </div>
                            </div>
                            <div class="text-center d-flex align-items-center ml-19">
                                <p class="crypto-font-22 OpenSans-600 status-color mb-unset">
                                    @if($exchange_type == 'crypto_sell')
                                        {{ __('Receiving Account Details') }}
                                    @else
                                        {{ __('Provide Crypto Address') }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="cp-exchange-stick-step complete-ml-step-success"></div>

                        <div class="steper-div-2 d-flex">
                            <div class="first-circle border-set" id="first-circle_step4">
                                <div class="second-circle border-set bg-set" id="second-circle_step4">
                                    <div class="third-circle visible" id="third-circle_step4">
                                        <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/success.svg') }}" alt="success"
                                            class="success-img img-fluid">
                                    </div>
                                    <div class="curent-second-circle curent-display"
                                        id="curent-second-circle_step4">

                                    </div>
                                </div>
                            </div>
                            <div class="text-center d-flex align-items-center ml-19"> <p class="crypto-font-22 OpenSans-600 status-color mb-unset">{{ __('Make Payment') }}</p></div>
                        </div>

                        <div class="cp-exchange-stick-step complete-ml-step-success"></div>

                        <div class="steper-div-2 d-flex">
                            <div class="first-circle border-set" id="first-circle_step5">
                                <div class="second-circle border-set bg-set" id="second-circle_step5">
                                    <div class="third-circle visible" id="third-circle_step5">
                                        <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/success.svg') }}" alt="success"
                                            class="success-img img-fluid">
                                    </div>
                                    <div class="curent-second-circle curent-display" id="curent-second-circle_step5">
                                    </div>
                                </div>
                            </div>
                            <div class="text-center d-flex align-items-center ml-19"> <p class="crypto-font-22 OpenSans-600 status-color-active mb-unset">{{ __('Complete Transaction') }}</p></div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-md-7 col-lg-8 pl-203p">
                <div class="crypto-box mob-mt-40">
                    <div class="box-header">
                        <div class="d-flex justify-content-center">
                            <p class="font-20 OpenSans-600 c-white text-center mb-unset">
                                {{ __(ucwords(str_replace("_"," ", $exchange_type))) }}
                            </p>
                        </div>
                    </div>

                    <div class="box-body box-border pr-28">
                       <div class="d-flex justify-content-center">
                           <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/success2.svg') }}" alt="success" class="img-fluid w-responsive">
                       </div>
                       <div class="d-flex flex-column justify-content-center mt-37">
                         <p class="text-center font-32 OpenSans-600 c-blublack text-uppercase mb-unset">
                             {{ __('Transaction Completed') }}
                         </p>
                         <p class="font-20-lineh-23 c-blublack OpenSans-400 mb-unset text-center mt-7">
                            {{ __('Please wait for admin approval') }}
                         </p>
                       </div>
                       <div class="d-flex flex-column justify-content-center text-center total-div border mt-46">
                        <span class="font-16 OpenSans-400 c-blublack">{{ __('Total') }}</span>
                        <div class="d-flex justify-content-center">
                            <div><span class="font-28 OpenSans-600 c-blublack">{{  $send_amount }} {{ $from_currency_code }} </span></div>
                            <div class="ml-10 mt-2 align-items-center">
                                <img class="c-dimension img-fluid" src="{{ image($from_currency_logo, 'currency') }}" alt="{{ $from_currency_code }}">
                            </div>
                        </div>
                       </div>
                       <div class="cp-stick"></div>
                       <div class="d-flex flex-column justify-content-center text-center total-div border">
                        <span class="font-16 OpenSans-400 c-blublack">{{ __('Getting Amount') }}</span>
                        <div class="d-flex justify-content-center">
                            <div><span class="font-28 OpenSans-600 c-blublack">{{ $get_amount }} {{ $to_currency_code }}</span></div>
                            <div class="ml-10 mt-2 align-items-center">
                               <img class="c-dimension img-fluid" src="{{ image($to_currency_logo, 'currency') }}" alt="{{ $to_currency_code }}">
                            </div>
                        </div>
                       </div>

                        <p class="mt-2 text-center a-tag track-tag"><strong class="color-E8">{{ __('Track the transaction') }}:</strong> <a href="{{ $trackUrl }}" target="_blank">{{ $trackUrl }}</a> </p>

                       <div class="d-flex flex-column justify-content-center text-center total-div mt-10">
                        <div class="flex-btn d-flex justify-content-center">

                                <a href="{{ route('crypto_exchange.print', $id ) }}" target="_blank" class="print-btn d-flex print-bg mob-mb-12">
                                <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/print.svg') }}" alt="print">
                                <span class="ml-11 text-light font-16 OpenSans-400"> {{ __('Print') }}
                                </span>
                                </a>

                                <a href="{{ !is_null($user_id) ? route('user_dashboard.crypto_buy_sell.create') :
                                            route('guest.crypto_exchange.home') }}" 
                                    class="font-15 OpenSans-400 c-blublack mb-unset ml-24 print-btn-again d-flex justify-content-center align-items-center" >
                                    {{ __('Exchange Again') }} 
                                </a>
                                
                        </div>
                       </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection

