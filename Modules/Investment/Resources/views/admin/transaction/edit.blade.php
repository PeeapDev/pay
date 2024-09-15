@extends('admin.layouts.master')
@section('title', __('Edit Transaction'))

@section('page_content')

    <div class="box box-default">
        <div class="box-body">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="top-bar-title padding-bottom pull-left">{{ __('Transaction Details') }}</div>
                </div>
                <!-- Transaction Status -->
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
            <div class="row f-14">
                <!-- Page title start -->
                <div class="col-md-8">
                    <div class="box">
                        <div class="box-body">
                            <div class="panel">
                                <div>
                                    <div class="p-4 rounded">
                                        <!-- Plan -->
                                        <div class="form-group row">
                                            <label
                                                class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Plan') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    {{ getColumnValue($transaction->invest?->investmentPlan, 'name', '') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- User -->
                                        <div class="form-group row">
                                            <label
                                                class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Investor') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static">{{ getColumnValue($transaction->user) }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Transaction ID -->
                                        <div class="form-group row">
                                            <label
                                                class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Transaction ID') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    {{ getColumnValue($transaction, 'uuid') }}</p>
                                            </div>
                                        </div>

                                        <!-- Type -->
                                        @if ($transaction->transaction_type_id)
                                            <div class="form-group row">
                                                <label
                                                    class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Type') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">
                                                        {{ str_replace('_', ' ', $transaction?->transaction_type?->name) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Investment Type -->
                                        <div class="form-group row">
                                            <label
                                                class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Investment Type') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    {{ getColumnValue($transaction->invest?->investmentPlan, 'investment_type', '') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Currency -->
                                        <div class="form-group row">
                                            <label
                                                class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Currency') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    {{ getColumnValue($transaction->currency, 'code') }}</p>
                                            </div>
                                        </div>

                                        <!-- Payment Method -->
                                        @if (isset($transaction->payment_method_id))
                                            <div class="form-group row">
                                                <label
                                                    class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Payment Method') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">
                                                        {{ $transaction?->payment_method?->id == Mts ? settings('name') : getColumnValue($transaction->payment_method, 'name', '') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        @if (isset($transaction->payment_status))
                                        <div class="form-group row">
                                            <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Payment Status') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static">{!! getStatusText($transaction->payment_status) !!}</p>
                                            </div>
                                        </div>
                                    @endif

                                        <!-- If bank deposit  -->
                                        @if ($transaction->bank)
                                            <div class="form-group row">
                                                <label
                                                    class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Bank Name') }}</label>
                                                <input type="hidden" class="form-control" name="bank_name"
                                                    value="{{ $transaction->bank?->bank_name }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ $transaction->bank?->bank_name }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label
                                                    class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Branch Name') }}</label>
                                                <input type="hidden" class="form-control" name="bank_branch_name"
                                                    value="{{ $transaction->bank?->bank_branch_name }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">
                                                        {{ $transaction->bank?->bank_branch_name }}</p>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label
                                                    class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Account Name') }}</label>
                                                <input type="hidden" class="form-control" name="account_name"
                                                    value="{{ $transaction->bank?->account_name }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">
                                                        {{ $transaction->bank?->account_name }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($transaction->payment_method?->id == Bank && $transaction->file?->filename && file_exists('public/uploads/files/bank_attached_files' .$transaction->file?->filename))
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

                                        <!-- Created at date -->
                                        <div class="form-group row">
                                            <label
                                                class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Profit') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    {{ getColumnValue($transaction->invest, 'estimate_profit', '') }}
                                                </p>
                                            </div>
                                        </div>
                                        <!-- Created at date -->
                                        <div class="form-group row">
                                            <label
                                                class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Date') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    {{ dateFormat(getColumnValue($transaction, 'created_at')) }}</p>
                                            </div>
                                        </div>

                                        <!-- Investment Status -->
                                        <div class="form-group row">
                                            <label
                                                class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Investment Status') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    {{ getColumnValue($transaction->invest, 'status', '') }}</p>
                                            </div>
                                        </div>


                                        @if ($transaction->status ==  'Success')
                                            <!-- Back Button -->
                                            <div class="row">
                                                <div class="col-md-6 offset-md-3">
                                                    <a id="cancel_anchor" class="btn btn-theme-danger me-1 f-14"
                                                        href="{{ url(config('adminPrefix') . '/transactions') }}">{{ __('Back') }}</a>
                                                </div>
                                            </div>
                                        @elseif ($transaction?->payment_method?->id == Bank)
                                            <form action="{{ route('investments.update', $transaction->id)}}" method="POST">
                                                @csrf
                                                <div class="form-group row align-items-center">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Change Status') }}</label>
                                                    <div class="col-sm-6">
                                                        <select class="form-control select2 w-60" name="status">
                                                                <option value="Success" {{ $transaction->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                                                <option value="Pending"  {{ $transaction->status == 'Pending' ? 'selected':"" }}>{{ __('Pending') }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 offset-md-3">
                                                        <a id="cancel_anchor" class="btn btn-theme-danger me-1 f-14" href="{{ url(config('adminPrefix').'/transactions') }}">{{ __('Cancel') }}</a>
                                                        <button type="submit" class="btn btn-theme f-14" id="request_payment">
                                                            <i class="fa fa-spinner fa-spin d-none"></i> <span id="transactions_edit_text">{{ __('Update') }}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amount Section -->
                <div class="col-md-4">
                    <div class="box">
                        <div class="box-body">
                            <div class="panel">
                                <div>
                                    <div class="pt-4 rounded">
                                        @if ($transaction->subtotal)
                                            <div class="form-group row">
                                                <label
                                                    class="control-label col-sm-6 fw-bold text-sm-end">{{ __('Amount') }}</label>
                                                <div class="col-sm-6">
                                                    {{ moneyFormat(optional($transaction->currency)->symbol, formatNumber($transaction->subtotal, $transaction?->currency?->id)) }}
                                                </div>
                                            </div>
                                        @endif

                                        <div class="form-group row total-deposit-feesTotal-space">

                                            <label
                                                class="control-label col-sm-6 d-flex fw-bold justify-content-end">{{ __('Fees') }}
                                                <span>
                                                    <small class="transactions-edit-fee">
                                                        @if (isset($transaction))
                                                            ({{ $transaction?->transaction_type?->name == 'Payment_Sent' ? '0' : formatNumber($transaction->percentage, $transaction?->currency?->id) }}%
                                                            +
                                                            {{ formatNumber($transaction->charge_fixed, $transaction?->currency?->id) }})
                                                        @else
                                                            (0% + 0)
                                                        @endif
                                                    </small>
                                                </span>
                                            </label>
                                            @php
                                                $total_transaction_fees = $transaction->charge_percentage + $transaction->charge_fixed;
                                            @endphp

                                            <div class="col-sm-6">
                                                <p class="form-control-static">
                                                    {{ moneyFormat(optional($transaction->currency)->symbol, formatNumber($total_transaction_fees, $transaction?->currency?->id)) }}
                                                </p>

                                            </div>
                                        </div>

                                        <hr class="increase-hr-height">

                                        @if ($transaction->total)
                                            <div class="form-group row total-deposit-space">
                                                <label
                                                    class="control-label col-sm-6 fw-bold text-sm-end">{{ __('Total') }}</label>
                                                <div class="col-sm-6">
                                                    <p class="form-control-static">
                                                        {{ moneyFormat(optional($transaction->currency)->symbol, str_replace('-', '', formatNumber($transaction->total, $transaction?->currency?->id))) }}
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
            </div>
        </div>
    </section>
@endsection
