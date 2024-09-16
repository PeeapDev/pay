<div class="col-lg-6">
    <div class="row">
        <div class="col-md-12 p-1-res">
            <div class="pt-80">
                @include('user.common.alert')
                @if (transactionTypeCheck())
                    <nav class="nav-dimension">
                        <div class="navmp nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link c-off-white padding-a OpenSans-700 crypto crypto_swap {{ ($exchange_type == 'crypto_swap') ? 'active' : '' }}"
                                id="nav-home-tab" data-bs-toggle="tab" href="#nav-home" role="tab"
                                aria-controls="nav-home" aria-selected="true" data-type="crypto_swap">{{ __('Crypto Swap') }} </a>
                            <a class="nav-item nav-link c-off-white padding-a OpenSans-700 crypto crypto_buy {{ ($exchange_type == 'crypto_buy' ||  $exchange_type == 'crypto_sell') ? 'active' : ''}} "
                                id="nav-profile-tab" data-bs-toggle="tab" href="#nav-profile"
                                role="tab" aria-controls="nav-profile"
                                aria-selected="false" data-type="crypto_buy">{{ __('Crypto Buy / Sell') }}</a>
                        </div>
                    </nav>
                @else
                    <nav class="nav-dimension">
                        <div class="navmp nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link c-off-white padding-a OpenSans-700"
                                id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab"
                                aria-controls="nav-home"
                                aria-selected="true">{{ transactionTypeCheck('crypto_buy_sell') ? __('Crypto Buy / Sell') : __(' Crypto Swap') }}
                            </a>
                        </div>
                    </nav>
                @endif

                <form action="{{ route('guest.crypto_exchange.verification') }}" method="POST"
                    accept-charset='UTF-8' id="crypto-send-form">
                    @csrf
                    <input type="hidden" name="from_type" id="from_type" value="{{ $exchange_type }}">

                    <div class="tab-content tab-dimension mt-n3" id="nav-tabContent">
                        <div class="box-shadow tabpan-rad tab-pane  show active bg-light bg-5B"
                            id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">

                            <!-- You Send Section -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="boxdiv yousend-top bg-light mx-28 mt-35 box-bg-one">
                                        <div class="d-flex justify-content-between">

                                            <!-- You Send Amount -->
                                            <div class="mt-2 mt-n8-res w-100">
                                                <span
                                                    class="font-14 OpenSans-400 c-blue2 pl-20 color-E8">{{ __('You Send') }}</span>
                                                <br>
                                                <input type="text"
                                                    class="form-control custom-height w-100 input-customization s-font-24 c-blue2 mulish4 pl-20 mt-n3 color-E8"
                                                    autocomplete="off" name="send_amount"
                                                    id="send_amount" value="{{ $min_amount }}"
                                                    onkeypress="return isNumberOrDecimalPointKey(this, event);"
                                                    oninput="restrictNumberToPrefdecimalOnSendInput(this)">
                                            </div>

                                            <!-- Button trigger modal -->
                                                <div id="selected-from-currency" class="selected d-flex gap-20" data-bs-toggle="modal" data-bs-target=".bd-example-modal-sm">
                                                    @if($direction)
                                                        <button type="button" class="btn btn-primary md:ms-2">
                                                            <img id="seleceted-from-image" class="img-fluid rounded-icon set-img" src="{{ image(optional($direction->fromCurrency)->logo, 'currency') }}">
                                                            <span class="set-coinname px-2" id="from-selected">{{ optional($direction->fromCurrency)->code }}</span>
                                                        </button>
                                                    @endif
                                                </div>

                                                <input type="hidden" name="from_currency" id="fromCurrencyId" value="{{  isset($direction) ? optional($direction->fromCurrency)->id : '' }}">

                                        </div>
                                    </div>
                                </div>
                            </div>




                            <!-- Limit Fees Estimated Text -->
                            <div class="row">
                                <div class="col-md-10 col-10 parent-2">
                                    <div class="ul-one ul-ml-51 dot display-hide">
                                    </div>
                                    <div
                                        class="d-flex align-items-center dot dot-message display-hide">
                                        <div class="ul-two ul-ml-47 dot display-hide">
                                        </div>

                                        <p
                                            class="mb-unset OpenSans-400 font-13 c-blue2 pl-16 send_amount_error">
                                        </p>
                                    </div>

                                    <div class="ul-three ul-ml-51">
                                    </div>
                                    <div class="d-flex align-items-center h-9p">
                                        <div class="ul-four ul-ml-47">
                                        </div>
                                        <p class="mb-unset OpenSans-400 font-13 c-blue2 pl-16">
                                            {{ __('Fees') }} : <span class="exchange_fee"></span>
                                        </p>
                                    </div>
                                    <div class="ul-five ul-ml-51">
                                    </div>
                                    <div class="d-flex align-items-center h-9p">
                                        <div class="ul-six ul-ml-47">
                                        </div>
                                        <p class="mb-unset OpenSans-400 font-13 c-blue2 pl-16">
                                            {{ __('Estimated rate') }} : <span class="rate"></span>
                                        </p>
                                    </div>

                                    <div class="ul-seven ul-ml-51">

                                    </div>
                                </div>
                                <div class="col-md-2 col-2 d-flex align-items-center">
                                    <div
                                        class="buy-sell-btn display-flex justify-content-center align-items-center switch-box display-hide cur-pointer">
                                        <svg width="22" height="23"
                                            viewBox="0 0 22 23" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M12.75 0C12.3358 0 12 0.373096 12 0.833333C12 1.29357 12.3358 1.66667 12.75 1.66667H15.75C15.9489 1.66667 16.1397 1.75446 16.2803 1.91074C16.421 2.06702 16.5 2.27899 16.5 2.5V17.4164L13.4226 14.2511C13.0972 13.9163 12.5695 13.9163 12.2441 14.2511C11.9186 14.5858 11.9186 15.1285 12.2441 15.4632L16.4107 19.7489C16.6068 19.9506 16.8761 20.0308 17.1305 19.9895C17.1694 19.9964 17.2093 20 17.25 20C17.6095 20 17.9098 19.719 17.983 19.344L21.7559 15.4632C22.0814 15.1285 22.0814 14.5858 21.7559 14.2511C21.4305 13.9163 20.9028 13.9163 20.5774 14.2511L18 16.9021V2.5C18 1.83696 17.7629 1.20107 17.341 0.732233C16.919 0.263392 16.3467 0 15.75 0H12.75ZM4.62801 4.01042C4.396 3.97235 4.1488 4.03861 3.96967 4.20921L0.21967 7.78064C-0.0732233 8.05958 -0.0732233 8.51184 0.21967 8.79079C0.512563 9.06974 0.987437 9.06974 1.28033 8.79079L4 6.20063V20.625C4 21.2549 4.23705 21.859 4.65901 22.3044C5.08097 22.7498 5.65326 23 6.25 23H9.25C9.66421 23 10 22.6456 10 22.2083C10 21.7711 9.66421 21.4167 9.25 21.4167H6.25C6.05109 21.4167 5.86032 21.3333 5.71967 21.1848C5.57902 21.0363 5.5 20.835 5.5 20.625V6.67682L7.71967 8.79079C8.01256 9.06974 8.48744 9.06974 8.78033 8.79079C9.07322 8.51184 9.07322 8.05958 8.78033 7.78064L5.487 4.64413C5.42152 4.27741 5.11645 4 4.75 4C4.70846 4 4.66771 4.00356 4.62801 4.01042Z"
                                                fill="currentColor" fill-opacity="0.6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- You Get Section -->
                            <div class="row pb-36">
                                <div class="col-md-12">
                                    <div class="boxdiv bg-light mx-28 box-bg-two">
                                        <div class="d-flex justify-content-between">

                                            <!-- You Get Amount -->
                                            <div class="mt-2 mt-n8-res w-100">
                                                <span
                                                    class="font-14 poppins5 c-blue2 pl-20">{{ __('You Get') }}</span>
                                                <br>
                                                <input type="text"
                                                    class="form-control custom-height w-100 input-customization s-font-24 c-blue2 mulish4 pl-20 mt-n3"
                                                    autocomplete="off" name="get_amount"
                                                    id="get_amount" value="0.1"
                                                    onkeypress="return isNumberOrDecimalPointKey(this, event);"
                                                    oninput="restrictNumberToPrefdecimalOnReceiveInput(this)">
                                            </div>



                                            <div id="selected-to-currency" class="selected d-flex gap-20" data-bs-toggle="modal" data-bs-target=".bd-example-modal-sm-to">
                                                @isset($direction)
                                                <button type="button" class="btn btn-primary md:ms-2"  >
                                                    <img id="seleceted-to-image" class="img-fluid rounded-icon set-imgTo" src="{{ image(optional($direction->toCurrency)->logo, 'currency') }}">
                                                    <span id="to-selected" class="set-coinnameTo px-2">{{ optional($direction->toCurrency)->code }} </span>
                                                </button>
                                                @endisset
                                            </div>

                                            <input type="hidden" id="toCurrencyId" name="to_currency" value="{{  isset($direction) ?  $direction->to_currency_id : '' }}">

                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Exchange Button -->
                            <div class="row pb-36">
                                <div class="col-md-12">
                                    <div class="mx-28 exchangebutton-2 text-center d-grid">
                                        <button type="submit" class="btn-lg btn-block btn cur-pointer btn-bg-color" id="crypto_buy_sell_button">
                                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                                <span class="visually-hidden"></span>
                                            </div>
                                            <span class="exc-font-22 OpenSans-600 c-white" id="rp_text">{{ __('Swap') }}</span>
                                            <span id="rightAngleSvgIcon">{!! svgIcons('right_angle') !!}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<div class="modal fade-in animated bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog crypto-modal-width modal-sm modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
            <h2 class="modal-title f-18 OpenSans-400" id="exampleModalLongTitle">{{ __('Select Crypto')}}</h2>
            <button type="button" class="close close-icon" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body modal-body-parent">
        <div class="p-3">
        <div class="crypto-search">
            <input type="text" class="form-control" id="fromInput" placeholder="{{ __('Search') }}">
            <span class="fa fa-search"></span>
        </div>
        </div>
            <div class="modal-body-child thin-scrollbar">
            <table class="table table-hover" id="from-currency-table">
                <tbody id="from-currency-tr">
                     @foreach ($fromCurrencies as $exchangeDirection)

                         <tr>
                            <td class="text-left cursor-pointer from-currency" id="{{ optional($exchangeDirection->fromCurrency)->id }}">
                                <div class="d-flex px-3 align-items-center">
                                    <img id="from-image-{{ optional($exchangeDirection->fromCurrency)->id }}" class="img-fluid currency-img" src="{{ image(optional($exchangeDirection->fromCurrency)->logo, 'currency') }}"
                                        alt="{{ optional($exchangeDirection->fromCurrency)->code }}"
                                    >

                                    <div class="px-3 coin-list">
                                        <p class="coin-tag" id="from-code-{{ optional($exchangeDirection->fromCurrency)->id }}">
                                         {{ optional($exchangeDirection->fromCurrency)->code }}
                                        </p>
                                        <span class="coin-name">{{ optional($exchangeDirection->fromCurrency)->name }}</span>
                                    </div>

                                </div>

                            </td>
                        </tr>

                     @endforeach

                </tbody>
            </table>
            </div>

        </div>
      </div>
    </div>
