@extends('admin.layouts.master')

@section('title', __('Edit Crypto Exchange'))

@section('page_content')
    <div class="box box-default">
        <div class="box-body">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="top-bar-title padding-bottom pull-left">{{ __('Exchange Details') }}</div>
                </div>
                <div>
                    @if ($exchange->status)
                        <h4 class="text-left f-18">{{ __('Status') }} : {!! getStatusText($exchange->status) !!}</h4>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <section class="min-vh-100">
        <div class="my-30" id="transaction_edit">
            <form action="{{ route('admin.crypto_exchanges.update') }}" class="form-horizontal row" id="exchange_form" method="POST">
                {{ csrf_field() }}
                <!-- Page title start -->
                <div class="col-md-8">
                    <div class="box">
                        <div class="box-body">
                            <div class="panel">
                                <div class="panel-body">
                                    <div>
                                        <input type="hidden" value="{{ $exchange->id }}" name="id" id="id">
                                        <input type="hidden" value="{{ $exchange->uuid }}" name="uuid" id="uuid">
                                        <input type="hidden" value="{{ $exchange->type }}" name="type" id="type">
                                        <input type="hidden" value="{{ $exchange->user_id }}" name="user_id" id="user_id">
                                        <input type="hidden" value="{{ $exchange->from_currency }}" name="currency_id" id="currency_id">
                                        <input type="hidden" value="{{ $transaction->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
                                        <input type="hidden" value="{{ optional($transaction->transaction_type)->name  }}" name="transaction_type" id="transaction_type">
                                        <input type="hidden" value="{{ $transaction->status }}" name="transaction_status" id="transaction_status">
                                        <input type="hidden" value="{{ $transaction->transaction_reference_id }}" name="transaction_reference_id" id="transaction_reference_id">
                                        <input type="hidden" value="{{ $transaction->uuid }}" name="transaction_uuid" id="transaction_uuid">
                                        <input type="hidden" value="{{ route('admin.crypto_exchanges.index')}}" name="redirect">

                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="user">{{ __('Exchanger') }}</label>
                                            <input type="hidden" class="form-control f-14" name="user"
                                                value="{{ !empty($exchange->user_id) ? getColumnValue($exchange->user) : $exchange->email_phone }}">
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ !empty($exchange->user_id) ? getColumnValue($exchange->user) : $exchange->email_phone }}
                                                </p>
                                            </div>
                                        </div>

                                    

                                        @if ($exchange->uuid)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="exchange_uuid">{{ __('Transaction ID') }}</label>
                                                <input type="hidden" class="form-control f-14" name="exchange_uuid" value="{{ $exchange->uuid }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ $exchange->uuid }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if (isset($transaction->transaction_type_id->name))
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="type">{{ __('Type') }} {{ $transaction->status }} </label>
                                                <input type="hidden" class="form-control f-14" name="type" value="{{ str_replace('_', ' ', $transaction->transaction_type->name) }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ str_replace('_', ' ',$transaction->transaction_type->name) }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if (isset($exchange->fromCurrency->code))
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="from_wallet">{{ __('Exchange
                                                    From') }}</label>
                                                <input type="hidden" class="form-control f-14" name="from_wallet" value="{{ $exchange->fromCurrency->code }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ $exchange->fromCurrency->code }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if (isset($exchange->to_currency))
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="to_wallet">{{ __('Exchange To') }}</label>
                                                <input type="hidden" class="form-control f-14" name="to_wallet" value="{{ optional($exchange->toCurrency)->code }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ optional($exchange->toCurrency)->code }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($exchange->exchange_rate)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="exchange_rate">{{ __('Exchange Rate') }}</label>
                                                <input type="hidden" class="form-control f-14" name="exchange_rate" value="{{ $exchange->exchange_rate }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ moneyFormat(optional($exchange->fromCurrency)->symbol, formatNumber(1, $exchange->from_currency)) .' = '. moneyFormat(optional($exchange->toCurrency)->symbol, formatNumber($exchange->exchange_rate, $exchange->to_currency)) }}</p>
                                                </div>
                                            </div>
                                        @endif


                                        @if ($exchange->created_at)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="created_at">{{ __('Date') }}</label>
                                                <input type="hidden" class="form-control f-14" name="created_at" value="{{ $exchange->created_at }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ dateFormat($exchange->created_at) }}</p>
                                                </div>
                                            </div>
                                        @endif


                                        @if (isset($exchange->receiver_address) && !empty($exchange->receiver_address) )
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="transactions_address">{{ __('Receiver Address') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ $exchange->receiver_address }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if( isset($exchange->receiving_details) && !empty($exchange->receiving_details))
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="transactions_details">{{ __('Receiving Details') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ $exchange->receiving_details }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if( isset($exchange->payment_details) && !empty($exchange->payment_details))
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="transactions_details">{{ __('Payment Details') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ $exchange->payment_details }} {{ $transaction->file?->filename }}</p>
                                                </div>
                                            </div>
                                        @endif


                                        @if ($exchange->file_name && file_exists('public/uploads/files/crypto-details-file/' . $exchange->file_name))
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="transactions_file">{{ __('Attached File') }}</label>
                                                <div class="col-sm-9">                              
                                                    <a class="text-info" href="{{ url('public/uploads/files/crypto-details-file/') . '/' . $exchange->file_name }}" download={{ $exchange->file_name }}><i class="fa fa-fw fa-download"></i>
                                                        {{ $exchange->file_name }}
                                                    </a>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($transaction->payment_method?->id == Bank && $transaction->file?->filename && file_exists('public/uploads/files/bank_attached_files/' . $transaction->file?->filename))
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Attached File') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">
                                                            <a href="{{ url('public/uploads/files/bank_attached_files').'/'.$transaction->file?->filename }}" download={{ $transaction->file?->filename }}><i class="fa fa-fw fa-download"></i>
                                                                {{ $transaction->file?->originalname }}
                                                            </a>                                  
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        

                                        @php
                                            $transactionA = \App\Models\Transaction::where('uuid', $transaction->uuid)
                                                        ->first();
                                        @endphp
                                         
                                    
                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="status">{{ __('Change Status') }}</label>
                                            <div class="col-sm-6">
                                                <select class="form-control select2 w-50" name="status">
                                                    <option value="Pending"
                                                        {{ isset($exchange->status) && $exchange->status == 'Pending' ? 'selected' : '' }}>
                                                        {{ __('Pending') }}</option>
                                                    <option value="Success"
                                                        {{ isset($exchange->status) && $exchange->status == 'Success' ? 'selected' : '' }}>
                                                        {{ __('Success') }}</option>
                                                    <option value="Blocked"
                                                        {{ isset($exchange->status) && $exchange->status == 'Blocked' ? 'selected' : '' }}>
                                                        {{ __('Cancel') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 offset-md-3">
                                                <a id="cancel_anchor"
                                                    class="btn btn-theme-danger f-14 me-1"
                                                    href="{{ route('admin.crypto_exchanges.index') }}">{{ __('Cancel') }}</a>
                                                <button type="submit" class="btn btn-theme f-14"
                                                    id="exchange_edit">
                                                    <i class="fa fa-spinner fa-spin d-none"></i>
                                                    <span id="exchange_edit_text">{{ __('Update') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="box">
                        <div class="box-body">
                            <div class="panel">
                                <div class="panel-body">
                                    <div>

                                        @if ($exchange->amount)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="amount">{{ __('Amount') }}</label>
                                                <input type="hidden" class="form-control f-14" name="amount" value="{{ $exchange->amount }}">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($exchange->fromCurrency)->symbol, formatNumber($exchange->amount, $exchange->from_currency)) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        @if (isset($exchange->fee))
                                            <div class="form-group row total-deposit-feesTotal-space">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="fee">{{ __('Fees') }}
                                                    <span>
                                                        <small class="transactions-edit-fee">
                                                            @if (isset($transaction))
                                                                ({{ formatNumber($transaction->percentage, $exchange->from_currency) }}% +
                                                                {{ formatNumber($transaction->charge_fixed, $exchange->from_currency) }})
                                                            @else
                                                                ({{ 0 }}%+{{ 0 }})
                                                            @endif
                                                        </small>
                                                    </span>
                                                </label>
                                                <input type="hidden" class="form-control f-14" name="fee" value="{{ $exchange->fee }}">

                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($exchange->fromCurrency)->symbol, formatNumber($exchange->fee, $exchange->from_currency)) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                        <hr class="increase-hr-height">

                                        @php
                                            $total = $exchange->fee + $exchange->amount;
                                        @endphp

                                        @if (isset($total))
                                            <div class="form-group row total-deposit-space">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="total">{{ __('Total') }}</label>
                                                <input type="hidden" class="form-control f-14" name="total" value="{{ $total }}">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($exchange->fromCurrency)->symbol, formatNumber($total, $exchange->from_currency)) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                        @if (isset($total))
                                            <div class="form-group row total-deposit-space">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="total">{{ __('Exchange Amount') }}</label>
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($exchange->toCurrency)->symbol, formatNumber($exchange->get_amount, $exchange->to_currency)) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('extra_body_scripts')

<script type="text/javascript">
    'use strict';
    var updateText = "{{ __('Updating...') }}";
</script>

<script src="{{ asset('Modules/CryptoExchange/Resources/assets/js/admin_transaction.min.js') }}"></script>

@endpush
