<?php

namespace Tests\Feature\Item;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 商品ページを開くとすべての商品が表示される()
    {
        $items = Item::factory()->count(3)->create();

        $response = $this->get('/');
        foreach ($items as $item) {
            $response->assertSee($item->name);
        }
    }

    /** @test */
    public function 購入済みの商品は_sold_ラベルが表示される()
    {
        $item = Item::factory()->create(['is_sold' => true]);

        $response = $this->get('/');
        $response->assertSee('SOLD');
    }

    /** @test */
    public function マイリストページでは自分が出品した商品が表示されない()
    {
        $user = User::factory()->create();
        $myItem = Item::factory()->create(['user_id' => $user->id]);
        $likedItem = Item::factory()->create();

        $user->favoriteItems()->attach($likedItem->id);

        $this->actingAs($user);
        $response = $this->get('/?tab=mylist');

        $response->assertSee($likedItem->name);
        $response->assertDontSee($myItem->name);
    }

    /** @test */
    public function 未ログインでマイリストページを開くと何も表示されない()
    {
        Item::factory()->count(3)->create();

        $response = $this->get('/?tab=mylist');
        $response->assertSee('マイリスト');
        $response->assertDontSeeText('SOLD');
        $response->assertDontSeeText('item'); // 商品名の部分一致
    }
}
