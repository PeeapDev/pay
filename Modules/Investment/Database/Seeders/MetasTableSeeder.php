<?php

namespace Modules\Investment\Database\Seeders;

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
            ['url' => 'investment/plans', 'title' => 'Investment Plans', 'description' => 'Investment Plans', 'keywords' => ''],
            ['url' => 'investment-list/{status}', 'title' => 'Investment List', 'description' => 'Investment List', 'keywords' => ''],
            ['url' => 'invest/create', 'title' => 'Invest Create', 'description' => 'Invest Create', 'keywords' => ''],
            ['url' => 'invest/confirm', 'title' => 'Invest Confirm', 'description' => 'Invest Confirm', 'keywords' => ''],
            ['url' => 'investment/success', 'title' => 'Investment Success', 'description' => 'Investment Success', 'keywords' => ''],
            ['url' => 'investment-details/{id}', 'title' => 'Investment Detail', 'description' => 'Investment Detail', 'keywords' => ''],
            ['url' => 'invest/paypal-payment', 'title' => 'Investment Paypal Payment', 'description' => 'Investment Paypal Payment', 'keywords' => ''],
            ['url' => 'invest/stripe-payment', 'title' => 'Investment Stripe Payment', 'description' => 'Investment Stripe Payment', 'keywords' => ''],
        ];

        \App\Models\Meta::insert($metas);
    }
}
