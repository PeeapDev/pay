@extends('admin.layouts.master')

@push('css')

@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __($page_title)])
@endsection

@section('content')
    {{-- api key update --}}
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __($page_title) }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" action="{{ setRoute('admin.live.exchange.rate.update',$provider->slug) }}" method="POST">
                @csrf
                @method("PUT")
                <div class="row mb-10-none">
                    <div class="col-xl-12 col-lg-12 form-group">
                        <div class="row" >
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 form-group">
                                <label>{{ __("Access Key") }} *</label>
                                <div class="input-group append">
                                    <span class="input-group-text"><i class="las la-key"></i></span>
                                    <input type="text" class="form--control" name="access_key" value="{{ @$provider->value->access_key }}">
                                </div>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 form-group">
                                <label>{{ __("Base URL") }} *</label>
                                <div class="input-group append">
                                    <span class="input-group-text"><i class="las la-link"></i></span>
                                    <input type="text" class="form--control" name="base_url" value="{{ @$provider->value->base_url }}">
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 form-group">
                                <label>{{ __("Multiply By") }} * (<span class="text-warning">{{ __('Greater than zero and up to two.') }}</span>)</label>
                                <div class="input-group append">
                                    <span class="input-group-text"><i class="las la-info"></i></span>
                                    <input type="text" class="form--control number-input" name="multiply_by" value="{{get_amount($provider->multiply_by,null,get_wallet_precision())}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.button.form-btn',[
                            'class'         => "w-100 btn-loading",
                            'text'          => __("update"),
                            'permission'    => "admin.live.exchange.rate.update"
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>
    {{-- api key update --}}

    {{-- exchange rate update permission --}}
    <div class="custom-card mt-20">
        <div class="card-header">
            <h6 class="title">{{ __("Apply Permissions For The Exchange Rate API.") }}</h6>
        </div>
        <div class="card-body">
            <div class="custom-card mb-10">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 mb-10">
                            <div class="table-area custom-table-area">
                                <div class="table-wrapper">
                                    <div class="table-responsive">
                                        <table class="custom-table">
                                            <tbody>
                                                    <tr>
                                                        <td>{{ __("Currency Permision")}}</td>
                                                        <td>
                                                            @include('admin.components.form.switcher',[
                                                                'name'          => 'currency_module',
                                                                'value'         => $provider->currency_module,
                                                                'options'       => [__("Enable") => 1,__("Disable") => 0],
                                                                'onload'        => true,
                                                                'data_target'   => $provider->slug,
                                                                'permission'    => "admin.live.exchange.rate.module.permission",
                                                            ])

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>{{ __("Payment Gateway Permision")}}</td>
                                                        <td>
                                                            @include('admin.components.form.switcher',[
                                                                'name'          => 'payment_gateway_module',
                                                                'value'         => $provider->payment_gateway_module,
                                                                'options'       => [__("Enable") => 1,__("Disable") => 0],
                                                                'onload'        => true,
                                                                'data_target'   => $provider->slug,
                                                                'permission'    => "admin.live.exchange.rate.module.permission",
                                                            ])

                                                        </td>
                                                    </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- exchange rate update permission --}}

    {{-- send exchange rate update request --}}
    <div class="custom-card mt-20">
        <div class="card-header">
            <h6 class="title">{{ __("Send API Request") }}</h6>
        </div>
        <div class="card-body">
            <div class="custom-card mb-10">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 mb-10">
                            <div class="table-area custom-table-area">
                                <div class="table-wrapper">
                                    <div class="table-responsive">
                                        <table class="custom-table">
                                            <tbody>
                                                    <tr>
                                                        <td>{{ __("Exchange Rate Update")}}</td>
                                                        <td>
                                                        <form class="card-form" action="{{ setRoute('admin.live.exchange.rate.send.request') }}" method="POST">
                                                            @csrf
                                                            @method("PUT")
                                                            <div class="col-xl-12 col-lg-12 form-group">
                                                                @include('admin.components.button.form-btn',[
                                                                    'class'         => "w-100 btn-loading",
                                                                    'text'          => __("Send Request"),
                                                                    'permission'    => "admin.live.exchange.rate.send.request"
                                                                ])
                                                            </div>
                                                            <form>

                                                        </td>
                                                    </tr>


                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- send exchange rate update request --}}

    {{-- check updated currency list --}}
    <div class="custom-card mt-20">
        <div class="card-header">
            <h6 class="title">{{ __("Exchange Currency Rate Updated By Currency Layer API") }}</h6>
        </div>
        <div class="card-body">
            <div class="custom-card">
                <div class="card-body">
                    <div class="row mb-30-none">
                        <div class="col-md-8 mb-30">
                            <div class="custom-inner-card">
                                <div class="card-inner-body">
                                    <b class="d-block mb-3">{{ __("Supported Currencies")}} : {{ count(updateAbleCurrency()['matching_currencies']) }}</b>
                                    @foreach (updateAbleCurrency()['matching_currencies'] ?? [] as $code)
                                        <span class="badge bg-success">{{ $code }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-30">
                            <div class="custom-inner-card">
                                <div class="card-inner-body">
                                    <b class="d-block mb-3">{{ __("Unsupported Currencies")}} : {{ count(updateAbleCurrency()['missing_currencies']) }}</b>
                                    @foreach (updateAbleCurrency()['missing_currencies'] ?? [] as $code)
                                        <span class="badge bg-warning">{{ $code }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- check updated currency list --}}
@endsection

@push('script')
    <script>
        switcherAjax("{{ setRoute('admin.live.exchange.rate.module.permission') }}");
    </script>
    <script>
        $(document).ready(function(){
            set_multiply_value();
        });

        $("input[name=multiply_by]").keyup(function(){
            set_multiply_value();
        });
        function acceptVar() {
           var multiplyByValue = $("input[name=multiply_by]").val();
           return {
                mByValue:multiplyByValue,
           };
       }
       function set_multiply_value(){
            var mByValue = acceptVar().mByValue;

            (mByValue > 2 || mByValue < 0 ) ? mByValue = "{{get_amount($provider->multiply_by,null,get_wallet_precision())}}" : mByValue = mByValue;
            $("input[name=multiply_by]").val(mByValue);
       }

    </script>
@endpush
