<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;
use Symfony\Component\DomCrawler\Crawler;
use Database\Seeders\CategoriesTableSeeder;

class CommentTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriesTableSeeder::class);
        User::factory(2)
        ->has(Item::factory()
            ->count(1)
        )
        ->hasProfile()
        ->create();
    }

    public function test_comment_login_user_submit()
    {
        $users = User::all();
        foreach ($users as $user){
            // ログイン
            $this->actingAs($user);
            // 全アイテムを取得
            $items = Item::all();
            // アイテムごと検証
            foreach($items as $item){
                $response = $this->get('/item/'.$item['id']);
                //コメントを入力しボタンを押す
                $response = $this->post('/item/'.$item['id'],[
                    'action' => 'comment',
                    'content' => 'これはテストです'
                ]);
                // 保存されていることの確認
                $this->assertDatabaseHas('comments',[
                    'item_id' => $item['id'],
                    'user_id' => $user['id'],
                    'content' => 'これはテストです'
                ]);
                // htmlを取得
                $html = $response->getContent();
                // 階層構造化
                $crawler = new Crawler($html);
                // コメントブロックを作成（コメントも同様のブロックだが本テストではコメントは0）
                $comment_block = $crawler->filter('.content__react-count')
                    ->each(fn($crawler) => intval($crawler->text()));
                $count = Comment::where('item_id',$item['id'])->count();
                // コメントの数が反映されることを期待
                $this->assertContains($count, $comment_block);
            }
        // ログアウト
        $response = $this->post('/logout');
        }
    }

    public function test_comment_logout_user_submit()
    {
        // 全アイテムを取得
        $items = Item::all();
        // アイテムごと検証
        foreach($items as $item){
            // 商品詳細ページにアクセス
            $response = $this->get('/item/'.$item['id']);
            //コメントを送信する
            $response = $this->post('/item/'.$item['id'],[
                'action' => 'comment',
                'content' => 'これはテストです'
            ]);
            // 保存されていないことの確認
            $this->assertDatabaseMissing('comments',[
                'content' => 'これはテストです'
            ]);
        }
    }

    public function test_comment_login_user_validation_null()
    {
        $users = User::all();
        foreach ($users as $user){
            // 認証
            $this->actingAs($user);
            // 全アイテムを取得
            $items = Item::all();
            // アイテムごと検証
            foreach($items as $item){
                $response = $this->get('/item/'.$item['id']);
                //コメントを送信する
                $response = $this->post('/item/'.$item['id'],[
                    'action' => 'comment',
                    'content' => ''
                ]);
                $response->assertSessionHasErrors([
                    'content' => 'コメントを入力してください',
                ]);
            }
        // ログアウト
        $response = $this->post('/logout');
        }
    }

public function test_comment_login_user_validation_255()
    {
        $users = User::all();
        foreach ($users as $user){
            // 認証
            $this->actingAs($user);
            // 全アイテムを取得
            $items = Item::all();
            // アイテムごと検証
            foreach($items as $item){
                $response = $this->get('/item/'.$item['id']);
                //コメントを送信する
                $response = $this->post('/item/'.$item['id'],[
                    'action' => 'comment',
                    // 以下256文字
                    'content' => '
                    01テストテストテストテスト-17
                    02テストテストテストテスト-17
                    03テストテストテストテスト-17
                    04テストテストテストテスト-17
                    05テストテストテストテスト-17
                    06テストテストテストテスト-17
                    07テストテストテストテスト-17
                    08テストテストテストテスト-17
                    09テストテストテストテスト-17
                    10テストテストテストテスト-17
                    11テストテストテストテスト-17
                    12テストテストテストテスト-17
                    13テストテストテストテスト-17
                    14テストテストテストテスト-17
                    15テストテストテストテスト-17a'
                ]);
                $response->assertSessionHasErrors([
                    'content' => 'コメントは255文字以内で入力してください',
                ]);
            }
        // ログアウト
        $response = $this->post('/logout');
        }
    }
}
