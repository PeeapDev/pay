@extends('user.layouts.master')

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">

    </div>
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __(@$page_title) }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="exchange-area-wrapper text-center">
                            <div class="exchange-area mb-20">
                                <code class="d-block text-center"><span>{{ __("Current Balance") }}</span>
                                    {{ get_amount(@$myCard->balance,$myCard->currency) }}
                                </code>
                            </div>
                        </div>
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hourglass-end"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Card Type") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--warning">{{ __((ucwords(@$myCard->card_type))) }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hourglass-end"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Card Brand") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--warning">{{ __((ucwords(@$myCard->card_brand??"Visa"))) }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-credit-card"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("cardI d") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$myCard->card_id }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hourglass-end "></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("customer Id") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$myCard->customer_id }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-user-tag"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("card Number") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    @if(is_null($myCard->card_number) )
                                        <span>---- ---- ---- ----</span>
                                    @else
                                        @php
                                            $card_pan = str_split($myCard->card_number, 4);
                                        @endphp
                                        @foreach($card_pan as $key => $value)
                                        <span>{{ @$value }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hourglass-start"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Cvv") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ __(@$myCard->cvv??"***") }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-business-time"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{__("expiration")}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{@$myCard->expiry??"00/00/0000" }}</span>
                                </div>
                            </div>


                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-city"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("city") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$myCard->user->strowallet_customer->city??"" }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-city"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("state") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ $myCard->user->strowallet_customer->state??"" }}</span>
                                </div>
                            </div>

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-file-archive"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{__("zip Code")}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$myCard->user->strowallet_customer->zipCode??"" }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{__("Status") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <div class="toggle-container">

                                        @include('admin.components.form.switcher',[

                                            'name'          => 'is_active',
                                            'value'         => old('is_active',@$myCard->is_active ),
                                            'options'       => [__('UnFreeze') => 1,__('Freeze') => 0],
                                            'onload'        => true,
                                            'data_target'   => @$myCard->id,
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __("Billing Address") }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <ul class="billing-list">
                            <li>
                                <span>{{ __("Billing Country") }}</span>
                                <h6>United State</h6>
                            </li>
                            <li>
                                <span>{{ __("Billing City") }}</span>
                                <h6>Miami</h6>
                            </li>
                            <li>
                                <span>{{ __("Billing State") }}</span>
                                <h6>3401 N. Miami, Ave. Ste 230</h6>
                            </li>
                            <li>
                                <span>{{ __("Billing Zip Code") }}</span>
                                <h6>33127</h6>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')

<script>
     $(document).ready(function(){
        switcherAjax("{{ setRoute('user.strowallet.virtual.card.change.status') }}");
    })
</script>

@endpush
