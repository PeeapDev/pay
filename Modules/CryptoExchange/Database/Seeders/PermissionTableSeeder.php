<?php

namespace Modules\CryptoExchange\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $permissions = [ 

            ['group' => 'Crypto Direction', 'name' => 'view_crypto_direction', 'display_name' => 'View Crypto Direction', 'description' => 'View Crypto Direction', 'user_type' => 'Admin'],
            ['group' => 'Crypto Direction', 'name' => 'add_crypto_direction', 'display_name' => 'Add Crypto Direction', 'description' => 'Add Crypto Direction', 'user_type' => 'Admin'],
            ['group' => 'Crypto Direction', 'name' => 'edit_crypto_direction', 'display_name' => 'Edit Crypto Direction', 'description' => 'Edit Crypto Direction', 'user_type' => 'Admin'],
            ['group' => 'Crypto Direction', 'name' => 'delete_crypto_direction', 'display_name' => 'Delete Crypto Direction', 'description' => 'Delete Crypto Direction', 'user_type' => 'Admin'],

            ['group' => 'Crypto Exchange Transaction', 'name' => 'view_crypto_exchange_transaction', 'display_name' => 'View Crypto Exchange Transaction', 'description' => 'View Crypto Exchange Transaction', 'user_type' => 'Admin'],
            ['group' => 'Crypto Exchange Transaction', 'name' => 'add_crypto_exchange_transaction', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['group' => 'Crypto Exchange Transaction', 'name' => 'edit_crypto_exchange_transaction', 'display_name' => 'Edit Crypto Exchange Transaction', 'description' => 'Edit Crypto Exchange Transaction', 'user_type' => 'Admin'],
            ['group' => 'Crypto Exchange Transaction', 'name' => 'delete_crypto_exchange_transaction', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            ['group' => 'Crypto Exchange Settings', 'name' => 'view_crypto_exchange_settings', 'display_name' => 'View Crypto Exchange Settings', 'description' => 'View Crypto Exchange Settings', 'user_type' => 'Admin'],
            ['group' => 'Crypto Exchange Settings', 'name' => 'add_crypto_exchange_settings', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['group' => 'Crypto Exchange Settings', 'name' => 'edit_crypto_exchange_settings', 'display_name' => 'Edit Crypto Exchange Settings', 'description' => 'Edit Crypto Exchange Settings', 'user_type' => 'Admin'],
            ['group' => 'Crypto Exchange Settings', 'name' => 'delete_crypto_exchange_settings', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],


            ['group' => 'Crypto Exchange', 'name' => 'manage_crypto_exchange', 'display_name' => 'Crypto Exchange', 'description' => 'Crypto Exchange', 'user_type' => 'User'],
            
        ];

        \App\Models\Permission::insert($permissions);
    }
}
