<?php

namespace Modules\Investment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
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
            ['group' => 'Investment Plan', 'name' => 'view_investment_plan', 'display_name' => 'View Investment Plan', 'description' => 'View Investment Plan', 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['group' => 'Investment Plan', 'name' => 'add_investment_plan', 'display_name' => 'Add Investment Plan', 'description' => 'Add Investment Plan', 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['group' => 'Investment Plan', 'name' => 'edit_investment_plan', 'display_name' => 'Edit Investment Plan', 'description' => 'Edit Investment Plan', 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['group' => 'Investment Plan', 'name' => 'delete_investment_plan', 'display_name' => 'Delete Investment Plan', 'description' => 'Delete Investment Plan', 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            ['group' => 'Investment', 'name' => 'view_investment', 'display_name' => 'View Investment', 'description' => 'View Investment', 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['group' => 'Investment', 'name' => 'add_investment', 'display_name' => null, 'description' => null, 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['group' => 'Investment', 'name' => 'edit_investment', 'display_name' => 'Edit Investment', 'description' => 'Edit Investment', 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['group' => 'Investment', 'name' => 'delete_investment', 'display_name' => null, 'description' => null, 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            
            ['group' => 'Investment Setting', 'name' => 'view_investment_setting', 'display_name' => 'View Investment Setting', 'description' => 'View Investment Setting', 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['group' => 'Investment Setting', 'name' => 'add_investment_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['group' => 'Investment Setting', 'name' => 'edit_investment_setting', 'display_name' => 'Edit Investment Setting', 'description' => 'Edit Investment Setting', 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['group' => 'Investment Setting', 'name' => 'delete_investment_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            
            ['group' => 'Investment Profit Approve', 'name' => 'view_profit_approve', 'display_name' => 'View Profit Approve', 'description' => 'view_profit_approve', 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')], 
            ['group' => 'Investment Profit Approve', 'name' => 'add_profit_approve', 'display_name' => null, 'description' => null, 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')], 
            ['group' => 'Investment Profit Approve', 'name' => 'edit_profit_approve', 'display_name' => null, 'description' => null, 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')], 
            ['group' => 'Investment Profit Approve', 'name' => 'delete_profit_approve', 'display_name' => null, 'description' => null, 'user_type' => 'Admin', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],

            ['group' => 'Investment', 'name' => 'manage_investment', 'display_name' => 'Investment', 'description' => 'Investment', 'user_type' => 'User', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ];

        \App\Models\Permission::insert($permissions);

        $adminPermissions = \App\Models\Permission::whereIn('group', [ 'Investment Plan','Investment Profit Approve', 'Investment', 'Investment Setting'])->where('user_type', 'Admin')->get(['id', 'display_name']);

        foreach ($adminPermissions as $value) {
            if ($value->display_name == null) continue;
            $roleData[] = [
                'role_id' => 1,
                'permission_id' => $value->id,
            ];
        }

        $investmentUserPermissions = \App\Models\Permission::where(['group' => 'Investment', 'user_type' => 'User'])->get(['id']);

        foreach ($investmentUserPermissions as $value) {
            $roleData[] = [
                'role_id' => 2,
                'permission_id' => $value->id,
            ];
            $roleData[] = [
                'role_id' => 3,
                'permission_id' => $value->id,
            ];
        }
        
        DB::table('permission_role')->insert($roleData);
    }
}
