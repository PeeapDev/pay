@extends('agent.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("agent.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{__(@$page_title)}}</h3>
        </div>
    </div>
    <div class="row mb-30-none">
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __(@$page_title) }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute('agent.send.money.confirmed') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center">
                                            <span>{{ __("Exchange Rate") }} <span class="exchange-rate">--</span></span>
                                        </code>
                                    </div>
                                </div>
                                <div class="col-xxl-12 col-xl-12 col-lg-12 form-group paste-wrapper">
                                    <label>{{ __("Phone/Email") }} ({{ __("Agent") }})<span class="text--base">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="credentials" class="form--control checkUser" id="username" placeholder="{{ __("Enter Email/Phone") }}" value="{{ old('credentials') }}" />
                                    </div>
                                    <button type="button" class="paste-badge scan"  data-toggle="tooltip" title="Scan QR"><i class="fas fa-camera"></i></button>
                                    <label class="exist text-start"></label>
                                </div>
                                <div class="col-xxl-6 col-xl-12 col-lg-6 form-group">
                                    <label>{{ __("Sender Amount") }}<span>*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" required placeholder="{{__('enter Amount')}}" name="sender_amount" value="{{ old("amount") }}">
                                        <select class="form--control nice-select sender_wallet" name="sender_wallet">
                                            @foreach ($sender_wallets ?? [] as $data)
                                                <option value="{{  $data->code }}"
                                                    data-rate="{{ $data->rate }}"
                                                    data-type="{{ $data->type }}"
                                                    data-currency-id="{{ $data->id }}"
                                                    data-sender-country-name="{{ $data->name }}"
                                                    >{{  $data->code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <code class="d-block mt-10 text-start text--warning balance-show">--</code>
                                </div>
                                <div class="col-xxl-6 col-xl-12 col-lg-6 form-group">
                                    <label>{{ __("Receiver Amount") }}<span>*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" required placeholder="{{__('enter Amount')}}" name="receiver_amount" value="{{ old("amount") }}">
                                        <select class="form--control nice-select receiver_wallet" name="receiver_wallet">
                                            @foreach ($receiver_wallets ?? [] as $data)
                                                <option value="{{  $data->code }}"
                                                    data-rate="{{ $data->rate }}"
                                                    data-type="{{ $data->type }}"
                                                    data-receiver-country-name="{{ $data->name }}"
                                                    >{{  $data->code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    @include('admin.components.form.textarea',[
                                        'label'         => __("Remarks")." (".__("Optional").")",
                                        'name'          => "remark",
                                        'placeholder'   => __("explain Trx"),
                                        'value'         => old("remark"),
                                    ])
                                </div>
                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading transfer">{{ __("Confirm Send") }} <i class="fas fa-paper-plane ms-1"></i></i></button>
                                </div>
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
                        <h5 class="title">{{__("Send Money Preview")}}</h5>
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
                                            <span>{{ __("Sending Wallet") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fw-bold sending-wallet">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-wallet"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Receiving Wallet") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fw-bold receiving-wallet">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-coins"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Entered Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="request-amount">--</span>
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
                                    <span class="fees-show">--</span>
                                </div>
                            </div>

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Recipient Received") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="recipient-get">--</span>
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
                            @if ($sendMoneyCharge->daily_limit > 0)
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
                            @if ($sendMoneyCharge->monthly_limit > 0)
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
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title ">{{__("Send Money Log")}}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('agent.transactions.index','transfer-money') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('agent.components.transaction-log',compact("transactions"))
        </div>
    </div>
</div>
<div class="modal fade" id="scanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
            <div class="modal-body text-center">
                <video id="preview" class="p-1 border" style="width:300px;"></video>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">@lang('close')</button>
            </div>
      </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
    //'use strict'
    (function ($) {
        $('.scan').click(function(){
            var scanner = new Instascan.Scanner({ video: document.getElementById('preview'), scanPeriod: 5, mirror: false });
            scanner.addListener('scan',function(content){
                var route = '{{url('agent/qr/scan/')}}'+'/'+content
                $.get(route, function( data ) {
                    if(data.error){
                        // alert(data.error)
                        throwMessage('error',[data.error]);
                    } else {
                        $("#username").val(data);
                        $("#username").focus()
                    }
                    $('#scanModal').modal('hide')
                });
            });

            Instascan.Camera.getCameras().then(function (cameras){
                if(cameras.length>0){
                    $('#scanModal').modal('show')
                        scanner.start(cameras[0]);
                } else{
                //    alert('No cameras found.');
                    throwMessage('error',["No camera found "]);
                }
            }).catch(function(e){
                // alert('No cameras found.');
                throwMessage('error',["No camera found "]);
            });
        });
        $('.checkUser').on('keyup',function(e){
            var url = '{{ route('agent.send.money.check.exist') }}';
            var value = $(this).val();
            var token = '{{ csrf_token() }}';
            if ($(this).attr('name') == 'credentials') {
                var data = {credentials:value,_token:token}

            }
            $.post(url,data,function(response) {
                if(response.own){
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exist').addClass('text--danger').text(response.own);
                    $('.transfer').attr('disabled',true)
                    return false
                }
                if(response['data'] != null){
                    if($('.exist').hasClass('text--danger')){
                        $('.exist').removeClass('text--danger');
                    }
                    $('.exist').text(`Valid agent for transaction.`).addClass('text--success');
                    $('.transfer').attr('disabled',false)
                } else {
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exist').text('Agent doesn\'t  exists.').addClass('text--danger');
                    $('.transfer').attr('disabled',true)
                    return false
                }

            });
        });
    })(jQuery);
</script>
<script>
    var defualCurrency = "{{ get_default_currency_code() }}";
    var defualCurrencyRate = "{{ get_default_currency_rate() }}";

       $(document).ready(function(){
           senderBalance();
           getExchangeRate()
           getLimit();
           getDailyMonthlyLimit();
           getFees();
           getReceiverAmount();
           getSenderAmount();
           getPreview();
           get_remaining_limits();
       });
       $("input[name=sender_amount]").keyup(function(){
           getExchangeRate();
           getFees();
           getReceiverAmount();
           getPreview();
           get_remaining_limits();
       });
       $("input[name=receiver_amount]").keyup(function(){
           getExchangeRate();
           getFees();
           getSenderAmount();
           getPreview();
           get_remaining_limits();
       });
       $("select[name=sender_wallet]").change(function(){
           senderBalance();
           getExchangeRate();
           getLimit();
           getDailyMonthlyLimit();
           getFees();
           getReceiverAmount();
           getPreview();
           get_remaining_limits();
       });
       $("select[name=receiver_wallet]").change(function(){
           senderBalance();
           getExchangeRate();
           getLimit();
           getDailyMonthlyLimit();
           getFees();
           getSenderAmount();
           getPreview();
           get_remaining_limits();
       });

       function acceptVar(){
           var defualCurrency          = defualCurrency;
           var defualCurrencyRate      = defualCurrencyRate;

           var senderCurrencyVal       = $("select[name=sender_wallet] :selected");
           var senderCurrencyCode      = $("select[name=sender_wallet] :selected").val();
           var senderCurrencyRate      = $("select[name=sender_wallet] :selected").data('rate');
           var senderCurrencyType      = $("select[name=sender_wallet] :selected").data('type');

           var receiverCurrencyVal     = $("select[name=receiver_wallet] :selected");
           var receiverCurrencyCode    = $("select[name=receiver_wallet] :selected").val();
           var receiverCurrencyRate    = $("select[name=receiver_wallet] :selected").data('rate');
           var receiverCurrencyType     = $("select[name=receiver_wallet] :selected").data('type');


           var currencyMinAmount       = "{{getAmount($sendMoneyCharge->min_limit)}}";
           var currencyMaxAmount       = "{{getAmount($sendMoneyCharge->max_limit)}}";
           var currencyFixedCharge     = "{{getAmount($sendMoneyCharge->fixed_charge)}}";
           var currencyPercentCharge   = "{{getAmount($sendMoneyCharge->percent_charge)}}";
           var currencyDailyLimit      = "{{getAmount($sendMoneyCharge->daily_limit)}}";
           var currencyMonthlyLimit      = "{{getAmount($sendMoneyCharge->monthly_limit)}}";

           if(senderCurrencyType == "CRYPTO"){
               var senderPrecison = "{{ get_precision_from_admin()['crypto_precision_value'] }}";
           }else{
               var senderPrecison = "{{  get_precision_from_admin()['fiat_precision_value'] }}";
           }
           if(receiverCurrencyType == "CRYPTO"){
               var receiverPrecison = "{{ get_precision_from_admin()['crypto_precision_value'] }}";
           }else{
               var receiverPrecison = "{{  get_precision_from_admin()['fiat_precision_value'] }}";
           }

           return {
               defualCurrency:defualCurrency,
               defualCurrencyRate:defualCurrencyRate,

               sCurrencyVal:senderCurrencyVal,
               sCurrencyCode:senderCurrencyCode,
               sCurrencyRate:senderCurrencyRate,
               sPrecison:senderPrecison,

               rCurrencyVal:receiverCurrencyVal,
               rCurrencyCode:receiverCurrencyCode,
               rCurrencyRate:receiverCurrencyRate,
               rPrecison:receiverPrecison,

               currencyMinAmount:currencyMinAmount,
               currencyMaxAmount:currencyMaxAmount,

               currencyFixedCharge:currencyFixedCharge,
               currencyPercentCharge:currencyPercentCharge,

               currencyDailyLimit:currencyDailyLimit,
               currencyMonthlyLimit:currencyMonthlyLimit,

           };
       }
       function getLimit(){
           var sender_currency = acceptVar().sCurrencyCode;
           var sender_currency_rate = acceptVar().sCurrencyRate;
           var min_limit = acceptVar().currencyMinAmount;
           var max_limit = acceptVar().currencyMaxAmount

           if($.isNumeric(min_limit) && $.isNumeric(max_limit)) {
               var min_limit_calc = parseFloat(min_limit*sender_currency_rate).toFixed(acceptVar().sPrecison);
               var max_limit_clac = parseFloat(max_limit*sender_currency_rate).toFixed(acceptVar().sPrecison);
               $('.limit-show').html( min_limit_calc + " " + sender_currency + " - " + max_limit_clac + " " + sender_currency);
               return {
                   minLimit:min_limit_calc,
                   maxLimit:max_limit_clac,
               };
           }else {
               $('.limit-show').html("--");
               return {
                   minLimit:0,
                   maxLimit:0,
               };
           }
       }

       function getDailyMonthlyLimit(){
           var sender_currency = acceptVar().sCurrencyCode;
           var sender_currency_rate = acceptVar().sCurrencyRate;
           var daily_limit = acceptVar().currencyDailyLimit;
           var monthly_limit = acceptVar().currencyMonthlyLimit

           if($.isNumeric(daily_limit) && $.isNumeric(monthly_limit)) {
               if(daily_limit > 0 ){
                   var daily_limit_calc = parseFloat(daily_limit * sender_currency_rate).toFixed(acceptVar().sPrecison);
                   $('.limit-daily').html( daily_limit_calc + " " + sender_currency);
               }else{
                   $('.limit-daily').html("");
               }

               if(monthly_limit > 0 ){
                   var montly_limit_clac = parseFloat(monthly_limit * sender_currency_rate).toFixed(acceptVar().sPrecison);
                   $('.limit-monthly').html( montly_limit_clac + " " + sender_currency);

               }else{
                   $('.limit-monthly').html("");
               }

           }else {
               $('.limit-daily').html("--");
               $('.limit-monthly').html("--");
               return {
                   dailyLimit:0,
                   monthlyLimit:0,
               };
           }

       }
       function feesCalculation(){
           var sender_currency = acceptVar().sCurrencyCode;
           var sender_currency_rate = acceptVar().sCurrencyRate;
           var sender_amount = $("input[name=sender_amount]").val();
           (sender_amount == "" || isNaN(sender_amount)) ? sender_amount = 0 : sender_amount = sender_amount;

           var fixed_charge = acceptVar().currencyFixedCharge;
           var percent_charge = acceptVar().currencyPercentCharge;

           if($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
               // Process Calculation
               var fixed_charge_calc = parseFloat(sender_currency_rate*fixed_charge);
               var percent_charge_calc  = (parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
               var total_charge = parseFloat(fixed_charge_calc) + parseFloat(percent_charge_calc);
               total_charge = parseFloat(total_charge);
               // return total_charge;
               return {
                   total:  parseFloat(total_charge).toFixed(acceptVar().sPrecison),
                   fixed:  parseFloat(fixed_charge_calc).toFixed(acceptVar().sPrecison),
                   percent:  parseFloat(percent_charge_calc).toFixed(acceptVar().sPrecison),
               };
           }else {
               // return "--";
               return false;
           }
       }
       function getFees(){
           var sender_currency = acceptVar().sCurrencyCode;
           var percent = acceptVar().currencyPercentCharge;
           var charges = feesCalculation();

           if (charges == false) {
               return false;
           }
           $(".fees-show").html( parseFloat(charges.fixed).toFixed(acceptVar().sPrecison) + " " + sender_currency + " + " + parseFloat(percent).toFixed(acceptVar().sPrecison) + "%  = "+ parseFloat(charges.total).toFixed(acceptVar().sPrecison)+ " "+ sender_currency);
       }
       function getPreview(){
           var sender_currency = acceptVar().sCurrencyCode;
           var sender_currency_rate = acceptVar().sCurrencyRate;

           var receiver_currency = acceptVar().rCurrencyCode;
           var receiver_currency_rate = acceptVar().rCurrencyRate;

           var senderAmount = $("input[name=sender_amount]").val();
           (senderAmount == "" || isNaN(senderAmount)) ? senderAmount = 0 : senderAmount = senderAmount;

           var receiverAmount = $("input[name=receiver_amount]").val();
           receiverAmount == "" ? receiverAmount = 0 : receiverAmount = receiverAmount;

           $(".sending-wallet").text(sender_currency + " (" + acceptVar().sCurrencyVal.data('sender-country-name') + ")");
           $(".receiving-wallet").text(receiver_currency + " (" + acceptVar().rCurrencyVal.data('receiver-country-name') + ")");

           // Sending Amount
           $('.request-amount').text(parseFloat(senderAmount).toFixed(acceptVar().sPrecison) + " " + sender_currency);

           // // Fees
           var charges = feesCalculation();
           var total_charge = 0;
           if(senderAmount == 0){
               total_charge  = parseFloat(0).toFixed(acceptVar().sPrecison);
           }else{
               total_charge = charges.total;
           }

           $('.fees').text(parseFloat(total_charge).toFixed(acceptVar().sPrecison) + " " + sender_currency);
           //recipient received
           $('.recipient-get').text(parseFloat(receiverAmount).toFixed(acceptVar().rPrecison) + " " + receiver_currency);

               // Pay In Total
           var totalPay = parseFloat(senderAmount)
           var pay_in_total = 0;
           if(senderAmount == 0){
                   pay_in_total = parseFloat(0).toFixed(acceptVar().sPrecison);
           }else{
                   pay_in_total =  parseFloat(totalPay) + parseFloat(charges.total);
           }
           $('.payable-total').text(parseFloat(pay_in_total).toFixed(acceptVar().sPrecison) + " " + sender_currency);

       }

       function senderBalance() {
           var senderCurrency = acceptVar().sCurrencyCode;
           var csrfToken = $('meta[name="csrf-token"]').attr('content');
           $.ajax({
               type: 'POST',
               url: "{{ route('agent.wallets.balance') }}",
               data: {
                   target: senderCurrency,
                   _token: csrfToken
               },
               success: function(response) {
                   $('.balance-show').html("{{ __('Available Balance') }}: " + parseFloat(response.data).toFixed(acceptVar().sPrecison) + " " + senderCurrency);
               }
           });
       }
       function getExchangeRate(){
           var sender_currency = acceptVar().sCurrencyCode;
           var sender_currency_rate = acceptVar().sCurrencyRate;
           var receiver_currency = acceptVar().rCurrencyCode;
           var receiver_currency_rate = acceptVar().rCurrencyRate;
           var rate = parseFloat(receiver_currency_rate) / parseFloat(sender_currency_rate);
           $('.exchange-rate').html("1 " + sender_currency + " = " + parseFloat(rate).toFixed(acceptVar().rPrecison) + " " + receiver_currency);

           return rate;
       }
       function getReceiverAmount(){
           var sender_currency = acceptVar().sCurrencyCode;
           var sender_currency_rate = acceptVar().sCurrencyRate;

           var receiver_currency = acceptVar().rCurrencyCode;
           var receiver_currency_rate = acceptVar().rCurrencyRate;

           var sender_amount = $("input[name=sender_amount]").val();
           var receiver_amount = $("input[name=receiver_amount]");

           if($.isNumeric(sender_amount)) {
               var rate = parseFloat(receiver_currency_rate) / parseFloat(sender_currency_rate);
               var receiver_will_get = parseFloat(rate) * parseFloat(sender_amount);
               receiver_will_get = parseFloat(receiver_will_get).toFixed(acceptVar().rPrecison);
               receiver_amount.val(receiver_will_get);
               preview_receiver_will_get = receiver_will_get;
           }else {
               receiver_amount.val("");
               preview_receiver_will_get = "0";
           }
       }
       function getSenderAmount(){
           var sender_currency = acceptVar().sCurrencyCode;
           var sender_currency_rate = acceptVar().sCurrencyRate;

           var receiver_currency = acceptVar().rCurrencyCode;
           var receiver_currency_rate = acceptVar().rCurrencyRate;

           var sender_amount = $("input[name=sender_amount]");
           var receiver_amount = $("input[name=receiver_amount]").val();

           if($.isNumeric(receiver_amount)) {
               var rate = parseFloat(sender_currency_rate) / parseFloat(receiver_currency_rate);
               var sender_will_get = parseFloat(rate) * parseFloat(receiver_amount);
               sender_will_get = parseFloat(sender_will_get).toFixed(acceptVar().sPrecison);
               sender_amount.val(sender_will_get);
               preview_receiver_will_get = parseFloat(receiver_amount);
           }else {
               sender_amount.val("");
               preview_receiver_will_get = "0";
           }
       }
       function get_remaining_limits(){
           var csrfToken           = $('meta[name="csrf-token"]').attr('content');
           var user_field          = "agent_id";
           var user_id             = "{{ userGuard()['user']->id }}";
           var transaction_type    = "{{ payment_gateway_const()::TYPETRANSFERMONEY }}";
           var currency_id         = acceptVar().sCurrencyVal.data('currency-id');
           var sender_amount       = $("input[name=sender_amount]").val();

           (sender_amount == "" || isNaN(sender_amount)) ? sender_amount = 0 : sender_amount = sender_amount;

           var charge_id           = "{{ $sendMoneyCharge->id }}";
           var attribute           = "{{ payment_gateway_const()::SEND }}"

           $.ajax({
               type: 'POST',
               url: "{{ route('global.get.total.transactions') }}",
               data: {
                   _token:             csrfToken,
                   user_field:         user_field,
                   user_id:            user_id,
                   transaction_type:   transaction_type,
                   currency_id:        currency_id,
                   sender_amount:      sender_amount,
                   charge_id:          charge_id,
                   attribute:          attribute,
               },
               success: function(response) {
                   var sender_currency = acceptVar().sCurrencyCode;

                   var status  = response.status;
                   var message = response.message;
                   var amount_data = response.data;

                   if(status == false){
                       $('.transfer').attr('disabled',true);
                       $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                       $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                       throwMessage('error',[message]);
                       return false;
                   }else{
                       $('.transfer').attr('disabled',false);
                       $('.daily-remaining').html(amount_data.remainingDailyTxnSelected + " " + sender_currency);
                       $('.monthly-remaining').html(amount_data.remainingMonthlyTxnSelected + " " + sender_currency);
                   }
               },
           });
       }

</script>

@endpush
