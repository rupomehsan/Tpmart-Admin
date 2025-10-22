<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Call the AccountGroupsSeeder
        $this->call([
            AccountGroupsSeeder::class,
        ]);
        
        // Keep existing ac_accounts data
        DB::table("ac_accounts")->insert([
            'account_name' => 'asset',
            'account_code' => '1',
        ]);
        DB::table("ac_accounts")->insert([
            'account_name' => 'liability',
            'account_code' => '2',
        ]);
        DB::table("ac_accounts")->insert([
            'account_name' => 'owner_equity',
            'account_code' => '3',
        ]);
        DB::table("ac_accounts")->insert([
            'account_name' => 'revenue',
            'account_code' => '4',
        ]);
        DB::table("ac_accounts")->insert([
            'account_name' => 'expense',
            'account_code' => '5',
        ]);
    }
}
