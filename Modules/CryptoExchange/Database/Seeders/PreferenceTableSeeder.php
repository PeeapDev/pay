<?php

namespace Modules\CryptoExchange\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PreferenceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $pre = [
            ['category' => 'crypto_exchange', 'field' => 'verification', 'value' => 'email'],
            ['category' => 'crypto_exchange', 'field' => 'available', 'value' => 'all'],
            ['category' => 'crypto_exchange', 'field' => 'transaction_type', 'value' => 'all'],
        ];
        
        \App\Models\Preference::insert($pre);

    }
}
