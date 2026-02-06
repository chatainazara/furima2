<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use App\Models\Buy;
use App\Models\Chat;
use App\Models\Evaluation;
use App\Mail\TransactionRatedMail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $userOverrides = [], array $profileOverrides = []): User
    {
        $user = User::create(array_merge([
            'name' => 'Test User',
            'email' => uniqid('user_') . '@sakamaki-forest.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ], $userOverrides));
        Profile::create(array_merge([
            'user_id'   => $user->id,
            'pict_url'  => 'storage/profile_dummy.png',
            'post_code' => '000-0000',
            'address'   => 'テスト住所',
            'building'  => null,
        ], $profileOverrides));
        return $user;
    }

    private function createBuy(
        ?User $buyer = null,
        bool $createEvaluation = false,
        ?User $seller = null
    ){
        $seller = $seller ?? $this->createUser(['name' => '売り手']);
        $buyer  = $buyer  ?? $this->createUser(['name' => '買い手']);
        $item = Item::create([
            'user_id' => $seller->id,
            'name' => 'Test Item',
            'pict_url' => 'storage/item_dummy.png',
            'brand_name' => 'Zenix',
            'price' => 1010,
            'detail' => 'スタイリッシュなテスト',
            'condition' => '良好',
        ]);
        $buy = Buy::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment' => 'card',
            'destination_post_code' => '000-0000',
            'destination_address' => '北海道あざら市',
            'destination_building' => 'ゼニガタハイツ303',
            'buyer_read' => null,
            'seller_read' => null,
        ]);
        $evaluation = null;
        if ($createEvaluation) {
            $evaluation = Evaluation::create([
                'buy_id' => $buy->id,
                'evaluate' => null,
                'evaluated' => null,
            ]);
        }
        return compact('seller', 'buyer', 'item', 'buy', 'evaluation');
    }


    private function createChat(Buy $buy, string $position, string $message = 'Hello'): Chat
        {
            return Chat::create([
                'buy_id' => $buy->id,
                'chat' => $message,
                'pict' => null,
                'position' => $position,
            ]);
        }

    private function createChats(
        Buy $buy,
        string $position,
        int $count = 1,
        string $baseMessage = 'Hello'
    ): array {
        $chats = [];
        for ($i = 1; $i <= $count; $i++) {
            $chats[] = $this->createChat(
                buy: $buy,
                position: $position,
                message: "{$baseMessage} {$i}"
            );
        }
        return $chats;
    }

    // FN001
    public function test_buyer_can_view_transaction_tab(): void
    {
        $data = $this->createBuy();
        $buyer = $data['buyer'];
        $item  = $data['item'];
        $buy   = $data['buy'];
        $number = random_int(1,10);
        $chat = $this->createChats($buy,"seller",$number);

        $response = $this->actingAs($buyer)->get('/mypage/transaction');
        $response->assertStatus(200);

        //マイページから取引中の商品を確認できる
        $response->assertSee($item->name);
        // 未読チャット数の確認
        $response->assertSee('<span class="badge">'.$number.'</span>', false);
    }

    // FN002
    public function test_buyer_can_view_transaction(): void
    {
        $data = $this->createBuy();
        $buyer = $data['buyer'];
        $item  = $data['item'];
        $buy   = $data['buy'];
        $number = random_int(1,10);
        $chat = $this->createChats($buy,"seller",$number);

        $response = $this->actingAs($buyer)->get('/mypage/transaction');
        $response->assertStatus(200);
        $response = $this->get('/transaction/'.$item->id);
        // 取引チャット画面への遷移を確認
        $response->assertSee("「売り手」さんとの取引画面");
    }

    // FN003
    public function test_buyer_can_view_transaction_side(): void
    {
        $data = $this->createBuy();
        $buyer  = $data['buyer'];
        $seller = $data['seller'];
        $item1  = $data['item'];
        // 同じbuyer・同じsellerで、別商品と別buyを追加
        $item2 = Item::create([
            'user_id' => $seller->id,
            'name' => 'Test Item 2',
            'pict_url' => 'storage/item_dummy.png',
            'brand_name' => 'Zenix',
            'price' => 2020,
            'detail' => '別商品',
            'condition' => '良好',
        ]);
        $buy2 = Buy::create([
            'user_id' => $buyer->id,      // 同じbuyer
            'item_id' => $item2->id,
            'payment' => 'card',
            'destination_post_code' => '000-0000',
            'destination_address' => '北海道あざら市',
            'destination_building' => 'ゼニガタハイツ303',
            'buyer_read' => null,
            'seller_read' => null,
        ]);
        $response = $this->actingAs($buyer)->get("/transaction/{$item1->id}");
        $response->assertStatus(200);
        // サイドバーに別取引が出る
        $response->assertSeeText('Test Item 2');
    }

    // FN004
    public function test_transactions_sorted(): void
    {
        $buyer = $this->createUser(['name' => '買い手']);
        $data1 = $this->createBuy($buyer);
        $data2 = $this->createBuy($buyer);
        // 取引Aのメッセージ（古い）
        $this->createChat($data1['buy'], 'seller', 'old message');
        sleep(1); // 時間差つける
        // 取引Bのメッセージ（新しい）
        $this->createChat($data2['buy'], 'seller', 'new message');
        $response = $this->actingAs($buyer)->get('/mypage/transaction/');
        $items = $response->viewData('items');
        //　最新のものが最初に来てることを確認
        $this->assertEquals(
            $data2['item']->id,
            $items->first()->id
        );
    }

    // FN005　取引商品新規通知確認機能
    public function test_unread_badge_counts(): void
    {
        $buyer = $this->createUser(['name' => '買い手']);
        $data = $this->createBuy($buyer);
        $buy  = $data['buy'];
        $item = $data['item'];
        // sellerが2件送信（buyer未読）
        $this->createChat($buy, 'seller', 'msg1');
        $this->createChat($buy, 'seller', 'msg2');
        // マイページの取引中の商品タブ
        $response = $this->actingAs($buyer)->get('/mypage/transaction/');
        $response->assertSuccessful();
        // 変数が渡っているか
        $response->assertViewHas('unreadCountByItem');
        $unreadByItem = $response->viewData('unreadCountByItem');
        // item単位で2件未読
        $this->assertSame(2, (int) ($unreadByItem[$item->id] ?? 0));
        // 取引画面を開く、既読ロジックが発火
        $this->actingAs($buyer)->get('/transaction/' . $item->id)->assertStatus(200);
        // もう一度マイページの取引中の商品タブ
        $response = $this->actingAs($buyer)->get('/mypage/transaction/');
        $response->assertSuccessful();
        $unreadByItem = $response->viewData('unreadCountByItem');
        $this->assertSame(0,  ($unreadByItem[$item->id] ?? 0));
    }

    // FN005　評価平均確認機能
    public function test_evaluation_view():void
    {
        $buyer  = $this->createUser(['name' => '買い手']);
        $seller = $this->createUser(['name' => '売り手']);
        $data1 = $this->createBuy($buyer, true, $seller);
        $data2 = $this->createBuy($buyer, true, $seller);
        $point1 = random_int(1,10);
        $point2 = random_int(1,10);
        $point3 = random_int(1,10);
        $point4 = random_int(1,10);
        $evaluation1 = $data1['evaluation'];
        $evaluation1->update([
            'evaluate' => $point1,
            'evaluated' => $point2,
        ]);
        $evaluation2 = $data2['evaluation'];
        $evaluation2->update([
            'evaluate' => $point3,
            'evaluated' => $point4,
        ]);
        // 売り手評価が表示されていることを確認
        $testAveRating = ($point1 + $point3)/2;
        $response = $this -> actingAs($seller) ->get('/mypage/transaction');
        $response -> assertStatus(200);
        $response -> assertViewHas('avgRating',$testAveRating);
        // 買い手評価が表示されていることを確認
        $testAveRating = ($point2 + $point4)/2;
        $response = $this -> actingAs($buyer) ->get('/mypage/transaction');
        $response -> assertStatus(200);
        $response -> assertViewHas('avgRating',$testAveRating);
    }

    // FN006
    public function test_can_post_chat(): void
    {
        Storage::fake('public');
        $data = $this->createBuy();
        $buyer = $data['buyer'];
        $seller = $data['seller'];
        $buy   = $data['buy'];
        // 買い手の投稿
        $file1 = UploadedFile::fake()->image('dummy.png');
        $response = $this->actingAs($buyer)->post('/transaction/store', [
            'buyId' => $buy->id,
            'position' => 'buyer',
            'pict' => $file1,
            'chat' => 'test message',
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('chats', [
            'buy_id' => $buy->id,
            'position' => 'buyer',
            'chat' => 'test message',
        ]);
        $chatBuyer = \App\Models\Chat::where('buy_id', $buy->id)->latest()->first();
        $this->assertNotNull($chatBuyer->pict);
        // 売り手の投稿
        $file2 = UploadedFile::fake()->image('dummy.png');
        $response = $this->actingAs($seller)->post('/transaction/store', [
            'buyId' => $buy->id,
            'position' => 'seller',
            'pict' => $file2,
            'chat' => 'test message sell',
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('chats', [
            'buy_id' => $buy->id,
            'position' => 'seller',
            'chat' => 'test message sell',
        ]);
        $chatSeller = \App\Models\Chat::where('buy_id', $buy->id)->latest()->first();
        $this->assertNotNull($chatSeller->pict);
    }

    // FN007 FN008
    public function test_chat_store_validation(): void
    {
    // 本文入力なし
        $data  = $this->createBuy();
        $buyer = $data['buyer'];
        $buy   = $data['buy'];
        $response = $this->actingAs($buyer)->post('/transaction/store', [
            'buyId' => $buy->id,
            'position' => 'buyer',
            'chat' => '',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'chat' => '本文を入力してください',
        ]);
    // 400文字越え
        $data2  = $this->createBuy();
        $buyer2 = $data2['buyer'];
        $buy2   = $data2['buy'];
        $over = str_repeat('a', 401);
        $response = $this->actingAs($buyer2)->post('/transaction/store', [
            'buyId' => $buy2->id,
            'position' => 'buyer',
            'chat' => $over,
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'chat' => '本文は400文字以内で入力してください',
        ]);
        // 画像形式間違い
        Storage::fake('public');
        $data3  = $this->createBuy();
        $buyer3 = $data3['buyer'];
        $buy3   = $data3['buy'];
        $badFile = UploadedFile::fake()->create('bad.gif', 10, 'image/gif');
        $response = $this->actingAs($buyer3)->post('/transaction/store', [
            'buyId' => $buy3->id,
            'position' => 'buyer',
            'chat' => '本文あり',
            'pict' => $badFile,
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'pict' => '「.png」または「.jpeg」形式でアップロードしてください',
        ]);
    }

    // FN009
    public function test_chat_text_preserved(): void
    {
        $data  = $this->createBuy();
        $buyer = $data['buyer'];
        $item  = $data['item'];
        $buy   = $data['buy'];
        // 保存前の画面遷移をおこすためエラーを起こす準備
        $badFile = UploadedFile::fake()->create('bad.gif', 10, 'image/gif');
        // 保存する前に跳ね返る想定
        $this->actingAs($buyer)->post('/transaction/store', [
            'buyId' => $buy->id,
            'position' => 'buyer',
            'chat' => '残ってほしい本文',
            'pict' => $badFile,
        ])->assertStatus(302)->assertSessionHasErrors(['pict']);
        $response = $this->actingAs($buyer)->get('/transaction/' . $item->id);
        $response->assertStatus(200);
        $response->assertSee('残ってほしい本文');
    }

    // FN010
    public function test_user_can_edit(): void
    {
        $data = $this->createBuy();
        $buyer = $data['buyer'];
        $item  = $data['item'];
        $buy   = $data['buy'];
        $chat = $this->createChat($buy, 'buyer', 'before');
        $response = $this->actingAs($buyer)->post('/transaction/update/' . $chat->id, [
            'chat' => 'after',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/transaction/' . $item->id);
        $this->assertDatabaseHas('chats', [
            'id' => $chat->id,
            'chat' => 'after',
        ]);
    }

    // FN011
    public function test_user_can_delete(): void
    {
        $data = $this->createBuy();
        $buyer = $data['buyer'];
        $buy   = $data['buy'];
        $chat = $this->createChat($buy, 'buyer', 'to be deleted');
        $response = $this->actingAs($buyer)->delete('/transaction/delete/' . $chat->id, [
            'chat' => $chat->id,
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseMissing('chats', ['id' => $chat->id]);
    }

    // FN012 FN015 FN016
    public function test_buyer_can_rating_and_send_mail(): void
    {
        Mail::fake();
        // 評価が保存されるロジックの確認
        $data = $this->createBuy(null,false);
        $buyer  = $data['buyer'];
        $seller = $data['seller'];
        $buy    = $data['buy'];
        $response = $this->actingAs($buyer)->post('/evaluation/store/' . $buy->id, [
            'rating' => 5,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertDatabaseHas('evaluations', [
            'buy_id' => $buy->id,
            'evaluate' => 5,
        ]);
        // メールが送られることの確認
        Mail::assertSent(TransactionRatedMail::class, function (TransactionRatedMail $mail) use ($seller) {
            return collect($mail->to)->pluck('address')->contains($seller->email);
        });
    }

    // FN013 FN014
    public function test_seller_can_update_rating(): void
    {
        $data = $this->createBuy(null,false);
        $seller = $data['seller'];
        $buy    = $data['buy'];
        Evaluation::create([
            'buy_id' => $buy->id,
            'evaluate' => 4,
            'evaluated' => null,
        ]);
        $response = $this->actingAs($seller)->post('/evaluation/update/' . $buy->id, [
            'rating' => 3,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertDatabaseHas('evaluations', [
            'buy_id' => $buy->id,
            'evaluated' => 3,
        ]);
    }
}
