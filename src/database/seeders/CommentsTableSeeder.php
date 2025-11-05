<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;
use Faker\Factory as Faker;
use Exception;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * - users と items が存在することを前提に、ランダムにコメントを作成します。
     * - item_id / user_id の外部キー制約に合うよう存在チェックを行い、
     *   既に同一 user が同一 item に同じテキストでコメントしている場合は重複回避します（簡易）。
     */
    public function run()
    {
        $faker = Faker::create('ja_JP');

        $userIds = User::pluck('id')->toArray();
        $itemIds = Item::pluck('id')->toArray();

        if (empty($userIds) || empty($itemIds)) {
            $this->command->info('Users or Items are missing — skipping comments seeding.');
            return;
        }

        $countToCreate = 80; // 作成したいコメント数（必要に応じて変更）

        $created = 0;
        $tries = 0;
        while ($created < $countToCreate && $tries < $countToCreate * 4) {
            $tries++;

            $userId = $faker->randomElement($userIds);
            $itemId = $faker->randomElement($itemIds);

            // 出品者が自分自身にコメントするのは避けたい場合は以下を有効に
            // $itemOwnerId = Item::find($itemId)->user_id ?? null;
            // if ($itemOwnerId && $itemOwnerId === $userId) {
            //     continue;
            // }

            $text = $faker->realText(60); // 文字列長は migration の string に注意

            // 重複（ほぼ）回避：同じ user が同じ item に同じ文を投げないようにする
            $exists = Comment::where('user_id', $userId)
                ->where('item_id', $itemId)
                ->where('comment', $text)
                ->exists();

            if ($exists) {
                continue;
            }

            try {
                Comment::create([
                    'user_id' => $userId,
                    'item_id' => $itemId,
                    'comment' => $text,
                ]);
                $created++;
            } catch (Exception $e) {
                // 制約エラー等はスキップして次へ
                \Log::warning('CommentsTableSeeder: failed to create comment - ' . $e->getMessage());
                continue;
            }
        }

        $this->command->info("CommentsTableSeeder: created {$created} comments (attempted {$tries} tries).");
    }
}
