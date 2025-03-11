@isset($transactions)
    @forelse ($transactions as $item)
        <div class="dashboard-list-item-wrapper">
            <div class="dashboard-list-item sent">
                <div class="dashboard-list-left">
                    <div class="dashboard-list-user-wrapper">
                        <div class="dashboard-list-user-icon">
                            @if ($item->attribute == payment_gateway_const()::SEND)
                            <i class="las la-arrow-up"></i>
                            @else
                            <i class="las la-arrow-down"></i>
                            @endif
                        </div>
                        <div class="dashboard-list-user-content">

                            @if ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <h4 class="title">{{ __("Withdraw Money") }} <span class="text--warning">{{ @$item->currency->name }}</span></h4>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <h4 class="title">{{ __("Balance Update From Admin") }}{{ __(" (".$item->creator_wallet->currency->code.")") }} </h4>
                            @elseif ($item->type == payment_gateway_const()::TYPEMAKEPAYMENT)
                                @if ($item->attribute == payment_gateway_const()::SEND)
                                    <h4 class="title">{{ __("Make Payment to") }} {{ __("@" . @$item->details->receiver_username." (".@$item->details->receiver_email.")") }} </h4>
                                @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                                    <h4 class="title">{{ __("Make Payment From") }} {{ __("@" .@$item->details->sender_username." (".@$item->details->sender_email.")") }} </h4>
                                @endif
                            @elseif ($item->type == payment_gateway_const()::MERCHANTPAYMENT)
                                @if ($item->attribute == payment_gateway_const()::RECEIVED)
                                    <h4 class="title">{{ __("Payment Money from") }}{{ __("@" . @$item->details->sender_username." (".@$item->details->pay_type.")") }} </h4>
                                    <span class="d-block py-1 text-warning font-weight-bold">{{ __(@$item->details->env_type) }}</span>
                                @endif
                            @elseif ($item->type == payment_gateway_const()::TYPEPAYLINK)
                                <h4 class="title">{{ __('Add Balance via') }} <span class="text--warning">({{ $item->type }})</span></h4>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <h4 class="title">{{ __("Exchange Money") }} <span class="text--warning">{{ $item->details->charges->from_wallet_country }} {{ __("To") }} {{ $item->details->charges->to_wallet_country }}</span></h4>
                            @endif
                            <span class="{{ $item->stringStatus->class }}">{{__($item->stringStatus->value) }} </span>
                        </div>
                    </div>
                </div>
                <div class="dashboard-list-right">
                    @if($item->type == payment_gateway_const()::TYPEMONEYOUT)
                        <h6 class="exchange-money text--warning fw-bold">{{ get_amount($item->request_amount,withdrawCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)) }}</h6>
                        <h4 class="main-money ">{{ get_amount($item->details->charges->payable??$item->request_amount,withdrawCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)) }}</h4>
                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                        <h4 class="main-money text--base">{{ get_transaction_numeric_attribute($item->attribute) }}{{ get_amount($item->request_amount,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)) }}</h4>
                        <h6 class="exchange-money">{{ get_amount($item->available_balance,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)) }}</h6>
                    @elseif ($item->type == payment_gateway_const()::TYPEMAKEPAYMENT)
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->details->charges->receiver_amount,$item->details->charges->receiver_currency,get_wallet_precision($item->creator_wallet->currency)) }}</h6>
                    @elseif ($item->type == payment_gateway_const()::MERCHANTPAYMENT)
                        @if ($item->attribute == payment_gateway_const()::RECEIVED)
                        <h4 class="main-money fw-bold">{{ get_amount($item->details->charges->receiver_amount,$item->details->charges->receiver_currency,2) }}</h4>
                        @endif
                    @elseif ($item->type == payment_gateway_const()::TYPEPAYLINK)
                    <h4 class="main-money text--base">{{ get_amount($item->request_amount, @$item->details->charge_calculation->receiver_currency_code,get_wallet_precision($item->creator_wallet->currency)) }}</h4>
                    <h6 class="exchange-money text--warning">{{ get_amount($item->details->charge_calculation->conversion_payable??$item->details->charge_calculation->receiver_amount, @$item->details->charge_calculation->receiver_currency_code,get_wallet_precision($item->creator_wallet->currency)) }}</h6>
                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                    <h4 class="main-money text--base">{{ get_amount($item->request_amount,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)) }}</h4>
                    <h6 class="exchange-money">{{ get_amount($item->payable,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)) }}</h6>
                    @endif
                </div>
            </div>
            <div class="preview-list-wrapper">
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-text-width"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("type") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--success">{{ @$item->type == "MONEY-OUT" ? "WITHDRAW" : @$item->type }}</span>
                    </div>
                </div>

                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="lab la-tumblr"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("web_trx_id") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ $item->trx_id }}</span>
                    </div>
                </div>

                @if ($item->type != payment_gateway_const()::TYPEMAKEPAYMENT )
                @if ($item->type != payment_gateway_const()::MERCHANTPAYMENT )

                <div class="preview-list-item">

                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-exchange-alt"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Exchange Rate") }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="preview-list-right">
                        @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->currency->rate??1,$item->currency->currency_code??get_default_currency_code()) }}</span>
                        @elseif ($item->type == payment_gateway_const()::SENDREMITTANCE)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->details->to_country->rate,$item->details->to_country->code) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                        <span>1 {{ withdrawCurrency($item)['wallet_currency'] }} = {{ isCrypto($item->details->charges->exchange_rate??$item->currency->rate??1,$item->currency->currency_code??get_default_currency_code(),$item->currency->gateway->crypto) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                        <span>1 {{ $item->creator_wallet->currency->code }} = {{ get_amount($item->details->charges->exchange_rate,$item->details->charges->exchange_currency,$item->details->charges->r_precision_digit??2) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->creator_wallet->currency->rate,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEPAYLINK)
                        <span>1 {{ @$item->details->charge_calculation->base_currency_code }} = {{ get_amount($item->details->charge_calculation->exchange_rate??$item->details->charge_calculation->r_exchange_rate, @$item->details->charge_calculation->receiver_currency_code,get_wallet_precision($item->creator_wallet->currency)) }}</span>
                        @endif
                    </div>
                </div>
                @endif
                @endif

                @if ($item->type == payment_gateway_const()::TYPEMAKEPAYMENT)

                @if ($item->attribute == payment_gateway_const()::SEND)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-exchange-alt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Exchange Rate") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span>{{ get_amount(1,$item->details->charges->sender_currency??get_default_currency_code())." = ".get_amount($item->details->charges->exchange_rate??get_default_currency_rate(),$item->details->charges->receiver_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)) }}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-battery-half"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("fees And Charges") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span>{{ get_amount($item->charge->total_charge,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)) }}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="lab la-get-pocket"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Recipient Received") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span>{{ get_amount($item->details->charges->receiver_amount??$item->details->recipient_amount,$item->details->charges->receiver_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)) }}</span>
                        </div>
                    </div>

                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-balance-scale"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Current Balance") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span class="text--base">{{ get_amount($item->available_balance,$item->details->charges->sender_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)) }}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-receipt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("remark") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span class="text--success">{{ @$item->remark}}</span>
                        </div>
                    </div>
                @else
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-balance-scale"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Current Balance") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ get_amount($item->available_balance,$item->details->charges->receiver_currency??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)) }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-receipt"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("remark") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--success">{{ @$item->remark}}</span>
                    </div>
                </div>
                @endif
                @else

                    @if ($item->type != payment_gateway_const()::MERCHANTPAYMENT )
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-battery-half"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("fees And Charges") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <span>{{ get_amount($item->charge->total_charge??0,@$item->currency->currency_code??get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <span>{{ get_amount($item->charge->total_charge??0,withdrawCurrency($item)['wallet_currency'],get_wallet_precision($item->creator_wallet->currency)) }}</span>
                            @elseif ($item->type == payment_gateway_const()::SENDREMITTANCE)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <span>{{ get_amount($item->charge->total_charge,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span>{{ get_amount($item->charge->total_charge,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEPAYLINK)
                            <span class="text--danger">{{ get_amount($item->details->charge_calculation->conversion_charge ?? $item->details->charge_calculation->r_total_charge, $item->details->charge_calculation->receiver_currency_code,get_wallet_precision($item->creator_wallet->currency)) }}</span>
                            @endif
                        </div>
                    </div>
                    @endif


                    @if ($item->type != payment_gateway_const()::MERCHANTPAYMENT)
                    @if ($item->type != payment_gateway_const()::TYPEPAYLINK)
                    @if ($item->type != payment_gateway_const()::TYPEMONEYEXCHANGE)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="lab la-get-pocket"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                        <span>{{ __("Will Get") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                        <span>{{ __("Payable Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                        <span>{{ __("Payable Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                        <span>{{ __("Total Payable") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                        @if($item->attribute ==  payment_gateway_const()::SEND)
                                            <span>{{ __("Total Deducted") }}</span>
                                            @else
                                            <span>{{ __("total Received") }}</span>
                                        @endif
                                    @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                        <span>{{ __("card Amount") }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <span class="text-danger">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <span>{{ isCrypto($item->payable,withdrawCurrency($item)['gateway_currency'],$item->currency->gateway->crypto) }}</span>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <span class="fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span class="fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                <span class="fw-bold"> {{ get_amount(@$item->details->card_info->amount,get_default_currency_code()) }}</span>

                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <span>{{ get_amount($item->payable,$item->creator_wallet->currency->code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span>{{ get_amount($item->payable,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)) }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                    @endif

                    @if ($item->type != payment_gateway_const()::MERCHANTPAYMENT)
                    @if ($item->type != payment_gateway_const()::TYPEPAYLINK)
                    @if ($item->type != payment_gateway_const()::TYPEMONEYEXCHANGE)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-receipt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                        <span>{{ __("Total Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                        <span>{{ __("Card Number") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                        <span>{{ __("Exchange Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                        <span>{{ __("remark") }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="preview-list-right">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <span class="text--warning">{{ get_amount($item->payable,@$item->currency->currency_code??get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <span class="text--success">{{ get_amount($item->available_balance,withdrawCurrency($item)['wallet_currency']??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)) }}</span>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <span class="text--success">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span class="text--success">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                @php
                                    $card_pan = str_split(@$item->details->card_info->card_pan, 4);
                                @endphp
                                @foreach($card_pan as $key => $value)
                                <span class="text--base fw-bold">{{ $value }}</span>
                                @endforeach
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <span class="text--warning">{{ get_amount($item->details->exchange_amount,$item->details->exchange_currency,get_wallet_precision($item->creator_wallet->currency)) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span class="text--warning">{{ $item->remark }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                    @endif
                @endif
                {{-- Exchange money log --}}
                @if ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-smoking"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Exchangeable Balance") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="fw-bold">{{ get_amount($item->details->charges->exchange_amount,$item->details->charges->exchange_currency,$item->details->charges->r_precision_digit??2) }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-balance-scale"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Current Balance") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="fw-bold">{{ get_amount($item->available_balance,$item->creator_wallet->currency->code,get_wallet_precision($item->creator_wallet->currency)) }}</span>
                    </div>
                </div>
            @endif
                 {{-- make pay to merchant by payemt gateway --}}
                 @if ($item->type == payment_gateway_const()::MERCHANTPAYMENT)

                 <div class="preview-list-item">
                     <div class="preview-list-left">
                         <div class="preview-list-user-wrapper">
                             <div class="preview-list-user-icon">
                                 <i class="las la-balance-scale"></i>
                             </div>
                             <div class="preview-list-user-content">
                                 <span>{{ __("Bussines Name") }}</span>
                             </div>
                         </div>
                     </div>
                     <div class="preview-list-right">
                         <span>{{ $item->details->payment_to }}</span>
                     </div>
                 </div>
                 <div class="preview-list-item">
                     <div class="preview-list-left">
                         <div class="preview-list-user-wrapper">
                             <div class="preview-list-user-icon">
                                 <i class="las la-user"></i>
                             </div>
                             <div class="preview-list-user-content">
                                 <span>{{ __("sender") }}</span>
                             </div>
                         </div>
                     </div>
                     <div class="preview-list-right">
                         <span>{{ $item->details->sender_username }}</span>
                     </div>
                 </div>
                 <div class="preview-list-item">
                     <div class="preview-list-left">
                         <div class="preview-list-user-wrapper">
                             <div class="preview-list-user-icon">
                                 <i class="las la-receipt"></i>
                             </div>
                             <div class="preview-list-user-content">
                                 <span>{{ __("payment Amount") }}</span>
                             </div>
                         </div>
                     </div>
                     <div class="preview-list-right">
                         <span>{{ get_amount($item->details->charges->receiver_amount,$item->details->charges->receiver_currency,2) }}</span>
                     </div>
                 </div>
            @endif
            @if ($item->type == payment_gateway_const()::TYPEPAYLINK)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="lab la-get-pocket"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __('availabe Blance') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--success">{{ get_amount($item->available_balance, $item->details->charge_calculation->receiver_currency_code,2) }}</span>
                    </div>
                </div>
                @if(isset($item->details->payment_type) && $item->details->payment_type == payment_gateway_const()::TYPE_CARD_PAYMENT)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-receipt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Payment Type') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ ucwords(str_replace('_',' ',$item->details->payment_type) )}}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-envelope"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Sender Email') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ $item->details->email }}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-user"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Card Holder Name') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ $item->details->card_name }}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-credit-card"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Sender Card Number') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">**** **** **** {{ @$item->details->last4_card }}</span>
                        </div>
                    </div>
                @endif
                @if(isset($item->details->payment_type) && $item->details->payment_type == payment_gateway_const()::TYPE_WALLET_SYSTEM)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-receipt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Payment Type') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ ucwords(str_replace('_',' ',$item->details->payment_type) )}}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-envelope"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Sender Email') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ $item->details->sender->email }}</span>
                        </div>
                    </div>
                @endif
                @if(isset($item->details->payment_type) && $item->details->payment_type == payment_gateway_const()::TYPE_GATEWAY_PAYMENT)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-receipt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Payment Type') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ ucwords(str_replace('_',' ',$item->details->payment_type) )}}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-hand-holding-usd"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Payment Gateway') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{$item->details->currency->name}}</span>
                        </div>
                    </div>
                @endif
            @endif
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-clock"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Time & Date") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ $item->created_at->format('d-m-y h:i:s A') }}</span>
                    </div>
                </div>

                @if( $item->status == 4 || $item->status == 6 &&  $item->reject_reason != null)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-smoking"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Rejection Reason") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text-danger">{{ __($item->reject_reason) }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-primary text-center">
            {{ __("No data found!") }}
        </div>
    @endforelse

    {{ get_paginate($transactions) }}


@endisset
