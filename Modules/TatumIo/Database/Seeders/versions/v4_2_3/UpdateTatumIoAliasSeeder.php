<?php

namespace Modules\TatumIo\Database\Seeders\versions\v4_2_3;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateTatumIoAliasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('crypto_providers')
            ->where('alias', 'TatumIo')
            ->update(['alias' => strtolower('TatumIo')]);
    }
}
