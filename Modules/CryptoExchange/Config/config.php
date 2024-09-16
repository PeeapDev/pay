<?php

if (!app()->runningInConsole()) {
    return [
        'name' => 'CryptoExchange',
        'item_id' => 'fv8wtkr1jfc',
        'options' => [
            ['label' => __('Settings'), 'url' => url(config('paymoney.prefix') . '/crypto_settings')]
        ],
        'supported_versions' => '4.2.3',
        'payment_methods' => [
            'crypto_buy' => ['Stripe', 'Paypal', 'PayUmoney', 'Payeer', 'Bank', 'Coinbase', 'Coinpayments'],
        ],
        'permission_group' => ['Crypto Direction', 'Crypto Exchange Transaction', 'Crypto Exchange Settings', 'Crypto Exchange'],
        'transaction_types' => (defined('Crypto_Sell') && defined('Crypto_Buy') && defined('Crypto_Swap')) ? [Crypto_Sell, Crypto_Buy, Crypto_Swap] : [],
        'transaction_type_settings' => [
            'web' => [
                'sent' => (defined('Crypto_Sell') && defined('Crypto_Buy') && defined('Crypto_Swap')) ? [Crypto_Sell, Crypto_Buy, Crypto_Swap] : [],
                'received' => [],
            ],
            'mobile' => [
                'sent' => [
                    'Crypto_Sell' => defined('Crypto_Sell') ? Crypto_Sell : '',
                    'Crypto_Buy' => defined('Crypto_Buy') ? Crypto_Buy : '',
                    'Crypto_Swap' =>  defined('Crypto_Swap') ? Crypto_Swap : ''
                ],
                'received' => []
            ]
        ],
        'transaction_list' => [
            'sender' => (defined('Crypto_Sell') && defined('Crypto_Buy') && defined('Crypto_Swap'))
                        ? [ Crypto_Sell => 'user', Crypto_Buy => 'user', Crypto_Swap => 'user']
                        : [],
            'receiver' => []
        ]
    ];
} else {
    return [
        'name' => 'CryptoExchange',
        'item_id' => 'fv8wtkr1jfc',
        'options' => [
            ['label' => __('Settings'), 'url' => url(config('paymoney.prefix') . '/crypto_settings')]
        ],
        'supported_versions' => '4.2.3',
        'payment_methods' => [
            'crypto_buy' => ['Stripe', 'Paypal', 'PayUmoney', 'Payeer', 'Bank', 'Coinbase', 'Coinpayments'],
        ],
        'permission_group' => ['Crypto Direction', 'Crypto Exchange Transaction', 'Crypto Exchange Settings', 'Crypto Exchange']
    ];
}
