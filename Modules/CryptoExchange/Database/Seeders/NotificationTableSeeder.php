<?php

namespace Modules\CryptoExchange\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class NotificationTableSeeder extends Seeder
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
            [
                'name'       => 'Crypto Exchange',
                'alias'      => 'crypto-exchange',
                'status'     => 'Active',
            ]
        ]);

        $notificationType = \App\Models\NotificationType::where('name', 'Crypto Exchange')->value('id');

        \App\Models\NotificationSetting::insert([
            [
                'notification_type_id' => $notificationType,
                'recipient_type'       => 'email',
                'recipient'            => NULL,
                'status'               => 'No',
            ],
            [
                'notification_type_id' => $notificationType,
                'recipient_type'       => 'sms',
                'recipient'            => NULL,
                'status'               => 'No',
            ],
        ]);

    }
}
