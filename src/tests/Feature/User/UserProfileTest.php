<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function プロフィールページで情報が正しく表示される()
    {
        $user = User::factory()->has(Profile::factory([
            'display_name' => 'テスト太郎',
            'image' => 'default.png',
        ]))->create();

        // 出品した商品（売却済みフラグを立てる）
        $item = Item::factory()->for($user, 'user')->create(['name' => '出品カメラ', 'is_sold' => true]);

        $this->actingAs($user);
        $response = $this->get('/mypage');

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('出品カメラ');
        $response->assertSee('SOLD');
        $response->assertSee('プロフィールを編集');
    }
}
