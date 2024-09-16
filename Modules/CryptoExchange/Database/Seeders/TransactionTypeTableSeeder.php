<?php

namespace Modules\CryptoExchange\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TransactionTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $type = [
            ['name' => 'Crypto_Swap'],
            ['name' => 'Crypto_Buy'],
            ['name' => 'Crypto_Sell'],
        ];

        \App\Models\TransactionType::insert($type);

    }
}