</div>

<div class="modal fade-in animated bd-example-modal-sm-to" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog crypto-modal-width modal-sm modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
            <h2 class="modal-title f-18 OpenSans-400" id="exampleModalLongTitle">{{ __('Select Crypto')}}</h2>
            <button type="button" class="close close-icon" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body modal-body-parent">
        <div class="p-3">
        <div class="crypto-search">
            <input type="text" class="form-control" id="toInput" placeholder="{{ __('Search') }}">
            <span class="fa fa-search"></span>
        </div>
        </div>
            <div class="modal-body-child thin-scrollbar">
            <table class="table table-hover" id="to-currency-table">
                <tbody id="to-currency-tr">
                    @isset($toCurrencies)
                        @foreach ($toCurrencies as $to_currency)

                            <tr>
                            <td class="text-left cursor-pointer to-currency" id="{{ $to_currency->id }}">
                                <div class="d-flex px-3 align-items-center">
                                    <img id="to-image-{{ $to_currency->id }}" class="img-fluid currency-img" src="{{ image($to_currency->logo, 'currency') }}" alt="{{ $to_currency->code }}">

                                    <div class="px-3 coin-list">
                                        <p class="coin-tag" id="to-code-{{ $to_currency->id }}">
                                            {{ $to_currency->code }}
                                        </p>
                                        <span class="coin-name">{{ $to_currency->name }}</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endisset

                </tbody>
            </table>
            </div>

        </div>
      </div>
    </div>
</div>


