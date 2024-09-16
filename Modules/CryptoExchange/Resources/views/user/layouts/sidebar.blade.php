@if(isActive('CryptoExchange') && Common::has_permission(auth()->id(),'manage_crypto_exchange') && cryptoValidity('auth_user'))
<li class="accordion-item bg-secondary border-0">
    <div class="accordion-header {{ request()->route()->named('user_dashboard.crypto_buy_sell.*') ? 'bg-info' : '' }}" id="cryptoExchange">
        <button class="mb-1 shadow-none bg-transparent p-0 d-flex align-items-center list-option h-46 accordion-button accordion-icon collapsed {{ request()->route()->named('user_dashboard.crypto_buy_sell.*') ? 'text-white bg-info' : 'text-info-100' }}" type="button" data-bs-toggle="collapse" data-bs-target="#flush-cryptoExchange" aria-expanded="false" aria-controls="flush-cryptoExchange">
            <span class="ms-3 mr-20">{!! menuSvgIcon('user_dashboard.crypto_buy_sell.*') !!}</span>
            <span class="child-currency">{{ __('Crypto Exchange') }}</span>
        </button>
    </div>
    <div id="flush-cryptoExchange" class="accordion-collapse collapse {{ request()->is('crypto-exchange/*') || request()->is('crypto-buy-sell/success') ? 'show' : '' }}" aria-labelledby="cryptoExchange" data-bs-parent="#accordion-menu">
        <ul class="accordion-body collapse-child ml-28 p-0 pl-16 mr-20">
            <li><a href="{{ route('user_dashboard.crypto_buy_sell.create') }}" class="mb-2 ml-34 pl-14 f-14 d-flex align-items-center list-option h-46 {{ (request()->is('crypto-exchange/buy-sell') || request()->is('crypto-exchange/confirm') || request()->is('crypto-buy-sell/success') || request()->is('crypto-exchange/verification'))  ? 'text-white bg-info' : 'text-info-100' }}">{{ __('Crypto Exchange') }}</a></li>
            <li><a href="{{ route('user_dashboard.crypto_buy_sell.list') }}" class="mb-2 ml-34 pl-14 f-14 d-flex align-items-center list-option h-46 {{ request()->is('crypto-exchange/buy-sell-list')  ? 'text-white bg-info' : 'text-info-100' }}">{{ __('Crypto Exchange List') }}</a></li>
        </ul>
    </div>
</li>
@endif