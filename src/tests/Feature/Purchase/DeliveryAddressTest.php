<?php

namespace Tests\Feature\Purchase;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use App\Models\Order;
use App\Models\Condition;

class DeliveryAddressTest extends TestCase
{
    use RefreshDatabase;

    private function createItem(): Item
    {
        $seller = User::factory()->has(Profile::factory())->create();
        $condition = Condition::factory()->create();

        return Item::factory()
            ->for($seller, 'user')
            ->for($condition)
            ->create([
                'image' => 'camera.jpg',
                'is_sold' => false,
            ]);
    }

    /** @test */
    public function 登録した配送先住所が購入画面に反映される()
    {
        $user = User::factory()->has(Profile::factory([
            'post_code' => '000-0000',
            'address' => '旧住所',
            'building' => '旧ビル',
        ]))->create();

        $item = $this->createItem();

        $this->actingAs($user);

        // 1. 住所変更 POST
        $this->post("/purchase/address/{$item->id}", [
            'post_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => '新ビル101',
        ]);

        // 2. 商品購入画面 GET
        $response = $this->get("/purchase/{$item->id}");

        $response->assertStatus(200);
        // HTML では「〒」と input が分かれる可能性があるので郵便番号だけを検証
        $response->assertSee('123-4567');
        $response->assertSee('東京都渋谷区');
        $response->assertSee('新ビル101');
    }

    /** @test */
    public function 登録した配送先住所が注文情報に保存される()
    {
        $user = User::factory()->has(Profile::factory())->create();
        $item = $this->createItem();

        $this->actingAs($user);

        // 1. 住所変更
        $this->post("/purchase/address/{$item->id}", [
            'post_code' => '111-2222',
            'address' => '東京都港区',
            'building' => 'ミッドタワー34F',
        ]);

        // 2. セッションに保存（controller は order_payment などを使うがここは success を直接呼ぶ）
        session([
            'order_payment' => 'konbini',
            'order_post_code' => '111-2222',
            'order_address' => '東京都港区',
            'order_building' => 'ミッドタワー34F',
        ]);

        $response = $this->get("/purchase/success/{$item->id}");

        $response->assertRedirect('/');

        // DB のカラム名は controller 実装に合わせて sending_* を検証
        $this->assertDatabaseHas('orders', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'sending_postcode' => '111-2222',
            'sending_address' => '東京都港区',
            'sending_building' => 'ミッドタワー34F',
        ]);
    }
}
