<?php

namespace Tests\Feature\Comment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use App\Models\Condition;
use App\Models\Comment;

class CommentTest extends TestCase
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
                'image' => 'sample.jpg',
            ]);
    }

    /** @test */
    public function ログインユーザーはコメントを送信できる()
    {
        $user = User::factory()->has(Profile::factory())->create();
        $item = $this->createItem();

        $this->actingAs($user);

        $response = $this->post('/comment', [
            'item_id' => $item->id,
            'comment' => 'とても素敵な商品ですね！',
        ]);

        $response->assertRedirect(); // リダイレクトされる想定
        $this->assertDatabaseHas('comments', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'comment' => 'とても素敵な商品ですね！',
        ]);
    }

    /** @test */
    public function 未ログインユーザーはコメントを送信できない()
    {
        $item = $this->createItem();

        $response = $this->post('/comment', [
            'item_id' => $item->id,
            'comment' => 'ゲストからのコメント',
        ]);

        $response->assertRedirect('/login'); // 認証ミドルウェアによるリダイレクト
        $this->assertDatabaseMissing('comments', [
            'comment' => 'ゲストからのコメント',
        ]);
    }

    /** @test */
    public function コメントが未入力の場合はバリデーションエラーになる()
    {
        $user = User::factory()->has(Profile::factory())->create();
        $item = $this->createItem();

        $this->actingAs($user);

        $response = $this->from("/item/{$item->id}")->post('/comment', [
            'item_id' => $item->id,
            'comment' => '',
        ]);

        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHasErrors(['comment']);
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
        ]);
    }

    /** @test */
    public function コメントが255文字を超えるとバリデーションエラーになる()
    {
        $user = User::factory()->has(Profile::factory())->create();
        $item = $this->createItem();

        $this->actingAs($user);

        $longComment = str_repeat('あ', 256);

        $response = $this->from("/item/{$item->id}")->post('/comment', [
            'item_id' => $item->id,
            'comment' => $longComment,
        ]);

        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHasErrors(['comment']);
        $this->assertDatabaseMissing('comments', [
            'comment' => $longComment,
        ]);
    }
}
