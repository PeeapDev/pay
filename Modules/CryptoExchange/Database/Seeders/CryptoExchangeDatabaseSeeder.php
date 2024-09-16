<?php

namespace Modules\CryptoExchange\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


class CryptoExchangeDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(TransactionTypeTableSeeder::class);
        $this->call(PreferenceTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);
        $this->call(MetasTableSeeder::class);
        $this->call(NotificationTableSeeder::class);
        $this->call(EmailTemplateTableSeeder::class);

    }
}
