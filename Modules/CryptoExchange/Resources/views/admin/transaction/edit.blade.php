@extends('admin.layouts.master')
@section('title', __('Edit Transaction'))

@section('page_content')

<div class="box box-default">
	<div class="box-body">
		<div class="d-flex justify-content-between">
			<div>
				@if ($transaction->status)
					<p class="text-left mb-0 f-18">{{ __('Status') }} :
                    @php
                        $transactionTypes = getPaymoneySettings('transaction_types')['web'];
                        if (in_array($transaction->transaction_type_id, $transactionTypes['all'])) {
                            echo getStatusText($transaction->status);
                        }
                    @endphp
                    </p>
				@endif
			</div>
		</div>
	</div>
</div>
<section class="min-vh-100">
    <div class="my-30">
        <form action="{{ route('admin.crypto_exchanges.update') }}" class="form-horizontal" id="transactions_form" method="POST">
            {{ csrf_field() }}
            <div class="row f-14">
                <!-- Page title start -->
                <div class="col-md-8">
                    <div class="box">
                        <div class="box-body">
                            <div class="panel">
                                <div>
                                    <div class="p-4 rounded">
                                        <input type="hidden" value="{{ $transaction->id }}" name="id" id="id">
                                        <input type="hidden" value="{{ $transaction->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
                                        <input type="hidden" value="{{ $transaction->transaction_reference_id }}" name="transaction_reference_id" id="transaction_reference_id">
                                        <input type="hidden" value="{{ $transaction->uuid }}" name="uuid" id="uuid">
                                        <input type="hidden" value="{{ $transaction->user_id }}" name="user_id" id="user_id">
                                        <input type="hidden" value="{{ $transaction->end_user_id }}" name="end_user_id" id="end_user_id">
                                        <input type="hidden" value="{{ $transaction->currency_id }}" name="currency_id" id="currency_id">
                                        <input type="hidden" value="{{ ($transaction->percentage) }}" name="percentage" id="percentage">
                                        <input type="hidden" value="{{ ($transaction->charge_percentage) }}" name="charge_percentage" id="charge_percentage">
                                        <input type="hidden" value="{{ ($transaction->charge_fixed) }}" name="charge_fixed" id="charge_fixed">
                                        <input type="hidden" value="{{ base64_encode($transaction->payment_method_id) }}" name="payment_method_id" id="payment_method_id">
                                        <input type="hidden" class="form-control" name="subtotal" value="{{ $transaction->subtotal }}">
                                        <input type="hidden" class="form-control" name="redirect" value="{{ url(config('adminPrefix') . '/transactions')}}">

                                        <!-- User -->
                                        <div class="form-group row">
                                            <label class="control-label col-sm-3 fw-bold text-sm-end" for="user">{{ __('Exchanger') }}</label>
                                            <input type="hidden" class="form-control" name="user" id="user" value="{{ !empty($transaction->user_id) ? getColumnValue($transaction->user) : $transaction->crypto_exchange?->email_phone }}">
                                            <div class="col-sm-9">
                                                <p class="form-control-static">{{ !empty($transaction->user_id) ? getColumnValue($transaction->user) : $transaction->crypto_exchange?->email_phone }}</p>
                                            </div>
                                        </div>

                                        <!-- Transaction ID -->
                                        <div class="form-group row">
                                            <label class="control-label col-sm-3 fw-bold text-sm-end" for="transactions_uuid">{{ __('Transaction ID') }}</label>
                                            <input type="hidden" class="form-control" name="transactions_uuid" id="transactions_uuid" value="{{ getColumnValue($transaction, 'uuid') }}">
                                            <div class="col-sm-9">
                                                <p class="form-control-static">{{ getColumnValue($transaction, 'uuid') }}</p>
                                            </div>
                                        </div>

                                        <!-- Type -->
                                        @if ($transaction->transaction_type_id)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end" for="type">{{ __('Type') }}</label>
                                                <input type="hidden" class="form-control" name="type" value="{{ str_replace('_', ' ', $transaction->transaction_type?->name) }}">
                                                <input type="hidden" class="form-control" name="transaction_type_id" value="{{ $transaction->transaction_type_id }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ str_replace('_', ' ', $transaction?->transaction_type?->name) }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Currency -->
                                        <div class="form-group row">
                                            <label class="control-label col-sm-3 fw-bold text-sm-end" for="currency">{{ __('Currency') }}</label>
                                            <input type="hidden" class="form-control" name="currency" value="{{ getColumnValue($transaction->currency, 'code') }}">
                                            <div class="col-sm-9">
                                                <p class="form-control-static">{{ getColumnValue($transaction->currency, 'code') }}</p>
                                            </div>
                                        </div>

                                        <!-- Payment Method -->
                                        @if (isset($transaction->payment_method_id))
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end" for="payment_method">{{ __('Payment Method') }}</label>
                                                <input type="hidden" class="form-control" name="payment_method" value="{{ ($transaction?->payment_method?->id == Mts) ? settings('name') : $transaction?->payment_method?->name }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ ($transaction?->payment_method?->name == Mts) ? settings('name') : $transaction->payment_method?->name }}</p>
                                                </div>
                                            </div>
                                        @endif

                                         
                                        @php
                                            $transactionA = \App\Models\Transaction::where('uuid', $transaction->uuid)
                                                        ->first();
                                        @endphp

                                        @if (isset($transactionA->payment_status))
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Payment Status') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{!! getStatusText($transactionA->payment_status) !!}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Created at date -->
                                        <div class="form-group row">
                                            <label class="control-label col-sm-3 fw-bold text-sm-end" for="created_at">{{ __('Date') }}</label>
                                            <input type="hidden" class="form-control" name="created_at" value="{{ getColumnValue($transaction, 'created_at') }}">
                                            <div class="col-sm-9">
                                                <p class="form-control-static">{{ dateFormat(getColumnValue($transaction, 'created_at')) }}</p>
                                            </div>
                                        </div>


                                         @if(module('CryptoExchange') && ($transaction->transaction_type_id == Crypto_Swap || $transaction->transaction_type_id == Crypto_Buy || $transaction->transaction_type_id == Crypto_Sell ))

                                            @if(isset($exchange_rate))
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end" for="transactions_uuid">{{ __('Exchange Rate') }}</label>
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $exchange_rate }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($receiving_details) && !empty($receiving_details))
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end" for="transactions_uuid">{{ __('Receiving Details') }}</label>
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $receiving_details }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($receiver_address))
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end" for="transactions_uuid">{{ __('Receiver Address') }}</label>
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $receiver_address }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($payment_details) && !empty($payment_details))
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end" for="transactions_uuid">{{ __('Payment Details') }}</label>
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $payment_details }}</p>
                                                    </div>
                                                </div>
                                            @endif


                                            @if(isset($file_name) && file_exists(public_path('/uploads/files/crypto-details-file/' . $file_name)))
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end" for="transactions_uuid">{{ __('Attached File') }}</label>
                                                    <div class="col-sm-9">
                                                        <a class="text-info" href="{{ url('/public/uploads/files/crypto-details-file').'/'.$file_name }}" download="{{ $file_name }}"><i class="fa fa-fw fa-download"></i>
                                                            {{ $file_name }}
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
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

                                        <!-- If bank deposit  -->
                                        @if ($transaction->bank)
                                              <div class="form-group row">
                                                  <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Bank Name') }}</label>
                                                  <input type="hidden" class="form-control" name="bank_name" value="{{ $transaction->bank?->bank_name }}">
                                                  <div class="col-sm-9">
                                                      <p class="form-control-static">{{ $transaction->bank?->bank_name }}</p>
                                                  </div>
                                              </div>

                                              <div class="form-group row">
                                                  <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Branch Name') }}</label>
                                                  <input type="hidden" class="form-control" name="bank_branch_name" value="{{ $transaction->bank?->bank_branch_name }}">
                                                  <div class="col-sm-9">
                                                      <p class="form-control-static">{{ $transaction->bank?->bank_branch_name }}</p>
                                                  </div>
                                              </div>

                                              <div class="form-group row">
                                                  <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Account Name') }}</label>
                                                  <input type="hidden" class="form-control" name="account_name" value="{{ $transaction->bank?->account_name }}">
                                                  <div class="col-sm-9">
                                                      <p class="form-control-static">{{ $transaction->bank?->account_name }}</p>
                                                  </div>
                                              </div>
                                        @endif
                                       
                                        <div class="form-group row align-items-center">
                                            <label class="control-label col-sm-3 fw-bold text-sm-end" for="status">{{ __('Change Status') }}</label>
                                            <div class="col-sm-6">
                                                <select class="form-control select2 w-60" name="status" id="status">
                                                    @if (module('CryptoExchange') )
                                                        <option value="Success" {{ $transaction->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                                        <option value="Pending"  {{ $transaction->status == 'Pending' ? 'selected':"" }}>{{ __('Pending') }}</option>
                                                        <option value="Blocked"  {{ $transaction->status == 'Blocked' ? 'selected':"" }}>{{ __('Cancel') }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                        
                                        <div class="row">
                                            <div class="col-md-6 offset-md-3">
                                                <a id="cancel_anchor" class="btn btn-theme-danger me-1 f-14" href="{{ url(\Config::get('adminPrefix').'/transactions') }}">{{ __('Cancel') }}</a>
                                                <button type="submit" class="btn btn-theme f-14" id="request_payment">
                                                    <i class="fa fa-spinner fa-spin d-none"></i> <span id="transactions_edit_text">{{ __('Update') }}</span>
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
                                <div>
                                    <div class="pt-4 rounded">
                                        @if ($transaction->subtotal)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-6 fw-bold text-sm-end" for="subtotal">{{ __('Amount') }}</label>
                                                <div class="col-sm-6">
                                                    {{ moneyFormat(optional($transaction->currency)->symbol, formatNumber($transaction->subtotal, $transaction->currency_id)) }}
                                                </div>
                                            </div>
                                        @endif

                                        <div class="form-group row total-deposit-feesTotal-space">
                                            <label class="control-label col-sm-6 d-flex fw-bold justify-content-end" for="fee">{{ __('Fees') }}
                                                <span>
                                                    <small class="transactions-edit-fee">
                                                        @if (isset($transaction))
                                                            ({{(($transaction->transaction_type?->name == "Payment_Sent") ? "0" : formatNumber($transaction->percentage, $transaction->currency_id))}}% + {{ formatNumber($transaction->charge_fixed, $transaction->currency_id)}})
                                                        @else
                                                            ({{0}}%+{{0}})
                                                        @endif
                                                    </small>
                                                </span>
                                            </label>
                                            <input type="hidden" class="form-control" name="fee" id="fee" value="{{ calculateFee($transaction) }}">
                                            <div class="col-sm-6">
                                                <p class="form-control-static">{{ moneyFormat(optional($transaction->currency)->symbol, formatNumber(calculateFee($transaction), $transaction->currency_id)) }}</p>
                                            </div>
                                        </div>

                                        <hr class="increase-hr-height">

                                        @if ($transaction->total)
                                            <div class="form-group row total-deposit-space">
                                                <label class="control-label col-sm-6 fw-bold text-sm-end" for="total">{{ __('Total') }}</label>
                                                <input type="hidden" class="form-control" name="total" id="total" value="{{ ($transaction->total) }}">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static">{{ moneyFormat(optional($transaction->currency)->symbol, str_replace("-",'',formatNumber($transaction->total, $transaction->currency_id)) ) }}</p>
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
        </form>
    </div>
</section>
@endsection
