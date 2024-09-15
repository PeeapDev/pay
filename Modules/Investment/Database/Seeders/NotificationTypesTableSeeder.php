<?php

namespace Modules\Investment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class NotificationTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        \App\Models\NotificationType::insert([
            ['name' => 'Investment', 'alias' => 'investment', 'status' => 'Active']
        ]);
    }
}
