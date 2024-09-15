<div class="box box-primary">
    <div class="box-header with-border ps-3">
        <h3 class="box-title underline">{{ __('Transaction Type') }} </h3>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked flex-column">
            <li {{ isset($list_menu) && $list_menu == 'deposit' ? 'class=active' : '' }}>
                <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/feeslimit/deposit/' . $currency->id) }}'>{{ __('Deposit') }}</a>
            </li>
            <li {{ isset($list_menu) && $list_menu == 'withdrawal' ? 'class=active' : '' }}>
                <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/feeslimit/withdrawal/' . $currency->id) }}'>{{ __('Withdraw') }}</a>
            </li>
            <li {{ isset($list_menu) && $list_menu == 'transfer' ? 'class=active' : '' }}>
                <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/feeslimit/transfer/' . $currency->id) }}'>{{ __('Transfer') }}</a>
            </li>
            <li {{ isset($list_menu) && $list_menu == 'request_payment' ? 'class=active' : '' }}>
                <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/feeslimit/request_payment/' . $currency->id) }}'>{{ __('Request
                    Payment') }}</a>
            </li>
            @if ($currency->type == 'fiat')
                <li {{ isset($list_menu) && $list_menu == 'exchange' ? 'class=active' : '' }}>
                    <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/feeslimit/exchange/' . $currency->id) }}'>{{ __('Exchange') }}</a>
                </li>
            @endif

            @foreach (getCustomModules() as $module)
                @if (!empty(config($module->get('alias') . '.fees_limit_settings')))
                    @foreach (config($module->get('alias') . '.' . 'fees_limit_settings') as $key => $transactionType)
                        @if(empty($transactionType['transaction_type'])) @continue @endif
                        <li {{ isset($list_menu) && $list_menu == strtolower($transactionType['transaction_type']) ? 'class=active' : '' }} >
                            <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/feeslimit/'. strtolower($transactionType['transaction_type']) .'/' . $currency->id) }}'>{{ $transactionType['display_name'] }}</a>
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>
    </div>
</div>
