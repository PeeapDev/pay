<div class="transac-parent cursor-pointer" data-bs-toggle="modal" data-bs-target="#transaction-Info-{{ $key }}">
    <div class="d-flex justify-content-between transac-child">
        <div class="d-flex w-50">
            <div class="deposit-circle d-flex justify-content-center align-items-center">
                <img src="{{ image(null,  'investment') }}" alt="{{ __('Transaction') }}">
            </div>
            
            <div class="ml-20 r-ml-8">
                <p class="mb-0 text-dark f-16 gilroy-medium theme-tran">{{ $transaction->transaction_type?->name }}</p>
                <div class="d-flex flex-wrap">
                    <p class="mb-0 text-gray-100 f-13 leading-17 gilroy-regular tran-title mt-2">{{ $transaction->invest?->investmentPlan?->name }}</p>
                    <p class="mb-0 text-gray-100 f-13 leading-17 gilroy-regular tran-title mt-2 d-flex justify-content-center align-items-center">
                        <svg class="mx-2 text-muted-100" width="4" height="4" viewBox="0 0 4 4" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="2" cy="2" r="2" fill="currentColor" />
                        </svg>
                    {{ dateFormat($transaction->created_at) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center">
            <div>
                <p class="mb-0 gilroy-medium text-gray-100 r-f-12 f-16 ph-20"> 
                    <svg class="mx-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.89992 3.84617L7.02742 5.71867L5.88409 6.86784C5.65113 7.10045 5.33538 7.23109 5.00617 7.23109C4.67697 7.23109 4.36122 7.10045 4.12826 6.86784L1.10659 3.84617C0.709923 3.4495 0.995756 2.77284 1.54992 2.77284H8.45659C9.01659 2.77284 9.29659 3.4495 8.89992 3.84617Z" fill="#2AAA5E"/>
                    </svg> {{ moneyFormat($transaction->currency?->symbol, formatNumber($transaction->subtotal, $transaction->currency?->id)) }}</span>
                </p>
                        
                <p class="{{ getColor($transaction->status) }} f-13 gilroy-regular text-end mt-6 mb-0 status-info rlt-txt">
                    {{ $transaction->status }}
                </p>
            </div>
            <div class="cursor-pointer transaction-arrow  ml-28 r-ml-12">
                <a class="arrow-hovers" data-bs-toggle="modal" data-bs-target="#transaction-Info-{{ $key }}">
                    <svg class="nscaleX-1" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.5312 1.52861C3.27085 1.78896 3.27085 2.21107 3.5312 2.47141L7.0598 6.00001L3.5312 9.52861C3.27085 9.78895 3.27085 10.2111 3.5312 10.4714C3.79155 10.7318 4.21366 10.7318 4.47401 10.4714L8.47401 6.47141C8.73436 6.21106 8.73436 5.78895 8.47401 5.52861L4.47401 1.52861C4.21366 1.26826 3.79155 1.26826 3.5312 1.52861Z" fill="currentColor" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-overly" id="transaction-Info-{{ $key }}" tabindex="-1" aria-hidden="true">
    <div class="transac modal-dialog modal-dialog-centered modal-lg res-dialog">
        <div class="modal-content modal-transac transaction-modal">
            <div class="modal-body modal-themeBody">
                <div class="d-flex position-relative modal-res">
                    <button type="button" class="cursor-pointer close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <svg class="position-absolute close-btn text-gray-100" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.24408 5.24408C5.56951 4.91864 6.09715 4.91864 6.42259 5.24408L10 8.82149L13.5774 5.24408C13.9028 4.91864 14.4305 4.91864 14.7559 5.24408C15.0814 5.56951 15.0814 6.09715 14.7559 6.42259L11.1785 10L14.7559 13.5774C15.0814 13.9028 15.0814 14.4305 14.7559 14.7559C14.4305 15.0814 13.9028 15.0814 13.5774 14.7559L10 11.1785L6.42259 14.7559C6.09715 15.0814 5.56951 15.0814 5.24408 14.7559C4.91864 14.4305 4.91864 13.9028 5.24408 13.5774L8.82149 10L5.24408 6.42259C4.91864 6.09715 4.91864 5.56951 5.24408 5.24408Z" fill="currentColor" />
                        </svg>
                    </button>
                    <div class="deposit-transac d-flex flex-column justify-content-center p-4 text-wrap">
                        <div class="d-flex justify-content-center text-primary align-items-center transac-img">
                            <img src="{{ image(null, 'investment') }}" alt="{{ __('Transaction') }}" class="img-fluid">
                        </div>
                        <p class="mb-0 mt-28 text-dark gilroy-medium f-15 r-f-12 r-mt-18 text-center">{{ getTransactionInfo($transaction->transaction_type?->name)['name'] }}&nbsp;{{ __('Amount') }}</p>
                        <p class="mb-0 text-dark gilroy-Semibold f-24 leading-29 r-f-26 text-center l-s2 mt-10">{{ moneyFormat($transaction->currency?->symbol, formatNumber($transaction->total, $transaction->currency?->id)) }}</p>
                        <p class="mb-0 mt-18 text-gray-100 gilroy-medium f-13 leading-20 r-f-14 text-center">{{ dateFormat($transaction->created_at) }}</p>
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('user.investment-money.print', $transaction->id) }}" class="infoBtn-print cursor-pointer f-14 gilroy-medium text-dark mt-35 d-flex justify-content-center align-items-center">{!! svgIcons('printer') !!}&nbsp;<span>{{ __('Print') }}</span>
                            </a>
                        </div>
                    </div>
                    <div class="ml-20 trans-details">
                        <p class="mb-0 mt-9 text-dark dark-5B f-20 gilroy-Semibold transac-title">{{ __('Transaction Details') }}</p>

                        <div class="row gx-sm-5">
                            <div class="col-6">
                                <p class="mb-0 mt-4 text-gray-100 gilroy-medium f-13 leading-20 r-f-9 r-mt-11">{{ __('Invested By') }}</p>
                                <p class="mb-0 mt-5p text-dark gilroy-medium f-15 leading-22 r-text">{{ getTransactionInfo($transaction->transaction_type?->name, $transaction)['user'] }}</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-0 mt-4 text-gray-100 gilroy-medium f-13 leading-20 r-f-9 r-mt-11">{{ __('Currency') }}</p>
                                <p class="mb-0 mt-5p text-dark gilroy-medium f-15 leading-22 r-text">{{ $transaction->currency?->code }}</p>
                            </div>
                        </div>
                        <div class="row gx-sm-5">
                            <div class="col-6">
                                <p class="mb-0 mt-20 text-gray-100 gilroy-medium f-13 leading-20 r-f-9 r-mt-11">{{ __('Transaction ID') }}</p>
                                <p class="mb-0 mt-5p text-dark gilroy-medium f-15 leading-22 r-text">{{ $transaction->uuid }}</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-0 mt-20 text-gray-100 gilroy-medium f-13 leading-20 r-f-9 r-mt-11">{{ __('Transaction Fee') }}</p>
                                <p class="mb-0 mt-5p text-dark gilroy-medium f-15 leading-22 r-text">
                                    @php
                                        $fee = $transaction->charge_percentage + $transaction->charge_fixed;
                                    @endphp
                                    {{ 
                                        $fee != 0 
                                            ? moneyFormat($transaction->currency?->symbol, formatNumber($fee, $transaction->currency?->id)) 
                                            : '-'
                                    }}
                                </p>
                            </div>
                        </div>
                        <div class="row gx-sm-5">
                            <div class="col-6">
                                <p class="mb-0 mt-20 text-gray-100 gilroy-medium f-13 leading-20 r-f-9 r-mt-11">{{ __('Payment Method') }}</p>
                                <p class="mb-0 mt-5p text-dark gilroy-medium f-15 leading-22 r-text">{{ getTransactionPaymentMethod($transaction->payment_method?->name) ?? '-' }}</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-0 mt-20 text-gray-100 gilroy-medium f-13 leading-20 r-f-9 r-mt-11">{{ __('Status') }}</p>
                                <p class="mb-0 mt-5p {{ getColor($transaction->status) }} gilroy-medium f-15 leading-22 r-text">{{ $transaction->status }}</p>
                            </div>
                        </div>
                        <p class="hr-border w-100 mb-0"></p>
                        <div class="row gx-sm-5">

                            <!-- Amount -->
                            <div class="col-6">
                                <p class="mb-0 mt-4 text-gray-100 dark-B87 gilroy-medium f-13 leading-20 r-f-9 r-mt-11">{{ __('Invested Amount') }}</p>
                                <p class="mb-0 mt-5p text-dark dark-CDO gilroy-medium f-15 leading-22 r-text">{{ moneyFormat($transaction->currency?->symbol, formatNumber($transaction->subtotal, $transaction->currency?->id)) }}</p>
                            </div>

                            <!-- Total Amount -->
                            <div class="col-6">
                                <p class="mb-0 mt-4 text-gray-100 dark-B87 gilroy-medium f-13 leading-20 r-f-9 r-mt-11">{{ __('Total Amount') }}</p>
                                <p class="mb-0 mt-5p text-dark dark-CDO gilroy-medium f-15 leading-22 r-text">{{ moneyFormat($transaction->currency?->symbol, formatNumber($transaction->total, $transaction->currency?->id)) }}</p>
                            </div>
                        </div>
                        
                        <!-- Transaction Note -->
                        @if (!empty($transaction->note))
                        <div class="row gx-sm-5">
                            <div class="col-12">
                                <p class="mb-0 mt-20 text-gray-100 gilroy-medium f-13 leading-20 r-f-9 r-mt-11">{{ __('Note') }}</p>
                                <p class="mb-0 mt-5p text-dark gilroy-medium f-15 leading-22 r-text">{{ $transaction->note ?? '' }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>