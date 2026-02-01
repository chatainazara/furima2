<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => 'レンゲ',
            'email' => 'renge@sakamaki-forest.com',
            'password' => Hash::make('rengerenge'),
            'email_verified_at' => now(),
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => 'エンジュ',
            'email' => 'enju@sakamaki-forest.com',
            'password' => Hash::make('enjuenju'),
            'email_verified_at' => now(),
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => 'オウレン',
            'email' => 'ouren@sakamaki-forest.com',
            'password' => Hash::make('ourenouren'),
            'email_verified_at' => now(),
        ];
        DB::table('users')->insert($param);
    }
}
