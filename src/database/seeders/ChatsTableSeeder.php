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
            'item_id' => 1,
            'chat' => '購入を検討しています、値下げ交渉は可能ですか',
            'pict' => 'img/sample/chat1.jpeg',
            'user_id' => 2,
        ];
        DB::table('chats')->insert($param);
        $param = [
            'item_id' => 1,
            'chat' => '即日現金払いであれば、1万3000円まで値引きします',
            'pict' => '',
            'user_id' => 1,
        ];
        DB::table('chats')->insert($param);
        $param = [
            'item_id' => 1,
            'chat' => '条件について承知しました。これにて購入させていただきます。',
            'pict' => '',
            'user_id' => 2,
        ];
        DB::table('chats')->insert($param);
        $param = [
            'item_id' => 2,
            'chat' => 'これはどのような商品ですか',
            'pict' => '',
            'user_id' => 3,
        ];
        DB::table('chats')->insert($param);
        $param = [
            'item_id' => 3,
            'chat' => 'これはどのような商品ですか',
            'pict' => '',
            'user_id' => 3,
        ];
        DB::table('chats')->insert($param);
        $param = [
            'item_id' => 3,
            'chat' => '完璧な商品です',
            'pict' => '',
            'user_id' => 1,
        ];
        DB::table('chats')->insert($param);
    }
}
