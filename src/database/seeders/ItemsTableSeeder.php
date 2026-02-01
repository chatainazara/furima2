<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsTableSeeder extends Seeder
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
            'name' => '腕時計',
            'pict_url' => 'img/sample/item1.jpeg',
            'brand_name' => 'Rolax',
            'price' => 15000,
            'detail' => 'スタイリッシュなデザインのメンズ腕時計',
            'condition' => '良好',
        ];
        DB::table('items')->insert($param);
        $param = [
            'user_id' => 1,
            'name' => 'HDD',
            'pict_url' => 'img/sample/item2.jpeg',
            'brand_name' => '西芝',
            'price' => 5000,
            'detail' => '高速で信頼性の高いハードディスク',
            'condition' => '目立った傷や汚れなし',
        ];
        DB::table('items')->insert($param);
        $param = [
            'user_id' => 1,
            'name' => '玉ねぎ3束',
            'pict_url' => 'img/sample/item3.jpeg',
            'brand_name' => 'なし',
            'price' => 300,
            'detail' => '新鮮な玉ねぎ3束のセット',
            'condition' => 'やや傷や汚れあり',
        ];
        DB::table('items')->insert($param);
        $param = [
            'user_id' => 1,
            'name' => '革靴',
            'pict_url' => 'img/sample/item4.jpeg',
            'brand_name' => '',
            'price' => 4000,
            'detail' => 'クラシックなデザインの革靴',
            'condition' => '状態が悪い',
        ];
        DB::table('items')->insert($param);
        $param = [
            'user_id' => 1,
            'name' => 'ノートPC',
            'pict_url' => 'img/sample/item5.jpeg',
            'brand_name' => '',
            'price' => 45000,
            'detail' => '高性能なノートパソコン',
            'condition' => '良好',
        ];
        DB::table('items')->insert($param);
        $param = [
            'user_id' => 2,
            'name' => 'マイク',
            'pict_url' => 'img/sample/item6.jpeg',
            'brand_name' => 'なし',
            'price' => 8000,
            'detail' => '高音質のレコーディング用マイク',
            'condition' => '目立った傷や汚れなし',
        ];
        DB::table('items')->insert($param);
        $param = [
            'user_id' => 2,
            'name' => 'ショルダーバッグ',
            'pict_url' => 'img/sample/item7.jpeg',
            'brand_name' => '',
            'price' => 3500,
            'detail' => 'おしゃれなショルダーバッグ',
            'condition' => 'やや傷や汚れあり',
        ];
        DB::table('items')->insert($param);
        $param = [
            'user_id' => 2,
            'name' => 'タンブラー',
            'pict_url' => 'img/sample/item8.jpeg',
            'brand_name' => 'なし',
            'price' => 500,
            'detail' => '使いやすいタンブラー',
            'condition' => '状態が悪い',
        ];
        DB::table('items')->insert($param);
        $param = [
            'user_id' => 2,
            'name' => 'コーヒーミル',
            'pict_url' => 'img/sample/item9.jpeg',
            'brand_name' => 'Starbacks',
            'price' => 4000,
            'detail' => '手動のコーヒーミル',
            'condition' => '良好',
        ];
        DB::table('items')->insert($param);
        $param = [
            'user_id' => 2,
            'name' => 'メイクセット',
            'pict_url' => 'img/sample/item10.jpeg',
            'brand_name' => '',
            'price' => 2500,
            'detail' => '便利なメイクアップセット',
            'condition' => '目立った傷や汚れなし',
        ];
        DB::table('items')->insert($param);
    }
}
