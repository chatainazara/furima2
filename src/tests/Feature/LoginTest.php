<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LoginTest extends TestCase
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
        User::factory()
        ->hasProfile()
        ->create();
    }

    public function test_user_email_validation()
    {
        // ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response = $this->get('/no_route');
        $response->assertStatus(404);
        // メールアドレスを入力せずに他の必須項目を入力する
        $loginData = [
            'email' => '',
            'password' => 'password',
        ];
        // ボタンを押す
        $response = $this->post('/login', $loginData);
        // バリデーションを期待
        $response->assertSessionHasErrors('email');
        $this->assertEquals('メールアドレスを入力してください', session('errors')->first('email'));
    }

    public function test_user_password_validation()
    {
        // ログインページを開く
        $response = $this->get('/login');
        $user = User::first();
        // パスワードを入力せずに他の必須項目を入力する
        $loginData = [
            'email' => $user->email,
            'password' => '',
        ];
        // ボタンを押す
        $response = $this->post('/login', $loginData);
        // バリデーションを期待
        $response->assertSessionHasErrors('password');
        $this->assertEquals('パスワードを入力してください', session('errors')->first('password'));
    }

    public function test_incorrect_data_validation()
    {
        // ログインページを開く
        $response = $this->get('/login');

    // 登録されていないメールアドレスを入力し他の必須項目を入力する
        $user = User::first();
        $loginData = [
            'email' => 'not'.$user->email,
            'password' => 'password',
        ];
        // ボタンを押す
        $response = $this->post('/login', $loginData);
        // バリデーションを期待
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertEquals('ログイン情報が登録されていません', session('errors')->first('email'));

    // 登録されていないパスワードを入力し他の必須項目を入力する
        $loginData = [
            'email' => $user->email,
            'password' => 'notpassword',
        ];
        // ボタンを押す
        $response = $this->post('/login', $loginData);
        // バリデーションを期待
        $response->assertSessionHasErrors('password');
        $this->assertEquals('ログイン情報が登録されていません', session('errors')->first('password'));
    }

    public function test_user_can_login()
    {
        // ログインページを開く
        $response = $this->get('/login');
        $user = User::first();
        // 全ての必要項目を入力
        $loginData= [
            'email' => $user->email,
            'password' => 'password',
        ];
        // ログインボタンを押す
        $response = $this->post('/login',$loginData);
        // 認証通過を期待
        $this->assertAuthenticated();
        // indexページへの移行('/')を期待
        $response->assertRedirect('/');
    }
}

