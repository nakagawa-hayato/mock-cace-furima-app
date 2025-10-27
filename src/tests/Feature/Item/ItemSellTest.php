<?php

namespace Tests\Feature\Item;

use Tests\TestCase;
use App\Models\User;
use App\Models\Condition;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class ItemSellTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出品情報が正しく保存される()
    {
        // ストレージを fake に切り替え
        Storage::fake('public');

        // 必要な参照データを作成（条件・カテゴリ）
        $condition = Condition::factory()->create();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $user = User::factory()->create();

        // 小さな PNG を base64 で用意して一時ファイルに保存（GD に依存しない方法）
        $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQImWNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=';
        $tmpPath = sys_get_temp_dir() . '/test-upload-' . uniqid() . '.png';
        file_put_contents($tmpPath, base64_decode($pngBase64));

        // 第5引数 true を渡すと is_uploaded_file チェックをバイパスするテストモードの UploadedFile を作れる
        $image = new UploadedFile($tmpPath, 'test_image.png', 'image/png', null, true);

        $response = $this->actingAs($user)->post('/sell', [
            'name' => 'ミラーレス一眼',
            'brand' => 'CANON',
            'price' => 50000,
            'description' => '高画質カメラです',
            'condition_id' => $condition->id,
            'category' => [$category1->id, $category2->id],
            'image' => $image,
        ]);

        // リダイレクト確認
        $response->assertRedirect('/');

        // バリデーションエラーがないか確認
        $response->assertSessionHasNoErrors();

        // 画像が保存されていることを確認 (store('images','public') で images/xxxxx が作られる想定)
        // hashName() はアップロード側で決まるため、storage 側に何か存在することを確認する方法として glob を使う
        $files = Storage::disk('public')->allFiles('images');
        $this->assertNotEmpty($files, 'images ディレクトリにファイルが存在するはずです');

        // DB に登録されていることを確認
        $this->assertDatabaseHas('items', [
            'name' => 'ミラーレス一眼',
            'brand' => 'CANON',
            'price' => 50000,
            'description' => '高画質カメラです',
        ]);

        // 一時ファイルを削除
        if (file_exists($tmpPath)) {
            @unlink($tmpPath);
        }
    }
}
