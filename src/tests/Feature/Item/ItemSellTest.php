<?php

namespace Tests\Feature\Item;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Category;
use App\Models\Condition;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ItemSellTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出品情報が正しく保存される()
    {
        Storage::fake('public');

        $user = User::factory()->has(Profile::factory())->create();
        $this->actingAs($user);

        $condition = Condition::factory()->create(['name' => '新品']);
        $category = Category::factory()->create(['name' => 'カメラ']);

        $imagePath = tempnam(sys_get_temp_dir(), 'test_img');
        file_put_contents($imagePath, 'dummy image content');
        $image = new UploadedFile(
            $imagePath,
            'test.jpg',
            'image/jpeg',
            null,
            true
        );

        $response = $this->post('/sell', [
            'name' => 'ミラーレス一眼',
            'bland' => 'CANON',
            'price' => 99999,
            'description' => 'プロ仕様の高画質カメラ',
            'condition_id' => $condition->id,
            'category' => [$category->id],
            'image' => $image,
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('items', [
            'name' => 'ミラーレス一眼',
            'bland' => 'CANON',
            'price' => 99999,
            'description' => 'プロ仕様の高画質カメラ',
            'condition_id' => $condition->id,
            'user_id' => $user->id,
        ]);
    }
}
