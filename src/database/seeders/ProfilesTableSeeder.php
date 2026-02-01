<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_id' => 1,
            'pict_url' => 'img/sample/profile1.png',
            'post_code' => '000-0000',
            'address' => '北海道千歳町0-0-0',
            'building' => '蓮花ハイツC棟302',
        ];
        DB::table('profiles')->insert($param);
        $param = [
            'user_id' => 2,
            'pict_url' => '',
            'post_code' => '000-0000',
            'address' => '北海道由仁市0-0-0',
            'building' => 'グリーンエンジュA-202',
        ];
        DB::table('profiles')->insert($param);
        $param = [
            'user_id' => 3,
            'pict_url' => 'img/sample/profile3.png',
            'post_code' => '000-0000',
            'address' => '北海道神居町0-0-0',
            'building' => 'オウレン荘105',
        ];
        DB::table('profiles')->insert($param);
    }
}
