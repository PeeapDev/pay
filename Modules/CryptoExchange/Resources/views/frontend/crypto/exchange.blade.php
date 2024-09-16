@extends('frontend.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('Modules/CryptoExchange/Resources/assets/landing/css/style.min.css') }}">
    <link rel="stylesheet" href="{{ asset('Modules/CryptoExchange/Resources/assets/landing/css/scrolling.min.css') }}">

@endsection

@section('content')
    <div class="navandbody-section p-main" id="crypto-front-initiate">
        <!-- Crypto exchange section -->
        <div>
            <div class="container-fluid px-240p pb-10 row-head">
                <div class="row">
                    <div class="col-lg-6  col-sm-12 col-sm-12 col-xs-12 mw-auto">
                        <div class="pt-95">
                            <p class="f-21 OpenSans-600 color-CD" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                                {{ __('THE SAFEST & MOST RELIABLE') }}</p>
                            <p class="bold-text p-0">
                                <span class="crypto-text OpenSans-700 color-E8">{{ __('CRYPTO') }}</span><br>
                                <span class="exchange-text OpenSans-700 color-FF">{{ __('EXCHANGE') }}</span>
                            </p>
                            <div class="OpenSans-400 font-20 text-width color-CD">
                                {{ __('Buy, sell, and exchange most popular cryptocurrencies on :x easily, safely & securely with low fees in just a few minutes.', ['x' => settings('name')]) }}
                            </div>
                            <p class="OpenSans-400 font-16 col-md-11 mulish4 c-blue2"></p>
                            <p class="font-22 OpenSans-600 c-blue2 mt-38 color-E8">{{ __('Let\'s Get Started..') }}</p>
                            <div
                                class="button-widths d-flex justify-content-between align-items-center mt-14 cursor-pointer btn-animate text-light">
                                <a href="{{ url('/register') }}" class="OpenSans-600">
                                    {{ __('Create an Account') }}
                                </a>
                                <div class="ml-27p svg-img-parent">
                                    <div class="svg-img">
                                        <svg width="54" height="54" viewBox="0 0 54 54" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect width="54" height="54" rx="6" fill="none"></rect>
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M15.8181 39.1818C15.4164 38.7801 15.4164 38.1289 15.8181 37.7273L34.7272 18.8182C35.1288 18.4165 35.7801 18.4165 36.1817 18.8182C36.5834 19.2198 36.5834 19.8711 36.1817 20.2727L17.2726 39.1818C16.871 39.5835 16.2198 39.5835 15.8181 39.1818Z"
                                                fill="#403E5B"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M24.2441 19.5454C24.2441 18.9774 24.7046 18.5169 25.2727 18.5169L35.4545 18.5169C36.0225 18.5169 36.483 18.9774 36.483 19.5454L36.483 29.7273C36.483 30.2953 36.0225 30.7558 35.4545 30.7558C34.8864 30.7558 34.426 30.2953 34.426 29.7273L34.426 20.574L25.2727 20.574C24.7046 20.574 24.2441 20.1135 24.2441 19.5454Z"
                                                fill="#403E5B"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('cryptoexchange::frontend.crypto.exchange_tab')
                </div>
            </div>
        </div>
        <!-- Crypto exchange section End-->
    </div>

    <!-- Buy Sell Exchange section -->
    <div class="pt-100">
        <div class="px-240p">
            <div class="text-center">
                <div data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                    <span class="OpenSans-600 font-24 c-blue3">{{ __('BUY SELL EXCHANGE') }}</span>
                </div>
                <p class="OpenSans-600 font-60 c-blublack" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                    {{ __('YOUR TRUSTED CRYPTO EXCHANGE') }}</p>
                <div class="d-flex flex-row justify-content-center" data-aos="fade-up"
                    data-aos-anchor-placement="top-bottom">
                    <hr class="new4">
                </div>
            </div>

            <div class="d-flex gap-32 mt-44 flx-wrap" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                <div class="cryptobox1 flex-basis-full">
                    <p class="OpenSans-600 font-32 c-blublack f-18-res">{{ __('Fast Crypto Exchange') }}</p>
                    <p class="OpenSans-400 font-20 c-blublack mb_unset f-14-res mt-3">
                        {{ __(':x is the easiest place to buy, sell & exchange cryptocurrency. Verify your identity started now..', ['x' => settings('name')]) }}
                    </p>
                    <div class="float-right">
                        <img class="mtop-23"
                            src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/cryto-bg.svg') }}"
                            alt="cryto-bg.svg">
                    </div>
                </div>
                <div class="cryptobox2 flex-basis-full">
                    <div class="row">
                        <img class="mt-23 crypto-fiat-img"
                            src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/buy-crypto-bg.svg') }}"
                            alt="cryto-bg.svg">
                    </div>
                    <p class="OpenSans-600 font-32 c-blublack mt-41 f-18-res">{{ __('Buy Crypto with Fiat') }} </p>
                    <p class="OpenSans-400 font-20 c-blublack f-14-res mt-3">
                        {{ __('Buy On :x, you can buy any crypto with more than 50 fiat currencies using your Visa or MasterCard.', ['x' => settings('name')]) }}
                    </p>
                </div>
                <div class="cryptobox3 flex-basis-full">
                    <p class="OpenSans-600 font-32 c-blublack f-18-res">{{ __('Advanced Data Encryption') }}</p>
                    <p class="OpenSans-400 font-20 p2-font-20 c-blublack f-14-res mt-3">
                        {{ __('Your transaction data is secured via end-to-end encryption, ensuring that only you have access to your personal information.') }}
                    </p>
                    <div class="row pr-lg1">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="parent-dot parent-dot-right">
                                <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/dot-right.svg') }}"
                                    class="img-dot img-fluid" alt="dot-right">
                                <div class="lock">
                                    <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/locks.svg') }}"
                                        class="img-fluid" alt="locks">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="first-section-b">
        <div class="container-fluid first-section-b-px-240p">
            <div class="row mt-140">
                <div class="col-md-12 col-xs-12">
                    <div class="text-center" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                        <span class="OpenSans-600 font-24 c-blue3 text-center headline">{{ __('HOW IT WORKS') }}</span>
                    </div>
                    <p class="OpenSans-600 font-60 text-center c-blublack headline" data-aos="fade-up" data-aos-anchor-placement="top-bottom">{{ __('FEW EASY STEPS TO MAKE') }}</p>
                    <div class="d-flex flex-row justify-content-center" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                        <hr class="new4 text-center">
                    </div>
                </div>
            </div>
            <div class="row mt-44">
                <div class="col-lg-5 col-md-6 col-xs-12 col-sm-12" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                    <div class="accordion ml-28" id="accordionStepper">
                        <div class="card mod-card">
                            <div class="card-header b-unset bg-unset" id="headingOne">
                                <div class="d-flex c-text-parent">
                                    <div class="active-circle-bg circle-bg circle-bg-one active-round text-light text-center d-flex justify-content-center ml-n50 mt-n12">
                                        <span class="d-flex align-items-center multistep-font step-active-color OpenSans-600">1</span>
                                    </div>
                                    <p class="text-left font-28 OpenSans-600 c-text c-blublack mt-n4 ml-26 " type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">{{ __('Create account') }}</p>
                                </div>
                            </div>

                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionStepper">
                                <div class="">
                                    <p class="font-20 OpenSans-400 ml-55 mt-n8 w-378">{{ __('On :x, you can buy any crypto with more than 50 fiat currencies using your Visa or MasterCard.', ['x' => settings('name')]) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card mod-card">
                            <div class="card-header b-unset bg-unset " id="headingTwo">
                                <div class="d-flex c-text-parent">
                                    <div class="circle-bg circle-bg-two active-round text-center d-flex justify-content-center ml-n50 mt-n12">
                                        <span class="d-flex align-items-center multistep-font step-color OpenSans-600">2</span>
                                    </div>
                                    <p class="text-left font-28 OpenSans-600 c-text c-blublack text-left collapsed mt-n4 ml-26" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">{{ __('Set up your amount') }}</p>
                                </div>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionStepper">
                                <div>
                                    <p class="font-20 OpenSans-400 ml-55 mt-n8 w-378">{{ __('On :x, you can buy any crypto with more than 50 fiat currencies using your Visa or MasterCard.', ['x' => settings('name')]) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card mod-card">
                            <div class="card-header b-unset bg-unset" id="headingThree">
                                <div class="d-flex c-text-parent">
                                    <div class="circle-bg circle-bg-three active-round text-center d-flex justify-content-center ml-n50 mt-n12">
                                        <span class="d-flex align-items-center multistep-font step-color OpenSans-600">3</span>
                                    </div>
                                    <p class="text-left font-28 OpenSans-600 c-blublack collapsed mt-n4 ml-26 c-text" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">{{ __('Pawn your crypto') }}</p>
                                </div>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-bs-parent="#accordionStepper">
                                <div>
                                    <p class="font-20 OpenSans-400 ml-55 mt-n8 w-378">{{ __('On :x, you can buy any crypto with more than 50 fiat currencies using your Visa or MasterCard.', ['x' => settings('name')]) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card overflow-unset b-unset">
                            <div class="card-header b-unset bg-unset" id="headingFour">
                                <div class="d-flex c-text-parent">
                                    <div class="circle-bg circle-bg-four text-center active-round d-flex justify-content-center ml-n36-res ml-n50 ml-38n mt-n12">
                                        <span class="d-flex align-items-center multistep-font step-color OpenSans-600">4</span>
                                    </div>
                                    <p class="text-left c-text font-28 OpenSans-600 c-blublack collapsed mt-n4 ml-26" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">{{ __('That is it') }}</p>
                                </div>
                            </div>
                            <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-bs-parent="#accordionStepper">
                                <div>
                                    <p class="font-20 OpenSans-400 ml-55 mt-n8 w-378">{{ __('On :x, you can buy any crypto with more than 50 fiat currencies using your Visa or MasterCard.', ['x' => settings('name')]) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-6 col-xs-12 col-sm-12 d-n-res position-relative">
                    <div class="img-div" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                        <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/lp-cover.svg') }}" alt="dot-logo" class="img-fluid">
                    </div>
                    <div class="right-dot-img">
                        <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/dot-right.svg') }}" alt="dot-logo" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- How It Works Section End-->


    <!-- CTA Banner Section -->
    <div class="second-section signup-today signip-parent">
        <div class="container-fluid">
            <div class="signup-today-p">
                <div class="row">
                    <div class="col-md-8 col-12">
                        <div class="d-flex flex-column" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                            <span class="font-24 OpenSans-700 c-blue-shade f-16-res">{{ __('HASSLE FREE') }}</span>
                            <p class="font-38 OpenSans-700 c-white">{{ __('BUY SELL EXCHANGE') }}</p>
                        </div>
                        <div class="cbtn-bg d-flex justify-content-between align-items-center mt-12 cursor-pointer btn-animate-two"
                            data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                            <a href="{{ url('/register') }}"
                                class="text-light OpenSans-600">{{ __('Create an Account') }}</a>
                            <div class="ml-27p svg-img-parent-two">
                                <div class="svg-img-two">
                                    <svg width="54" height="54" viewBox="0 0 54 54" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="54" height="54" rx="6" fill="" />
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M15.8181 39.1816C15.4164 38.78 15.4164 38.1287 15.8181 37.7271L34.7272 18.818C35.1288 18.4163 35.7801 18.4163 36.1817 18.818C36.5834 19.2196 36.5834 19.8709 36.1817 20.2725L17.2726 39.1816C16.871 39.5833 16.2198 39.5833 15.8181 39.1816Z"
                                            fill="white" />
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M24.2441 19.5454C24.2441 18.9774 24.7046 18.5169 25.2727 18.5169L35.4545 18.5169C36.0225 18.5169 36.483 18.9774 36.483 19.5454L36.483 29.7272C36.483 30.2953 36.0225 30.7557 35.4545 30.7557C34.8864 30.7557 34.426 30.2953 34.426 29.7272L34.426 20.5739L25.2727 20.5739C24.7046 20.5739 24.2441 20.1134 24.2441 19.5454Z"
                                            fill="white" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                        <div class="img-child">
                            <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/man.svg') }}" alt="man" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CTA Banner Section End-->

    <!-- Frequently Asked Questions Section-->
    <div class="third-section pt-120" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
        <div class="container-fluid px3-460p row-heads position-relative">
            <div class="row">
                <div class="col-md-12">
                    <div class="text-center">
                        <span class="OpenSans-600 font-24 c-blue3 text-center" data-aos="fade-up" data-aos-anchor-placement="top-bottom">{{ __('WE GOT YOU COVERED') }}</span>
                    </div>
                    <p class="OpenSans-600 font-60 text-center c-blublack" data-aos="fade-up" data-aos-anchor-placement="top-bottom">{{ __('FREQUENTLY ASKED QUESTIONS') }}</p>

                    <div class="d-flex flex-row justify-content-center" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                        <hr class="new4 text-center">
                    </div>
                    <div class="text-center mt-44">
                        <span class="text-center font-32-p5 OpenSans-600 c-blublack mb-12 p-0 mb-unset">{{ __('About') }}</span>
                    </div>
                </div>
            </div>

            <div class="row" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                <div class="col-md-12 p-0-res">
                    <div id="main">
                        <div class="container">
                            <p class="text-center font-32-p5 poppins5 c-blublack mb-12 p-0 mb-unset"></p>
                            <div class="accordion accordion-flush" id="faq">
                                <div class="card">
                                    <div class="card-header" id="faqhead1">
                                        <a href="#" class="btn btn-header-link collapsed exp-font-22 OpenSans-600 q-color"
                                            data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="false"
                                            aria-controls="faq1">{{ __('What is :x Crypto Exchange?', ['x' => settings('name')]) }}</a>
                                    </div>

                                    <div id="faq1" class="collapse" aria-labelledby="faqhead1" data-bs-parent="#faq">
                                        <div class="card-body OpenSans-400">
                                            {{ __(':x Crypto Exchange is a user-friendly and efficient platform designed for exchanging cryptocurrencies. It provides a straightforward, quick, and secure method for individuals to swap various crypto coins and engage in buying and selling activities. The administrators have the freedom to create multiple channels for purchasing, selling, and swapping digital currencies, ensuring flexibility for users. Moreover, the exchange services are not limited to registered users; even unregistered individuals can access and utilize the platform. Transactions can be conducted using wallets, external addresses, and diverse payment gateways, offering convenience and accessibility. Additionally, exchangers have the capability to track their transactions at any time and from any location, facilitating transparency and control over their crypto activities.', ['x' => settings('name')]) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header" id="faqhead2">
                                        <a href="#" class="btn btn-header-link collapsed  exp-font-22 OpenSans-600 q-color"
                                            data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false"
                                            aria-controls="faq2">{{ __('Can I use :x Crypto Exchange and Buy Sell module without registering?', ['x' => settings('name')]) }}</a>
                                    </div>

                                    <div id="faq2" class="collapse" aria-labelledby="faqhead2" data-bs-parent="#faq">
                                        <div class="card-body OpenSans-400">
                                            {{ __('Yes, the module is available for both registered and unregistered users. Guest users can also access the crypto exchange and buy/sell functionalities.') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header" id="faqhead3">
                                        <a href="#" class="btn btn-header-link collapsed exp-font-22 OpenSans-600 q-color" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="true" aria-controls="faq3">{{ __('What verification methods are required for guest users?', ['x' => settings('name')]) }}</a>
                                    </div>

                                    <div id="faq3" class="collapse" aria-labelledby="faqhead3" data-bs-parent="#faq">
                                        <div class="card-body OpenSans-400">{{ __('Guest users are required to verify their email or phone number for every transaction. An OTP (One-Time Password) will be sent to the provided email or phone, which needs to be entered correctly to proceed.') }}</div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header" id="faqhead4">
                                        <a href="#" class="btn btn-header-link collapsed exp-font-22 OpenSans-600 q-color" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="true" aria-controls="faq4">{{ __('How long does it take for transactions to be processed?') }}</a>
                                    </div>

                                    <div id="faq4" class="collapse" aria-labelledby="faqhead4" data-bs-parent="#faq">
                                        <div class="card-body OpenSans-400">{{ __('The processing time for transactions on :x Crypto Exchange can vary depending on several factors, including the network congestion of the involved cryptocurrencies and the chosen transaction fees. Generally, the platform aims to process transactions as quickly as possible to ensure a seamless user experience. However, it is important to note that blockchain networks inherently have their own confirmation times, which can affect the overall speed of transaction processing. It is recommended to review the specific transaction details and blockchain network information for a more accurate estimate of the processing time.', ['x' => settings('name')]) }}</div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header" id="faqhead5">
                                        <a href="#" class="btn btn-header-link collapsed exp-font-22 OpenSans-600 q-color" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="true" aria-controls="faq5">{{ __('What are the available transaction types?') }}</a>
                                    </div>

                                    <div id="faq5" class="collapse" aria-labelledby="faqhead5" data-bs-parent="#faq">
                                        <div class="card-body OpenSans-400">{{ __('There are three transaction types: Crypto Swap, Crypto Buy, and Crypto Sell. Crypto Exchange allows you to swap one cryptocurrency for another, while Crypto Buy enables the purchase of cryptocurrency using fiat currency. Crypto Sell allows you to sell your cryptocurrency for fiat currency.') }}</div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header" id="faqhead6">
                                        <a href="#" class="btn btn-header-link collapsed exp-font-22 OpenSans-600 q-color" data-bs-toggle="collapse" data-bs-target="#faq6" aria-expanded="true" aria-controls="faq6">{{ __('How can I access transaction details and print transaction information?') }}</a>
                                    </div>

                                    <div id="faq6" class="collapse" aria-labelledby="faqhead6" data-bs-parent="#faq">
                                        <div class="card-body OpenSans-400">{{ __('Users can view their transaction details and print transaction information from their user dashboard. They can access this information after completing a transaction successfully.') }}</div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header" id="faqhead7">
                                        <a href="#" class="btn btn-header-link collapsed exp-font-22 OpenSans-600 q-color" data-bs-toggle="collapse" data-bs-target="#faq7" aria-expanded="true" aria-controls="faq7">{{ __('Can the admin set fees limits and exchange rates for different currency pairs?') }}</a>
                                    </div>

                                    <div id="faq7" class="collapse" aria-labelledby="faqhead7" data-bs-parent="#faq">
                                        <div class="card-body OpenSans-400">{{ __('Yes, the admin has control over exchange directions and can set limits, fees, and exchange rates for different currency pairs. This allows for customization and flexibility in managing the crypto exchange platform.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dot-left-side">
                <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/half-dot-left.svg') }}" alt="dot-logo" class="img-fluid">
            </div>
            <div class="dot-right-side">
                <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/half-right-dot.svg') }}" alt="dot-logo" class="img-fluid">
            </div>
        </div>

    </div>
    <!-- Frequently Asked Questions Section End-->

    <!--Market Trends Section-->
    <div>
        <div class="container-fluid px-240p pt-140 pb-140">
            <div class="img-row row mt-327">
                <div class="col-md-6 col-xs-12">
                    <p data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                        <span class="font-32-p5 OpenSans-700 hasle-text">{{ __('Hassle free') }} <br></span>
                        <span class="font-48 OpenSans-700 fast-sectext color-E8">{{ __('FAST & SECURED ') }}<br></span>
                        <span class="font-64-crypto OpenSans-600 last-crypto-text color-FF">{{ __('CRYPTO') }}</span>
                        <span class="font-64-crypto poppins7 color-E8">{{ __('EXCHANGE') }}</span>
                        <span class="hasle-text font-64-crypto color-FF">.</span>
                    </p>
                    <p class="font-28 c-blublack OpenSans-600 mt-23" data-aos="fade-up" data-aos-anchor-placement="top-bottom">{{ __('Sign up now to build your own portfolio for free!') }}</p>
                    <div class="button-widths d-flex justify-content-between align-items-center mt-32 cursor-pointer btn-animate text-light OpenSans-600" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                        <a href="{{ url('/register') }}" class="OpenSans-600">{{ __('Create an Account') }}</a>
                        <div class="ml-27p svg-img-parent">
                            <div class="svg-img">
                                <svg width="54" height="54" viewBox="0 0 54 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="54" height="54" rx="6" fill="none"></rect>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.8181 39.1818C15.4164 38.7801 15.4164 38.1289 15.8181 37.7273L34.7272 18.8182C35.1288 18.4165 35.7801 18.4165 36.1817 18.8182C36.5834 19.2198 36.5834 19.8711 36.1817 20.2727L17.2726 39.1818C16.871 39.5835 16.2198 39.5835 15.8181 39.1818Z" fill="#403E5B"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M24.2441 19.5454C24.2441 18.9774 24.7046 18.5169 25.2727 18.5169L35.4545 18.5169C36.0225 18.5169 36.483 18.9774 36.483 19.5454L36.483 29.7273C36.483 30.2953 36.0225 30.7558 35.4545 30.7558C34.8864 30.7558 34.426 30.2953 34.426 29.7273L34.426 20.574L25.2727 20.574C24.7046 20.574 24.2441 20.1135 24.2441 19.5454Z" fill="#403E5B"></path>
                                </svg>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12 img-top-gap" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                    <img src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/cryptoimg.png') }}" alt="img" class="img-fluid mt-n96">
                </div>
            </div>
        </div>
    </div>
    <!--Market Trends Section-->

    <!-- Download section -->
    <div class="pt-86 dark-app pb-144 position-relative">
        <img class="app-dot" src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/Dotapp.png') }}" alt="">
        <div class="px-240 position-relative">
            <div class="bg-app">
                <div class="row">
                    <div class="col-md-6 order-last order-md-first pay-img">
                        <img class="ml-171 mt-81 desktop-mobile-view" src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/app-img.png') }}" alt="">
                        <img class="ml-171 mt-81 app-mobile-view" src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/app-mobile-view.png') }}" alt="">
                    </div>
                    <div class="col-md-4 order-first order-md-last">
                        <div class="mt-148">
                            <p class="color-FE OpenSans-600 f-18 leading-24 mb-0 text-center">{{ __('DOWNLOAD THE APP') }}</p>
                            <p class="color-05B OpenSans-700 f-34 mb-23 mt-7 text-center app-content" data-content="REASONS">{{ __('Try it on mobile today') }}</p>
                            <p class="small-border mb-0 bgd-blue m-auto"></p>
                        </div>

                        <div class="d-flex gap-2 mt-56 app-sec">
                            @foreach (getAppStoreLinkFrontEnd() as $app)
                                @if (!empty($app->logo) && file_exists('public/uploads/app-store-logos/thumb/' . $app->logo))
                                    <a href="{{ $app->link }}" target="_blank">
                                        <img class="cursor-pointer {{ $app->company == 'Apple' ? 'ml-3 ml-r11' : '' }} app-image" src="{{ url('public/uploads/app-store-logos/thumb/' . $app->logo) }}"  alt="{{ $app->company }}">
                                    </a>
                                @else
                                    <a href="#">
                                        <img src='{{ url('public/uploads/app-store-logos/default-logo.jpg') }}' class="img-responsive" width="120" height="90">
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <img class="app-dot-right" src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/images/app-dot-right.png') }}" alt="">
        </div>
    </div>

    <!-- Download section End-->
@endsection

@section('js')
    <script src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/js/main.min.js') }}"></script>
    <script src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/js/scrolling.min.js') }}"></script>
    <script src="{{ asset('Modules/CryptoExchange/Resources/assets/landing/js/nav-scroll.min.js') }}"></script>
    <script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js') }}" type="text/javascript"></script>
    @include('common.restrict_number_to_pref_decimal')
    @include('common.restrict_character_decimal_point')


    <script type="text/javascript">
        'use strict';
        var requiedText = "{{ __('This field is required.') }}";
        var numberText = "{{ __('Please enter a valid number.') }}";
        var submitBtnText = "{{ __('Processing...') }}";
        var exchangeText = "{{ __('Swap') }}";
        var buyText = "{{ __('Buy') }}";
        var sellText = "{{ __('Sell') }}";
        var directionNotAvaillable = "{{ __('Direction not available.') }}";
        var decimalPreferrence = "{{ preference('decimal_format_amount_crypto', 8) }}";
        var noResult = "{{ __('No Result') }}";
        var directionListUrl = "{{ route('guest.crypto_exchange.direction_list') }}";
        var directionAmountUrl = "{{ route('guest.crypto_exchange.direction_amount') }}";
        var directionTypeUrl = "{{ route('guest.crypto_exchange.direction_type') }}";
        var confirmationUrl = "{{ route('guest.crypto_exchange.verification') }}";
        var currencyLogoUrl = "{{ url(getDirectory('currency')) }}";
    </script>
    <script src="{{ asset('Modules/CryptoExchange/Resources/assets/js/crypto_front.min.js') }}"></script>
@endsection
