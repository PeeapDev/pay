@isset($gateway)
    <div class="payment-gateway-currencies-wrapper">
        @foreach ($gateway->currencies as $item)
            <div class="custom-card mt-15 gateway-currency" id="{{ Str::lower($item->currency_code) . "-block" }}" data-target="{{ $item->id }}">
                <div class="card-header">
                    <h6 class="currency-title">{{ $item->name }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 form-group payment-file">
                            @include('admin.components.form.input-file',[
                                'label'             => __("Gateway Image"),
                                'name'              => "gateway_currency[".$item->currency_code."][image]",
                                'class'             => "file-holder",
                                'old_files_path'    => files_asset_path('payment-gateways'),
                                'old_files'         => $item->image,
                            ])
                        </div>
                        <div class="col-xxl-2 col-xl-4 col-lg-4 mb-10">
                            <div class="custom-inner-card">
                                <div class="card-inner-header">
                                    <h5 class="title">{{ __("Amount Limit") }}</h5>
                                </div>
                                <div class="card-inner-body">
                                    <div class="row">
                                        <div class="col-xl-12 form-group">
                                            <div class="form-group">
                                                @include('admin.components.form.input-amount',[
                                                    'label'         => __("Minimum"),
                                                    'name'          => "gateway_currency[".$item->currency_code."][min_limit]",
                                                    'value'         => old("gateway_currency.".$item->currency_code.".min_limit",get_amount($item->min_limit,null,$pricison)),
                                                    'currency'      => $item->currency_code,
                                                ])
                                            </div>
                                        </div>
                                        <div class="col-xl-12 form-group">
                                            <div class="form-group">
                                                @include('admin.components.form.input-amount',[
                                                    'label'         => __("Maximum"),
                                                    'name'          => "gateway_currency[".$item->currency_code."][max_limit]",
                                                    'value'         => old("gateway_currency.".$item->currency_code.".max_limit",get_amount($item->max_limit,null,$pricison)),
                                                    'currency'      => $item->currency_code,
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-xl-4 col-lg-4 mb-10">
                            <div class="custom-inner-card">
                                <div class="card-inner-header d-block">
                                    <h5 class="title">{{ __("Transaction Limit") }} </h5>
                                    <span class="small text--base">({{ __("Execute if the value is greater than zero") }})</span>
                                </div>
                                <div class="card-inner-body">
                                    <div class="row">
                                        <div class="col-xl-12 form-group">
                                            <div class="form-group">
                                                @include('admin.components.form.input-amount',[
                                                    'label'         => __("Daily Limit"),
                                                    'name'          => "gateway_currency[".$item->currency_code."][daily_limit]",
                                                    'value'         => old("gateway_currency.".$item->currency_code.".daily_limit",get_amount($item->daily_limit,null,$pricison)),
                                                    'currency'      => $item->currency_code,
                                                ])
                                            </div>
                                        </div>
                                        <div class="col-xl-12 form-group">
                                            <div class="form-group">
                                                @include('admin.components.form.input-amount',[
                                                    'label'         => __("Monthly Limit"),
                                                    'name'          => "gateway_currency[".$item->currency_code."][monthly_limit]",
                                                    'value'         => old("gateway_currency.".$item->currency_code.".monthly_limit",get_amount($item->monthly_limit,null,$pricison)),
                                                    'currency'      => $item->currency_code,
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-xl-4 col-lg-4 mb-10">
                            <div class="custom-inner-card">
                                <div class="card-inner-header">
                                    <h5 class="title">{{ __("Charges") }}</h5>
                                </div>
                                <div class="card-inner-body">
                                    <div class="row">
                                        <div class="col-xl-12 form-group">
                                            <div class="form-group">
                                                @include('admin.components.form.input-amount',[
                                                    'label'         => __("Fixed"),
                                                    'name'          => "gateway_currency[".$item->currency_code."][fixed_charge]",
                                                    'value'         => old("gateway_currency.".$item->currency_code.".fixed_charge",get_amount($item->fixed_charge,null,$pricison)),
                                                    'currency'      => $item->currency_code,
                                                ])
                                            </div>
                                        </div>
                                        <div class="col-xl-12 form-group">
                                            <div class="form-group">
                                                @include('admin.components.form.input-amount',[
                                                    'label'         =>__( "Percent"),
                                                    'name'          => "gateway_currency[".$item->currency_code."][percent_charge]",
                                                    'value'         => old("gateway_currency.".$item->currency_code.".percent_charge",get_amount($item->percent_charge,null,$pricison)),
                                                    'currency'      => "%",
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-xl-4 col-lg-4 mb-10">
                            <div class="custom-inner-card">
                                <div class="card-inner-header">
                                    <h5 class="title">{{ __("Rate") }}</h5>
                                </div>
                                <div class="card-inner-body">
                                    <div class="row">
                                        <div class="col-xl-12 form-group">
                                            <div class="form-group">
                                                <label>{{ __("Rate") }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text append ">1 &nbsp; <span class="default-currency">{{ get_default_currency_code($default_currency) }}</span> = </span>
                                                    <input type="te" class="form--control" value="{{ old("gateway_currency.".$item->currency_code.".rate",get_amount($item->rate,null,$pricison)) }}" name="{{ "gateway_currency[".$item->currency_code."][rate]" }}">
                                                    <span class="input-group-text">{{ $item->currency_code }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 form-group">
                                            <div class="form-group">
                                                @include('admin.components.form.input',[
                                                    'label'     => __("Symbol"),
                                                    'name'      => "gateway_currency[".$item->currency_code."][currency_symbol]",
                                                    'value'     => old("gateway_currency.".$item->currency_code.".currency_symbol",$item->currency_symbol),
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endisset
