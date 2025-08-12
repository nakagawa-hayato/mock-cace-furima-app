<?php

namespace Tests\Feature\Purchase;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use App\Models\Condition;
use App\Models\Order;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function createItem()
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
    public function 商品を購入すると購入済みとなり一覧にsoldと表示される()
    {
        $buyer = User::factory()->has(Profile::factory([
            'post_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => 'ビル101'
        ]))->create();

        $item = $this->createItem();

        $this->actingAs($buyer);

        // 事前にセッションへ情報を保存（通常Stripeが保存するもの）
        session([
            'order_method' => 'コンビニ支払い',
            'order_post_code' => $buyer->profile->post_code,
            'order_address' => $buyer->profile->address,
            'order_building' => $buyer->profile->building,
        ]);

        // 購入完了（Stripeではsuccessコールバックから来る）
        $response = $this->get("/purchase/success/{$item->id}");

        $response->assertRedirect('/');
        $response->assertSessionHas('status', '購入が完了しました');

        $this->assertDatabaseHas('orders', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'method' => 'コンビニ支払い',
        ]);

        $this->assertTrue(Item::find($item->id)->is_sold);
    }

    /** @test */
public function 購入した商品がプロフィール購入履歴に表示される()
{
    $buyer = User::factory()->has(Profile::factory())->create();
    $item = Item::factory()->for($buyer, 'user')->create([
        'is_sold' => true,
        'name' => '高級カメラ',
        'image' => 'camera.jpg',
    ]);

    // 購入履歴に追加
    Order::create([
        'user_id' => $buyer->id,
        'item_id' => $item->id,
        'method' => 'カード支払い',
        'post_code' => '123-4567',
        'address' => '東京都港区',
        'building' => 'テストビル',
    ]);

    $this->actingAs($buyer);

    $response = $this->get('/mypage');

    $response->assertStatus(200);
    $response->assertSee('高級カメラ'); // 商品名表示の確認

    // ↓ SOLDであることを明示的に表示しているかチェック（index.blade.phpと合わせる）
    $response->assertSee('SOLD');
}

    /** @test */
    public function 未ログインでは購入ページにアクセスできない()
    {
        $item = $this->createItem();

        $response = $this->get("/purchase/{$item->id}");

        $response->assertRedirect('/login');
    }
}
