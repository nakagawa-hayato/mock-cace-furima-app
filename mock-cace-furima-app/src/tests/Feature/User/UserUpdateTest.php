<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function プロフィール編集ページに初期情報が表示される()
    {
        $user = User::factory()->has(Profile::factory([
            'display_name' => '初期ユーザー',
            'post_code' => '111-2222',
            'address' => '東京都港区',
            'building' => 'ビル303',
            'image' => 'default.png',
        ]))->create();

        $this->actingAs($user);

        $response = $this->get('/mypage/profile');

        $response->assertStatus(200);
        $response->assertSee('初期ユーザー');
        $response->assertSee('111-2222');
        $response->assertSee('東京都港区');
        $response->assertSee('ビル303');
        $response->assertSee('default.png');
    }
}
