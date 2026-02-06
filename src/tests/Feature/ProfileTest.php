<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Item;
use App\Models\User;
use App\Models\Profile;
use App\Models\Buy;


class ProfileTest extends TestCase
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
        ->has(Profile::factory())
        ->has(Item::factory()->count(1))
        ->create();

        Buy::factory(2)
        ->create();

    }

    public function test_profile_visible()
    {
        $profiles = Profile::all();
        $users = User::all();
        // ユーザー一人ずつ検証
        foreach($users as $user){
            // ログイン
            $this->actingAs($user);
            // プロフィール画面にアクセス
            $response = $this->get('/mypage');
            // プロフィール画面の表示を確認
            $response->assertViewIs('auth.profile');
            // ユーザーネームの表示を確認
            $response->assertSeeText($user['name']);
            // 画像の表示を確認
            $pict_url=Profile::find($user['id'])->pict_url;
            $response->assertSee($pict_url);
            // このユーザーが出品した商品が表示されているか
            $items = Item::where('user_id',$user['id'])->get();
            foreach($items as $item){
                $response->assertSeeText($item['name']);
            }
            // このユーザーが出品していない商品が表示されていないか
            $notitems=Item::whereNotIn('user_id',[$user['id']])->get();
            foreach($notitems as $notitem){
                $response->assertDontSeeText($notitem['name']);
            }
            // 購入した商品にアクセス
            $response = $this->post('/mypage?tab=buy');
            $response->assertViewIs('auth.profile');
            // 購入した商品が表示されることを確認
            $buys = Buy::where('user_id',$user['id'])->pluck('item_id')->toArray();
            $buy_items = Item::whereIn('id',$buys)->get();
            foreach($buy_items as $buy_item){
                $response->assertSeeText($buy_item['name']);
            }
            // 購入していない商品が表示されないことを確認
            $notbuy_items = Item::whereNotIn('id',$buys)->get();
            foreach($notbuy_items as $notbuy_item){
                $response->assertDontSeeText($notbuy_item['name']);
            }
            // ログアウト
            $response = $this->post('/logout');
        }
    }
}
