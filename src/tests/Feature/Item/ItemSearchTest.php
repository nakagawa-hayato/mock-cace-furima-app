<?php

namespace Tests\Feature\Item;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 各 Item に必要な条件
        $this->condition = Condition::factory()->create();
    }

    /** @test */
    public function 部分一致する商品が検索結果に表示される()
    {
        Item::factory()->create([
            'name' => 'スマートフォン',
            'condition_id' => $this->condition->id,
        ]);

        Item::factory()->create([
            'name' => 'タブレット',
            'condition_id' => $this->condition->id,
        ]);

        $response = $this->get('/?keyword=スマ');

        $response->assertStatus(200);
        $response->assertSee('スマートフォン');
        $response->assertDontSee('タブレット');
    }

    /** @test */
    public function ホームページで検索すると結果が表示される()
    {
        Item::factory()->create([
            'name' => 'パソコン',
            'condition_id' => $this->condition->id,
        ]);

        $response = $this->get('/?keyword=パソ');

        $response->assertStatus(200);
        $response->assertSee('パソコン');
    }

    /** @test */
    public function マイリストで検索するとキーワードが保持される()
    {
        $user = User::factory()->create();

        // ログインユーザーの商品をいいねした設定
        $item = Item::factory()->create([
            'name' => 'デジカメ',
            'condition_id' => $this->condition->id,
        ]);
        $user->favoriteItems()->attach($item->id);

        $response = $this
            ->actingAs($user)
            ->get('/?tab=mylist&keyword=デジ');

        $response->assertStatus(200);
        $response->assertSee('デジカメ');

        // キーワード保持（フォーム入力欄にvalueとして入っているか）
        $response->assertSee('value="デジ"', false);
    }
}
