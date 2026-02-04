<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'buy_id' => 1,
            'chat' => '購入しました、郵送をお願いします',
            'pict' => '',
            'position' => 'buyer'
        ];
        DB::table('chats')->insert($param);
        $param = [
            'buy_id' => 1,
            'chat' => '繁忙期につき、郵送が明日になりますが大丈夫でしょうか',
            'pict' => '',
            'position' => 'seller'
        ];
        DB::table('chats')->insert($param);
        $param = [
            'buy_id' => 1,
            'chat' => '承知しました、お待ちしております',
            'pict' => '',
            'position' => 'buyer'
        ];
        DB::table('chats')->insert($param);
        $param = [
            'buy_id' => 1,
            'chat' => '本日郵送しました',
            'pict' => '',
            'position' => 'seller'
        ];
        DB::table('chats')->insert($param);
        $param = [
            'buy_id' => 1,
            'chat' => '到着しました、綺麗に郵送いただきありがとうございました',
            'pict' => '',
            'position' => 'buyer'
        ];
        DB::table('chats')->insert($param);
        $param = [
            'buy_id' => 2,
            'chat' => '郵送をお願いします',
            'pict' => '',
            'position' => 'buyer'
        ];
        DB::table('chats')->insert($param);
        $param = [
            'buy_id' => 2,
            'chat' => '本日発送いたします',
            'pict' => '',
            'position' => 'seller'
        ];
        DB::table('chats')->insert($param);
        $param = [
            'buy_id' => 2,
            'chat' => '到着を楽しみにお待ちします',
            'pict' => '',
            'position' => 'buyer'
        ];
        DB::table('chats')->insert($param);
        $param = [
            'buy_id' => 3,
            'chat' => 'お支払いいたしました、よろしくお願いします',
            'pict' => '',
            'position' => 'buyer'
        ];
        DB::table('chats')->insert($param);
        $param = [
            'buy_id' => 3,
            'chat' => '本日午後郵送予定です、少々お待ち下さい',
            'pict' => '',
            'position' => 'seller'
        ];
        DB::table('chats')->insert($param);
        $param = [
            'buy_id' => 3,
            'chat' => '早急にご対応いただきありがとうございます',
            'pict' => '',
            'position' => 'buyer'
        ];
        DB::table('chats')->insert($param);
    }
}
