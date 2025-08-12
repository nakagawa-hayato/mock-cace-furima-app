<?php

namespace Tests\Feature\Item;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;

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
    public function 購入済みの商品はSOLDラベルが表示される()
    {
        $soldItem = Item::factory()->create(['is_sold' => true]);
        $unsoldItem = Item::factory()->create(['is_sold' => false]);

        $response = $this->get('/');

        $response->assertSee('SOLD');
        $response->assertSee($soldItem->name);
        $response->assertSee($unsoldItem->name);
    }

    /** @test */
    public function マイリストページでは自分が出品した商品が表示されない()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myItem = Item::factory()->create(['user_id' => $user->id]);
        $likedItem = Item::factory()->create(['user_id' => $otherUser->id]);

        // お気に入り登録
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        $this->actingAs($user);
        $response = $this->get('/?tab=mylist');

        $response->assertSee($likedItem->name);
        $response->assertDontSee($myItem->name);
    }

    /** @test */
    public function 未ログインでマイリストページを開くと何も表示されない()
    {
        $item = Item::factory()->create();

        $response = $this->get('/?tab=mylist');

        $response->assertDontSee($item->name);
    }
}
