<div class="col-xl-12">
    <div class="row mb-30-none">
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __(@$page_title) }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form row" action="{{ route('user.sudo.virtual.card.create') }}" method="POST">
                            @csrf
                            <div class="col-xl-12 col-lg-12 form-group text-center">
                                <div class="exchange-area">
                                    <code class="d-block text-center">
                                        <span>{{ __("Exchange Rate") }} <span class="exchange-rate">--</span></span>
                                    </code>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("date Of Birth") }} <span class="text--base">*</span></label>
                                        <div class="input-group">
                                            <input type="date" class="form--control" required placeholder="{{__("enter date Of Birth")}}" name="date_of_birth" value="{{ old("date_of_birth") }}">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("Identity Type") }} <span class="text--base">*</span></label>
                                        <select class="form--control" name="identity_type" required>
                                            <option  disabled selected value="null" >{{ __('Choose One') }}</option>
                                            <option  value="BVN">{{ __("Bank Verification Number") }}</option>
                                            <option  value="NIN">{{ __("National Identification Number") }}</option>
                                            <option  value="TIN">{{ __("Taxpayer identification numbers") }}</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("Identity Number") }} <span class="text--base">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form--control" required placeholder="{{__("Enter Identity Number")}}" name="identity_number" value="{{ old("identity_number") }}">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("Phone") }}<span class="text--base">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form--control prepend" required placeholder="{{ __('enter Phone Number') }}" name="phone_number" value="{{ old('phone_number',auth()->user()->full_mobile) }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-12 col-lg-12 form-group">
                                <label>{{ __("Amount") }} <span class="text--base">*</span></label>
                                <div class="input-group">
                                        <input type="text" class="form--control number-input" required placeholder="{{__('enter Amount')}}" name="card_amount" value="{{ old("card_amount") }}">
                                        <select class="form--control nice-select" name="currency">
                                            @foreach ($supported_currency as $item)
                                            <option value="{{ $item->code }}"
                                                data-code="{{ $item->code }}"
                                                data-symbol="{{ $item->symbol }}"
                                                data-rate="{{ $item->rate }}"
                                                data-wallet="{{ $item->id }}"
                                                data-currency-id="{{ $item->id }}"
                                                data-name="{{ $item->country }}">{{ $item->code }}</option>
                                            @endforeach
                                        </select>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group">
                                <label>{{ __("From Wallet") }} <span class="text--base">*</span></label>
                                <select class="form--control select2-auto-tokenize" name="from_currency">
                                    @foreach ($from_wallets as $item)
                                    <option value="{{ $item->currency->code }}"
                                        data-code="{{ $item->currency->code }}"
                                        data-symbol="{{ $item->currency->symbol }}"
                                        data-rate="{{ $item->currency->rate }}"
                                        data-type="{{ $item->currency->type }}"
                                        data-balance="{{ $item->balance }}"
                                        data-wallet="{{ $item->id }}"
                                        data-currency-id="{{ $item->currency->id }}"
                                        data-name="{{ $item->currency->country }}">{{ucwords($item->currency->name) ."( ".get_amount($item->balance,$item->currency->code." )")}}</option>
                                    @endforeach
                                </select>
                                <div class="note-area mt-10">
                                    <code class="d-block fees-show">--</code>
                                    <code class="d-block Total payable-total">--</code>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group">
                                <button type="submit" class="btn--base w-100 btn-loading buyBtn">{{ __("buy Card") }} <i class="las la-plus-circle ms-1"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{__("Preview")}}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-coins"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("card Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fw-bold request-amount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Total Charge") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fees">--</span>
                                </div>
                            </div>

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{__("Total Payable")}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="last payable-total text-warning">--</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            {{-- limit section  --}}
            <div class="dash-payment-item-wrapper limit">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{__("Limit Information")}}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-wallet"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Transaction Limit") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="limit-show">--</span>
                                </div>
                            </div>
                            @if ($cardCharge->daily_limit > 0)
                                <div class="preview-list-item">
                                    <div class="preview-list-left">
                                        <div class="preview-list-user-wrapper">
                                            <div class="preview-list-user-icon">
                                                <i class="las la-wallet"></i>
                                            </div>
                                            <div class="preview-list-user-content">
                                                <span>{{ __("Daily Limit") }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-list-right">
                                        <span class="limit-daily">--</span>
                                    </div>
                                </div>
                                <div class="preview-list-item">
                                    <div class="preview-list-left">
                                        <div class="preview-list-user-wrapper">
                                            <div class="preview-list-user-icon">
                                                <i class="las la-wallet"></i>
                                            </div>
                                            <div class="preview-list-user-content">
                                                <span>{{ __("Remaining Daily Limit") }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-list-right">
                                        <span class="daily-remaining">--</span>
                                    </div>
                                </div>
                            @endif
                            @if ($cardCharge->monthly_limit > 0)
                                <div class="preview-list-item">
                                    <div class="preview-list-left">
                                        <div class="preview-list-user-wrapper">
                                            <div class="preview-list-user-icon">
                                                <i class="las la-wallet"></i>
                                            </div>
                                            <div class="preview-list-user-content">
                                                <span>{{ __("Monthly Limit") }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-list-right">
                                        <span class="limit-monthly">--</span>
                                    </div>
                                </div>
                                <div class="preview-list-item">
                                    <div class="preview-list-left">
                                        <div class="preview-list-user-wrapper">
                                            <div class="preview-list-user-icon">
                                                <i class="las la-wallet"></i>
                                            </div>
                                            <div class="preview-list-user-content">
                                                <span>{{ __("Remaining Monthly Limit") }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-list-right">
                                        <span class="monthly-remaining">--</span>
                                    </div>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
