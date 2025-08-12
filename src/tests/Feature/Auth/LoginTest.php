<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力のときはバリデーションエラーになる()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function パスワードが未入力のときはバリデーションエラーになる()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function 未登録情報を入力した時はバリデーションエラーになる()
    {
        // テスト用ユーザー作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        $response = $this->from('/login')->post('/login', [
            'email' => 'test2@example.com',
            'password' => '1212',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['user']);
    }

    /** @test */
    public function 入力が正しければログインに成功して商品一覧ページに遷移する()
    {
        // テスト用ユーザー作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // ログイン試行
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }
}