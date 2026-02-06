<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class LogoutTest extends TestCase
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
        ->create();
    }

    public function test_user_logout_validation()
    {
        $user = User::first();
        // 認証されていない状態を確認
        $this->assertGuest();
        // 認証
        $this->actingAs($user);
        // 認証通過を期待
        $this->assertAuthenticated();
        // logoutボタンを押下
        $response = $this->post('/logout');
        //logoutを期待
        $this->assertGuest();
    }
}
