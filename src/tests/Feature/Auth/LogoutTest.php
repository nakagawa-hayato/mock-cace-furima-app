<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインしているユーザーがログアウトできる()
    {
        // 1. テストユーザー作成 & ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user); // ← ログイン状態にする

        // 2. /logout に POST でアクセス（フォーム送信を想定）
        $response = $this->post('/logout');

        // 3. ログアウトされてトップページへリダイレクト
        $response->assertRedirect('/');

        // 4. 認証状態が解除されていることを確認
        $this->assertGuest(); // ← 未ログイン状態
    }
}
