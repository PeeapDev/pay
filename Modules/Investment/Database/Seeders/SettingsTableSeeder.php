<?php

namespace Modules\Investment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        \App\Models\Setting::insert([
            ['name' => 'schema_display', 'value' => NULL, 'type' => 'investment'],
            ['name' => 'plan_description', 'value' => NULL, 'type' => 'investment'],
            ['name' => 'kyc', 'value' => NULL, 'type' => 'investment'],
            ['name' => 'invest_start_on_admin_approval', 'value' => NULL, 'type' => 'investment'],
            ['name' => 'admin_investment_plan_view', 'value' => 'List', 'type' => 'investment'],
        ]);

    }
}
