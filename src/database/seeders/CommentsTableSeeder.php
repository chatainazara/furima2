<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentsTableSeeder extends Seeder
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
            'content' => '魅力的な商品だと思います'
        ];
        DB::table('comments')->insert($param);
        $param = [
            'item_id' => 1,
            'user_id' => 3,
            'content' => '金額交渉は可能でしょうか'
        ];
        DB::table('comments')->insert($param);
        $param = [
            'item_id' => 1,
            'user_id' => 2,
            'content' => '金額交渉は可能でしょうか'
        ];
        DB::table('comments')->insert($param);
        $param = [
            'item_id' => 3,
            'user_id' => 1,
            'content' => '大きさはどれくらいですか'
        ];
        DB::table('comments')->insert($param);
        $param = [
            'item_id' => 6,
            'user_id' => 3,
            'content' => '注文後何日で出荷いただけますか'
        ];
        DB::table('comments')->insert($param);
        $param = [
            'item_id' => 6,
            'user_id' => 1,
            'content' => '4束に見えるのですが出品は3束ですか'
        ];
        DB::table('comments')->insert($param);
        $param = [
            'item_id' => 6,
            'user_id' => 2,
            'content' => '4束ですタイトルの間違えです'
        ];
        DB::table('comments')->insert($param);
    }
}
