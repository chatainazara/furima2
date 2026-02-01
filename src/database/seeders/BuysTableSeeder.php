<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'item_id' => 5,
            'user_id' => 3,
            'payment' => 'card',
            'destination_post_code' => '111-1111',
            'destination_address' => '北海道猿払市0-0-0',
            'destination_building' => 'オウレンビル202',
        ];
        DB::table('buys')->insert($param);
        $param = [
            'item_id' => 4,
            'user_id' => 2,
            'payment' => 'konbini',
            'destination_post_code' => '222-2222',
            'destination_address' => '北海道浜頓別村0-0-0',
            'destination_building' => 'リキッダビルディング1202',
        ];
        DB::table('buys')->insert($param);
    }
}
