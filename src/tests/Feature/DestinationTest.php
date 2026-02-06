<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Database\Seeders\CategoriesTableSeeder;
use Symfony\Component\DomCrawler\Crawler;
use Stripe\PaymentIntent;
use Mockery;

class DestinationTest extends TestCase
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
        // 初期値として14個のカテゴリーを作成
        $this->seed(CategoriesTableSeeder::class);
        // ユーザー・商品・商品のカテゴリーリレーション・プロフィールを作成登録
        User::factory(2)
        ->has(Item::factory()
            ->count(1)
        )
        ->hasProfile()
        ->create();
    }

    public function test_destination_change()
    {
        $users = User::all();
        foreach ($users as $user){
            // ログイン
            $this->actingAs($user);
            // 自分以外が出品した商品を取得
            $items = Item::where('user_id','!=',$user->id)->get();
            // アイテムごと検証
            foreach($items as $item){
                // 購入画面を開く
                $response = $this->get('/purchase/'.$item['id']);
                // 送付先住所変更画面で住所を登録して商品購入画面を再度開く
                $response = $this->post('/purchase/address/'.$item['id'],[
                    'destination_post_code' => '999-9999',
                    'destination_address' => 'test_address',
                    'destination_building' => 'test_building',
                    'payment' => 'card',
                ]);
                // htmlを取得
                $html = $response->getContent();
                // 階層構造化
                $crawler = new Crawler($html);
                // お届け先を部分をブロック化
                $destination_block = $crawler->filter('.destination__content')->text();
                //先ほど変更した住所がお届け先として表示されていることを確認
                $expecting = ['999-9999','test_address','test_building'];
                foreach ($expecting as $word){
                    $this->assertStringContainsString($word, $destination_block);
                }
            }
        // ログアウト
        $response = $this->post('/logout');
        }
    }


    public function test_destination_change_save()
    {
        $users = User::all();
        foreach ($users as $user){
            // ログイン
            $this->actingAs($user);
            // 自分以外が出品した商品を取得
            $items = Item::where('user_id','!=',$user->id)->get();
            // アイテムごと検証
            foreach($items as $item){
                // 購入画面を開く
                $response = $this->get('/purchase/'.$item['id']);
                // お届け先を変更し購入画面を再度開く
                $response = $this->post('/purchase/address/'.$item['id'],[
                    'destination_post_code' => '999-9999',
                    'destination_address' => 'test_address',
                    'destination_building' => 'test_building',
                    'payment' => 'card',
                ]);
                // Stripe static create をモック（カード払い用）
                $cardMock = Mockery::mock('overload:' . PaymentIntent::class);
                $cardMock->shouldReceive('create')
                        ->andReturn((object)['id' => 'pi_card_test_123']);
                // カード払い
                $this->actingAs($user)
                    ->withSession([
                        'user_id' => $user->id,
                        'item_id' => $item->id,
                        'payment' => 'card',
                        'destination_post_code' => '999-9999',
                        'destination_address' => 'test_address',
                        'destination_building' => 'test_building',
                    ]);
                // 商品を購入
                $response = $this->postJson('/payment/store', [
                    'payment_method' => 'pm_card_test_123',
                ]);
                // 正しく送付先住所が紐づいている
                $this->assertDatabaseHas('buys',[
                    'user_id' => $user->id,
                    'item_id' => $item->id,
                    'payment' => 'card',
                    'destination_post_code' => '999-9999',
                    'destination_address' => 'test_address',
                    'destination_building' => 'test_building',
                ]);
            }
        // ログアウト
        $response = $this->post('/logout');
        }
    }
}

