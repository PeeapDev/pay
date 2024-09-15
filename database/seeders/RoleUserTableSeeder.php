<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RoleUser;
use Illuminate\Database\Seeder;

class RoleUserTableSeeder extends Seeder
{
    public function run()
    {
        RoleUser::create([
            'user_id' => 1,
            'role_id' => 1,
            'user_type' => 'Admin',
        ]);

        if (app()->runningInConsole()) {

            $users = User::get();

            $roleUsers = $users->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'role_id' => $user->role_id,
                    'user_type' => ucfirst($user->type),
                ];
            })->toArray();

            RoleUser::insert($roleUsers);
        }
    }
}
