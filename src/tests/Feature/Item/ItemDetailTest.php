<?php

namespace Tests\Feature\Item;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use App\Models\Condition;
use App\Models\Category;
use App\Models\Comment;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 商品詳細ページに必要な情報がすべて表示される()
    {
        // コメントユーザーとプロフィール作成
        $commentUser = User::factory()
            ->has(Profile::factory([
                'display_name' => 'コメントユーザー',
            ]))
            ->create();

        // 出品者（User + Profile）
        $seller = User::factory()
            ->has(Profile::factory([
                'display_name' => '出品者',
            ]))
            ->create();

        // コンディション（Factory使用）
        $condition = Condition::factory()->create([
            'name' => '新品',
        ]);

        // カテゴリを複数作成
        $category1 = Category::factory()->create(['name' => '家電']);
        $category2 = Category::factory()->create(['name' => 'カメラ']);
        $category3 = Category::factory()->create(['name' => '趣味']);

        // 商品作成（Factoryで関連付け）
        $item = Item::factory()
            ->for($seller, 'user')
            ->for($condition)
            ->create([
                'name' => 'デジタルカメラ',
                'brand' => 'CANON',
                'price' => 25000,
                'description' => '高画質なカメラです',
                'image' => 'camera.jpg',
            ]);

        // カテゴリを複数アタッチ
        $item->categories()->attach([
            $category1->id,
            $category2->id,
            $category3->id,
        ]);

        // コメント作成（Factoryでuser_id/item_id指定）
        Comment::factory()->create([
            'user_id' => $commentUser->id,
            'item_id' => $item->id,
            'comment' => 'とても良さそうな商品ですね！',
        ]);

        // 商品詳細ページにアクセス
        $response = $this->get("/item/{$item->id}");

        // ステータス確認
        $response->assertStatus(200);

        // 商品情報の表示確認
        $response->assertSee('デジタルカメラ');
        $response->assertSee('CANON');
        $response->assertSee('25,000');
        $response->assertSee('高画質なカメラです');
        $response->assertSee('新品');

        // カテゴリ複数表示確認
        $response->assertSee('家電');
        $response->assertSee('カメラ');
        $response->assertSee('趣味');

        // コメント表示確認
        $response->assertSee('コメントユーザー');
        $response->assertSee('とても良さそうな商品ですね！');
    }
}
