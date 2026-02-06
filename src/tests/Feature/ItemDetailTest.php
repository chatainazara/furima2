<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Comment;
use Symfony\Component\DomCrawler\Crawler;
use Database\Seeders\CategoriesTableSeeder;

class ItemDetailTest extends TestCase
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
        $count=Category::count();
        User::factory(2)
        ->has(Item::factory()
            ->count(1)
        )
        ->hasProfile()
        ->hasComments(1)
        ->create();

        Favorite::factory(1)
        ->create();
    }

    public function test_all_item_visible()
    {
        $items = Item::with('categories')->get();
        foreach($items as $item){
            // 商品詳細ページを開く
            $response = $this->get('/item/'.$item['id']);
            // htmlを取得
            $html = $response->getContent();
            // 階層構造化
            $crawler = new Crawler($html);
            // 商品写真のURLが存在することを確認
            $response->assertSee([
                $item['pict_url'],
            ]);
            // テスト条件の各因子が存在するかを確認
            $response->assertSeeText([
                // 商品名
                $item['name'],
                // ブランド名
                $item['brand_name'],
                // 価格
                number_format($item['price'],0),
                // いいね数
                Favorite::where('item_id',$item['id'])->count(),
                // コメント数
                Comment::where('item_id',$item['id'])->count(),
                // 商品説明
                $item['detail'],
                // 商品の状態
                //カテゴリーは次のテストケースで確認
                $item['condition'],
                // コメントタイトルにつくコメント数
                'コメント('.Comment::where('item_id',$item['id'])->count().')',
            ]);
            // コメントユーザーのプロフィール画像、ユーザ名、コメント内容
            $comments = Comment::with('user.profile')
                ->where('item_id', $item['id'])
                ->get();
            foreach($comments as $comment){
                $response->assertSeeText([
                    // コメントした人の名前
                    $comment->user->name,
                    // コメント
                    $comment->content,
                ]);
                // 写真が表示されていることの確認
                $response->assertSee($comment->user->profile->pict_url);
            }
            // この商品にコメントしているユーザーを抽出
            $comment_users = Comment::where('item_id',$item['id'])
                ->select('user_id')
                ->distinct()
                ->get()
                ->toArray();
            // コメント全体から上記のユーザーを除外したコメントを取得
            $not_comments = Comment::with('user.profile')
            ->whereNotIn('user_id',$comment_users)
            ->get();
            foreach($not_comments as $not_comment){
                $response->assertDontSeeText([
                    // コメントした人の名前が表示されていない
                    $not_comment->user->name,
                    // コメント自体は同一コメントになる可能性を否定できないのでテストしない
                ]);
                // 写真が表示されていないことの確認
                $response->assertDontSee($not_comment->user->profile->pict_url);
            }
        }
    }

    public function test_all_item_category_visible()
    {
        $items = Item::with('categories')->get();
        foreach($items as $item){
            // 商品詳細ページを開く
            $response = $this->get('/item/'.$item['id']);
            // htmlを取得
            $html = $response->getContent();
            // 階層構造化
            $crawler = new Crawler($html);
            // 複数選択されたカテゴリーが存在することを確認
            $categories = $item->categories->pluck('content')->toArray();
            $response->assertSeeText($categories);
            // 選択されていないカテゴリーを抽出
            $category_all = Category::all()->pluck('content')->toArray();
            $diffs = array_diff($category_all,$categories);
            // カテゴリー部分をブロック化
            $category_block = $crawler->filter('.content__info--item')->text();
            // 選択されていないカテゴリーが存在しないことを確認
            foreach ($diffs as $diff) {
            $this->assertStringNotContainsString($diff, $category_block);
            }
        }
    }
}