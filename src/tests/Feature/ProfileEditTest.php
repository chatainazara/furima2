<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use App\Models\Profile;

class ProfileEditTest extends TestCase
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
        ->create();
    }

    public function test_old_value_visible()
    {
        $users = User::all();
        // ユーザー一人ずつ検証
        foreach($users as $user){
            // ログイン
            $this->actingAs($user);
            // プロフィール編集画面にアクセス
            $response = $this->get('/mypage/profile');
            // 既存のプロフィール
            $profile = Profile::where('user_id',$user->id)->first();
            // 以下が表示されていることを確認
            $response->assertSee($profile->pict_url);
            $response->assertSee($user->name);
            $response->assertSee($profile->post_code);
            $response->assertSee($profile->address);
            // ログアウト
            $response = $this->post('/logout');
        }
    }
}
