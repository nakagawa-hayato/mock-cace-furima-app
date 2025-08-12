<?php

namespace Tests\Feature\Favorite;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use App\Models\Condition;
use App\Models\Category;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ユーザーが商品をいいねして登録できる()
    {
        $user = User::factory()->has(Profile::factory())->create();

        $condition = Condition::factory()->create();
        $item = Item::factory()
            ->for($user, 'user') // 出品者
            ->for($condition)
            ->create([
                'image' => 'sample.jpg',
            ]);

        $this->actingAs($user);

        // 初期状態：いいねが0
        $this->assertEquals(0, $item->favorites()->count());

        // いいねを送信（POST）
        $response = $this->post("/favorite/{$item->id}");
        $response->assertRedirect();

        // いいねが1つ追加されているか確認
        $this->assertEquals(1, $item->fresh()->favorites()->count());
    }

    /** @test */
    public function いいねアイコンが登録状態に応じて変化し、再クリックで解除される()
    {
        $user = User::factory()->has(Profile::factory())->create();

        $item = Item::factory()
            ->for($user, 'user')
            ->for(Condition::factory())
            ->create([
                'image' => 'sample.jpg',
            ]);

        $this->actingAs($user);

        // 最初はいいねされていない
        $this->assertFalse($item->isFavoritedBy($user));

        // いいね登録（POST）
        $this->post("/favorite/{$item->id}");
        $item->refresh();
        $this->assertTrue($item->isFavoritedBy($user));

        // アイコンが「⭐️」表示（ビューで切り替える想定）
        $response = $this->get("/item/{$item->id}");
        $response->assertSee('⭐️');

        // いいね解除（DELETE）
        $this->delete("/favorite/{$item->id}");
        $item->refresh();
        $this->assertFalse($item->isFavoritedBy($user));

        // 再度ページにアクセス → アイコンが「☆」に戻る
        $response = $this->get("/item/{$item->id}");
        $response->assertSee('☆');
    }

    /** @test */
    public function いいね登録と解除で合計数が増減する()
    {
        $user = User::factory()->has(Profile::factory())->create();

        $item = Item::factory()
            ->for($user, 'user')
            ->for(Condition::factory())
            ->create([
                'image' => 'sample.jpg',
            ]);

        $this->actingAs($user);

        // 初期状態
        $this->assertEquals(0, $item->favorites()->count());

        // いいね登録
        $this->post("/favorite/{$item->id}");
        $this->assertEquals(1, $item->fresh()->favorites()->count());

        // いいね解除
        $this->delete("/favorite/{$item->id}");
        $this->assertEquals(0, $item->fresh()->favorites()->count());
    }
}
