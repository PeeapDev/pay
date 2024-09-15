<?php

namespace Modules\Investment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class InvestmentDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(SettingsTableSeeder::class);
        $this->call(MetasTableSeeder::class);
        $this->call(TransactionTypesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(EmailTemplateTableSeeder::class);
        $this->call(NotificationTypesTableSeeder::class);
        $this->call(NotificationSettingsTableSeeder::class);
    }
}
