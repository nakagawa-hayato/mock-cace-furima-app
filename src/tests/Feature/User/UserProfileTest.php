<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use App\Models\Order;
use App\Models\Condition;


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

        $item = Item::factory()->for($user, 'user')->create(['name' => '出品カメラ', 'is_sold' => true]);
        Order::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'method' => 'カード支払い',
            'post_code' => '000-0000',
            'address' => '東京都渋谷区',
        ]);

        $this->actingAs($user);
        $response = $this->get('/mypage');

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('出品カメラ');
        $response->assertSee('SOLD');
        $response->assertSee('プロフィールを編集');
    }
}
