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
            'item_id' => 1,
            'user_id' => 2,
            'payment' => 'konbini',
            'destination_post_code' => '000-0000',
            'destination_address' => '北海道由仁市0-0-0',
            'destination_building' => 'グリーンエンジュA-202',
        ];
        DB::table('buys')->insert($param);
        $param = [
            'item_id' => 2,
            'user_id' => 3,
            'payment' => 'card',
            'destination_post_code' => '000-0000',
            'destination_address' => '北海道神居町0-0-0',
            'destination_building' => 'オウレン荘105',
        ];
        DB::table('buys')->insert($param);
        $param = [
            'item_id' => 3,
            'user_id' => 2,
            'payment' => 'konbini',
            'destination_post_code' => '000-0000',
            'destination_address' => '北海道由仁市0-0-0',
            'destination_building' => 'グリーンエンジュA-202',
        ];
        DB::table('buys')->insert($param);
        $param = [
            'item_id' => 6,
            'user_id' => 1,
            'payment' => 'konbini',
            'destination_post_code' => '000-0000',
            'destination_address' => '北海道千歳町0-0-0',
            'destination_building' => '蓮花ハイツC棟302',
        ];
        DB::table('buys')->insert($param);
        $param = [
            'item_id' => 7,
            'user_id' => 1,
            'payment' => 'konbini',
            'destination_post_code' => '000-0000',
            'destination_address' => '北海道千歳町0-0-0',
            'destination_building' => '蓮花ハイツC棟302',
        ];
        DB::table('buys')->insert($param);
    }
}
