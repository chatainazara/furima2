<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use App\Models\Item;
use App\Models\Buy;

class ItemTest extends TestCase
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
        User::factory(2)
        ->hasItems()
        ->create();

        Buy::factory(2)
        ->create();
    }

    public function test_all_item_visible()
    {
        $items = Item::All();
        // 商品一覧ページの表示
        $response = $this->get('/');
        // 全ての商品名が表示されていることを確認
        foreach ($items as $item) {
            $response->assertSeeText($item->name);
        }
    }

    public function test_sold_label_visible()
    {
        // 商品一覧ページの表示
        $response = $this->get('/');
        // 購入された商品にsoldが表示される
        $response->assertSeeText('sold');
    }

    public function test_sold_item_unvisible()
    {
        $users = User::all();
        foreach ($users as $user){
            // ログイン
            $this->actingAs($user);
            // 商品一覧ページの表示
            $response = $this->get('/');
            // $userの出品したもののid取得
            $removeItems = Item::where('user_id',Auth::id())->get();
            // 自分が出品したものが画面上に出ていないかを確認
            foreach ($removeItems as $item) {
                $response->assertDontSeeText($item->name);
            }
            // ログアウト
            $response = $this->post('/logout');
        }
    }

}
