<?php

namespace Tests\Feature\Item;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 部分一致する商品が検索結果に表示される()
    {
        Item::factory()->create(['name' => 'デジカメ']);
        Item::factory()->create(['name' => 'ノートPC']);

        $response = $this->get('/?keyword=デジ');
        $response->assertStatus(200);
        $response->assertSee('デジカメ');
        $response->assertDontSee('ノートPC');
    }

    /** @test */
    public function ホームページで検索すると結果が表示される()
    {
        Item::factory()->create(['name' => 'カメラ']);
        Item::factory()->create(['name' => 'スマホ']);

        $response = $this->get('/?keyword=カメラ');
        $response->assertStatus(200);
        $response->assertSee('カメラ');
        $response->assertDontSee('スマホ');
    }

    /** @test */
    public function マイリストで検索するとキーワードが保持される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['name' => 'デジカメ']);
        $user->favoriteItems()->attach($item->id);

        $response = $this
            ->actingAs($user)
            ->get('/?tab=mylist&keyword=デジ');

        $response->assertStatus(200);
        $response->assertSee('デジカメ');

        // フォーム入力欄に value として保持されているか
        $response->assertSee('value="デジ"', false);
    }
}
