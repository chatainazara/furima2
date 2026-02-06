<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;


class EmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_email_sent()
    {
        // ユーザーを作成
        $user = User::factory()->make();
        $formData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        // ユーザー登録（/register を POST）
        $this->post('/register', $formData)
            ->assertRedirect();
        // MailHog からメール取得
        $response = file_get_contents('http://mailhog:8025/api/v2/messages');
        $messages = json_decode($response, true);
        $this->assertNotEmpty($messages['items']);
    }

    public function test_verification_page_show()
    {
        // Guzzle用のモックハンドラを作成、ダミーのHTTPレスポンスを返すように設定
        $mock = new MockHandler([
            new Response(200, [], '{"items":[{"Content":{"Body":"Test email body"}}]}')
        ]);
        // 処理の流れを作成
        $handlerStack = HandlerStack::create($mock);
        // クライアントを作成
        $client = new Client(['handler' => $handlerStack]);
        // ユーザー登録データ
        $formData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        // 登録
        $this->post('/register', $formData)
            ->assertRedirect('/email/verify');
        // mailhog用クライアント作成
        $client = new Client([
            'base_uri' => 'http://mailhog:8025',
            'timeout'  => 5.0,
        ]);
        try {
            // MailHog のトップページにアクセス
            $response = $client->get('/');
            // ステータスコードが 200 であることを確認
            $this->assertEquals(200, $response->getStatusCode(), 'MailHogページにアクセスできません');
            // ページ本文に MailHog の文字列が含まれているか確認
            $body = (string) $response->getBody();
            $this->assertStringContainsString('MailHog', $body, 'MailHogの画面が表示されていません');
        } catch (RequestException $e) {
            $this->fail('MailHogへのアクセスに失敗しました: ' . $e->getMessage());
        }
    }

    public function test_verification_finish()
    {
        // ユーザーを作成
        $user = User::factory()->make();
        $formData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        // ユーザー登録（/register を POST）
        $this->post('/register', $formData)
            ->assertRedirect();
        // MailHog からメール取得
        $response = file_get_contents('http://mailhog:8025/api/v2/messages');
        $messages = json_decode($response, true);
        $mail = $messages['items'][0];
        // メール本文をデコード
        $body = quoted_printable_decode($mail['Content']['Body']);
        // 認証URLを抽出
        preg_match('/http:\/\/localhost\/email\/verify\/\d+\/[a-f0-9]+\?expires=\d+&signature=[a-f0-9]+/', $body, $matches);
        $verificationUrl = $matches[0] ?? null;
        // 認証リンクを踏む
        $user = User::where('email', $formData['email'])->first();
        $this->actingAs($user)
            ->get(parse_url($verificationUrl, PHP_URL_PATH) . '?' . parse_url($verificationUrl, PHP_URL_QUERY))
            ->assertRedirect('/mypage/profile');
        // メール認証が完了したことを確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
