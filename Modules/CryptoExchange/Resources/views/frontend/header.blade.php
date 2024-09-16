@if (isActive('CryptoExchange') && cryptoValidity('guest_user'))
    <li class="nav-item {{ isset($menu) && $menu == 'Crypto Exchange' ? 'nav-active' : '' }}">
        <a href="{{ route('guest.crypto_exchange.home') }}" class="nav-link"> {{ __('Crypto Exchange') }}</a>
    </li>
@endif
