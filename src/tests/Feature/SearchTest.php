<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Support\Str;



class SearchTest extends TestCase
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
        $number_user = 2;
        $number_item = 3;
        User::factory($number_user)
        ->has(Item::factory()->count($number_item))
        ->create();
        Favorite::factory(round(($number_user-1)*$number_user*$number_item)/10+1)
        ->create();
    }

    public function test_part_searchable()
    {
        $items = Item::all();
        foreach ($items as $item){
            // itemのnameについて最初の一文字を取得
            $first_char = Str::substr($item->name, 0, 1);
             // 検索ボタンを押す
            $response = $this->post('/',['search'=>$first_char]);
            // 一部を抜き出して検索された商品が表示されている
            $response->assertSeeText($item->name);
            //検索にヒットする商品を取得
            $search_items = Item::NameSearch($first_char)->pluck('id');
            // itemsテーブルから検索にヒットする要素を排除したコレクションを取得
            $notsearch_items = Item::whereNotIn('id',$search_items)->get();
            // 検索にヒットしない要素が画面上に表示されていないことを確認
            foreach($notsearch_items as $notsearch_item){
                $response->assertDontSeeText($notsearch_item->name);
            }
        }
    }

    public function test_searchable_save_mylist()
    {
        // favoritesテーブルに存在するユーザーのidを取得
        $select_users = Favorite::distinct()->pluck('user_id')->toArray();
        // いいねを登録しているユーザーを抽出
        $users = User::whereIn('id',$select_users)->get();
        foreach($users as $user){
            // 認証
            $this->actingAs($user);
            // ホームページへアクセス
            $response = $this->get('/');
            // このユーザーがいいねしている商品のid全部を配列で取得
            $favorites = Favorite::where('user_id',$user['id'])->pluck('item_id')->toArray();
            // このユーザーのお気に入り商品全て
            $items = Item::whereIn('id',$favorites)->get();
            // 自分が出品した商品のid
            $my_items = Item::where('user_id',$user['id'])->pluck('id')->all();
            // 自分以外が出品した商品のid
            $others_items = Item::whereNotIn('id',$my_items)->pluck('id')->toArray();
            // このユーザーのお気に入り商品があることを前提にアイテムごとに検索する
            foreach ($items as $item){
                // itemのnameについて最初の一文字を取得(検索ワードとする)
                $first_char = Str::substr($item['name'], 0, 1);
                // ログイン後のホームページにおいて検索を押す
                $response = $this->post('/',['search'=>$first_char]);
                // 検索でヒットした商品を取得
                $search_items = Item::whereIn('id',$others_items)->NameSearch($first_char)->select('id','name')->get()->toArray();
                // ヒットした商品が全て確認できる
                foreach ($search_items as $search_item){
                    $response->assertSeeText($search_item['name']);
                }
                $searches = array_column($search_items,'id');
                // 検索でヒットした商品以外を取得
                $notsearch_items = Item::whereNotIn('id',$searches)->get();
                // 検索でヒットしなかった商品が画面に表示されていないことを確認
                foreach ($notsearch_items as $notsearch_item){
                    $response->assertDontSeeText($notsearch_item['name']);
                }
                // マイリストへ遷移
                $response = $this->post('/?tab=mylist',['search'=>$first_char]);
                // お気に入り商品のうち検索でヒットしたもの
                $favorite_searches = Item::whereIn('id',$items->pluck('id'))->whereIn('id',$others_items)->NameSearch($first_char)->select('id','name')->get()->toArray();
                // マイリストかつ検索ヒットの商品が表示されていることを期待
                foreach ($favorite_searches as $favorite_search){
                    $response->assertSeeText($favorite_search['name']);
                }
                // お気に入り商品のうち検索でヒットしたもの以外の全ての商品の名前の配列
                $notFavorites = Item::whereNotIn('id',array_column($favorite_searches,'id'))->pluck('name')->toArray();
                // お気に入り商品のうち検索でヒットしたもの以外の全ての商品が表示されないことを期待
                foreach($notFavorites as $notFavorite){
                    $response->assertDontSeeText($notFavorite);
                }
            }
            $response = $this->post('/logout');
        }
    }
}
