@extends('admin.layouts.master')

@push('css')
@endpush

@section('page-title')
    @include('admin.components.page-title', ['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb', [
        'breadcrumbs' => [
            [
                'name' => __('Dashboard'),
                'url' => setRoute('admin.dashboard'),
            ],
        ],
        'active' => __('Add Money Details'),
    ])
@endsection

@section('content')

<div class="custom-card">
    <div class="card-header">
        <h6 class="title">{{ __("Add Money Details") }}</h6>
    </div>
    <div class="card-body">
        <form class="card-form">
            <div class="row align-items-center mb-10-none">
                <div class="col-xl-4 col-lg-4 form-group">
                    <ul class="user-profile-list-two">
                        <li class="one">{{ __("Date") }}: <span>{{ @$data->created_at->format('d-m-y h:i:s A') }}</span></li>
                        <li class="two">{{ __("web_trx_id") }}: <span>{{ @$data->trx_id }}</span></li>
                        <li class="three">{{ __("Fullname") }}: <span>
                            @if($data->user_id != null)
                                <a href="{{ setRoute('admin.users.details',$data->creator->username) }}">{{ $data->creator->fullname }} ({{ __("USER") }})</a>
                            @elseif($data->agent_id != null)
                                <a href="{{ setRoute('admin.agents.details',$data->creator->username) }}">{{ $data->creator->fullname }}</a>
                            @endif
                            </span></li>
                        <li class="four">{{ __("Method") }}: <span>{{ @$data->currency->name }}</span></li>
                        <li class="five">{{ __("Amount") }}: <span>{{ get_amount($data->request_amount,$data->creator_wallet->currency->code,get_wallet_precision($data->creator_wallet->currency)) }}</span></li>
                    </ul>
                </div>

                <div class="col-xl-4 col-lg-4 form-group">
                    <div class="user-profile-thumb">
                        <img src="{{ get_gateway_image($data->currency->payment_gateway_id) }}" alt="payment">
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 form-group">
                    <ul class="user-profile-list two">
                        <li class="one">{{ __("Charge") }}: <span>{{ get_amount(@$data->charge->total_charge??0,$data->currency->currency_code??get_default_currency_code(),$precision) }} </span></li>
                        <li class="two">{{ __("Rate") }}:
                            <span>1 {{ $data->creator_wallet->currency->code}} = {{ get_amount($data->details->amount->exchange_rate??$data->currency->rate,$data->currency->currency_code??get_default_currency_code(),$precision) }}</span>
                        </li>
                        <li class="three">{{ __("After Charge") }}: <span>{{ get_amount(@$data->payable,$data->currency->currency_code??get_default_currency_code(),$precision) }} </span></li>
                        <li class="four">{{ __("Payable") }}: <span>{{ get_amount(@$data->payable,$data->currency->currency_code??get_default_currency_code(),$precision) }}</span></li>
                        <li class="five">{{__("Status") }}:  <span class="{{ @$data->stringStatus->class }}">{{ __(@$data->stringStatus->value) }}</span></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

@if(@$data->status == 2)
<div class="custom-card mt-15">
    <div class="card-header">
        <h6 class="title">{{ __("Information of Logs") }}</h6>
    </div>
    <div class="card-body">
        <ul class="product-sales-info">
            @php
               $get_values = isset($data->details->get_values) ? $data->details->get_values : $data->details;
            @endphp

            @foreach ($get_values ?? [] as $item)
            <li>
                @if ($item->type == "file")
                    @php
                        $file_link = get_file_link("kyc-files",$item->value);
                    @endphp
                    <span class="kyc-title">{{ $item->label }}:</span>
                    @if (its_image($item->value))
                        <div class="kyc-image ">
                            <img class="img-fluid" width="200px" src="{{ $file_link }}" alt="{{ $item->label }}">
                        </div>
                    @else
                        <span class="text--danger">
                            @php
                                $file_info = get_file_basename_ext_from_link($file_link);
                            @endphp
                            <a href="{{ setRoute('file.download',["kyc-files",$item->value]) }}" >
                                {{ Str::substr($file_info->base_name ?? "", 0 , 20 ) ."..." . $file_info->extension ?? "" }}
                            </a>
                        </span>
                    @endif
                @else
                    <span class="kyc-title">{{ $item->label }}:</span>
                    <span>{{ $item->value }}</span>
                @endif
            </li>
        @endforeach
        </ul>
        <div class="product-sales-btn">
            <button type="button" class="btn btn--base approvedBtn">{{ __("approve") }}</button>
            <button type="button" class="btn btn--danger rejectBtn" >{{ __("reject") }}</button>
        </div>
    </div>
</div>

<div class="modal fade" id="approvedModal" tabindex="-1" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="approvedModalLabel">
                <h5 class="modal-title">{{ __("Approved Confirmation") }} ( <span class="fw-bold text-success">{{ get_amount($data->request_amount,$data->creator_wallet->currency->code,get_wallet_precision($data->creator_wallet->currency)) }}</span> )</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.add.money.approved') }}" method="POST">
                    @csrf
                    @method("PUT")
                    <div class="row mb-10-none">
                        <div class="col-xl-12 col-lg-12 form-group">
                            <input type="hidden" name="id" value={{ @$data->id }}>
                           <p>{{ __("Are you sure to approved this request?") }}</p>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                <button type="submit" class="btn btn--base btn-loading">{{ __("Approved") }}</button>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="rejectModalLabel">
                <h5 class="modal-title">{{ __("Rejection Confirmation") }} ( <span class="fw-bold text-danger">{{ get_amount($data->request_amount,$data->creator_wallet->currency->code,get_wallet_precision($data->creator_wallet->currency)) }}</span> )</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.add.money.rejected') }}" method="POST">
                    @csrf
                    @method("PUT")
                    <div class="row mb-10-none">
                        <div class="col-xl-12 col-lg-12 form-group">
                            <input type="hidden" name="id" value={{ @$data->id }}>
                            @include('admin.components.form.textarea',[
                                'label'         => __("Explain Rejection Reason*"),
                                'name'          => 'reject_reason',
                                'value'         => old('reject_reason')
                            ])
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                <button type="submit" class="btn btn--base">{{ __("confirm") }}</button>
            </div>
        </form>
        </div>
    </div>
</div>
@endif


@endsection


@push('script')
<script>
    $(document).ready(function(){
        @if($errors->any())
        var modal = $('#rejectModal');
        modal.modal('show');
        @endif
    });
</script>
<script>
     (function ($) {
        "use strict";
        $('.approvedBtn').on('click', function () {
            var modal = $('#approvedModal');
            modal.modal('show');
        });
        $('.rejectBtn').on('click', function () {
            var modal = $('#rejectModal');
            modal.modal('show');
        });
    })(jQuery);

</script>
@endpush
