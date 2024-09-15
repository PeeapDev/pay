<?php

namespace Modules\Investment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class NotificationSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $notificationType = \App\Models\NotificationType::where('name', 'Investment')->first(['id']);

        \App\Models\NotificationSetting::insert([
            ['notification_type_id' => $notificationType->id, 'recipient_type' => 'email', 'recipient' => null, 'status' => 'No']
        ]);
    }
}
