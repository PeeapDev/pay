<?php

if (!app()->runningInConsole()) {
    return [
        'name' => 'Investment',
        'item_id' => 'mn7hifa2ruq',
        'options' => [
            ['label' => __('Settings'), 'url' => url(config('paymoney.prefix') . '/investment-settings/add')]
        ],
        'supported_versions' => '4.2.3',
        'payment_methods' => [
            'investment' => ['Wallet', 'Stripe', 'Paypal', 'PayUmoney', 'Payeer', 'Coinbase', 'Bank', 'Coinpayments']
        ],
        'transaction_types' => defined('Investment') ? [Investment] : [],
        'transaction_type_settings' => [
            'web' => [
                'sent' => defined('Investment') ? [Investment] : [],
                'received' => [],
            ],
            'mobile' => [
                'sent' => [
                    'Investment' => defined('Investment') ? Investment : ''
                ],
                'received' => []
            ]
        ],
        'transaction_list' => [
            'sender' => defined('Investment') ? [Investment => 'user'] : [],
            'receiver' => []
        ]
    ];
} else {
    return [
        'name' => 'Investment',
        'item_id' => 'mn7hifa2ruq',
        'options' => [
            ['label' => __('Settings'), 'url' => url(env('ADMIN_PREFIX') . '/investment-settings/add')]
        ],
        'supported_versions' => '4.2.3',
        'payment_methods' => [
            'investment' => ['Wallet', 'Stripe', 'Paypal', 'PayUmoney', 'Payeer', 'Coinbase', 'Bank', 'Coinpayments']
        ],
    ];
}
