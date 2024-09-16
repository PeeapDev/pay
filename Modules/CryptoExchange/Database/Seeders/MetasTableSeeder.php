<?php

namespace Modules\CryptoExchange\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MetasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $metas = [
            ['url' => 'crypto-exchange/create', 'title' => 'Crypto Exchange', 'description' => 'Crypto Exchange', 'keywords' => ''],
            ['url' => 'crypto-exchange/verification', 'title' => 'Crypto Identity Verification', 'description' => 'Identity Verification', 'keywords' => ''],
            ['url' => 'crypto-exchange/receiving-info', 'title' => 'Crypto Receiving Info', 'description' => 'Receiving Info', 'keywords' => ''],
            ['url' => 'crypto-exchange/payment', 'title' => 'Crypto Payment Info', 'description' => 'Crypto Payment Info', 'keywords' => ''],
            ['url' => 'crypto-exchange/make-payment', 'title' => 'Crypto Payment Info', 'description' => 'Crypto Payment Info', 'keywords' => ''],
            ['url' => 'crypto-exchange/success', 'title' => 'Crypto Transaction Success', 'description' => 'Crypto Transaction Success', 'keywords' => ''],
            ['url' => 'crypto-exchange/buy-sell', 'title' => 'Crypto Exchange', 'description' => 'Crypto Exchange', 'keywords' => ''],
            ['url' => 'crypto-buy-sell-confirm', 'title' => 'Crypto Transaction Confirm', 'description' => 'Crypto Transaction Confirm', 'keywords' => ''],
            ['url' => 'crypto-exchange/confirm', 'title' => 'Crypto Transaction Confirm', 'description' => 'Crypto Transaction Confirm', 'keywords' => ''],
            ['url' => 'crypto-buy-sell/success', 'title' => 'Crypto Transaction Success', 'description' => 'Crypto Transaction Success', 'keywords' => ''],
            ['url' => 'crypto-exchange/bank-payment', 'title' => 'Crypto Transaction Success', 'description' => 'Crypto Transaction Success', 'keywords' => ''],
            ['url' => 'crypto-exchange/track-transaction/{uuid}', 'title' => 'Track Transaction', 'description' => 'Track Transaction', 'keywords' => ''],
            ['url' => 'crypto-exchange/paypal-payment/success/{amount}', 'title' => 'Crypto Transaction Success', 'description' => 'Crypto Transaction Success', 'keywords' => ''],
            ['url' => 'crypto-exchange/buy-sell-list', 'title' => 'Crypto Transaction List', 'description' => 'Crypto Transaction List', 'keywords' => ''],
            ['url' => 'crypto-exchange/complete', 'title' => 'Crypto Transaction Success', 'description' => 'Crypto Transaction Success', 'keywords' => '']
        ];

        \App\Models\Meta::insert($metas);

    }
}
