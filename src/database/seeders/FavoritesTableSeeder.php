<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use Faker\Factory as Faker;

class FavoritesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * - users と items が存在することを前提とします。
     * - 重複 (同一 user_id + item_id) は firstOrCreate で回避します。
     */
    public function run()
    {
        $faker = Faker::create('ja_JP');

        $userIds = User::pluck('id')->toArray();
        $itemIds = Item::pluck('id')->toArray();

        if (empty($userIds) || empty($itemIds)) {
            $this->command->info('Users or Items are missing — skipping favorites seeding.');
            return;
        }

        $targetCount = 200; // 生成したいお気に入り数（必要に応じて変更）
        $created = 0;
        $tries = 0;

        while ($created < $targetCount && $tries < $targetCount * 4) {
            $tries++;

            $userId = $faker->randomElement($userIds);
            $itemId = $faker->randomElement($itemIds);

            // オプション: 出品者が自分のアイテムにfavしないようにしたければ有効化
            // $itemOwnerId = Item::find($itemId)->user_id ?? null;
            // if ($itemOwnerId === $userId) continue;

            // 重複回避: 既に存在すればスキップ
            $fav = Favorite::firstOrCreate(
                ['user_id' => $userId, 'item_id' => $itemId],
                // second arg only used if creating; no additional fields needed
                []
            );

            // firstOrCreate returns model; if it was newly created, increment
            if ($fav->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->command->info("FavoritesTableSeeder: created {$created} favorites (attempted {$tries} tries).");
    }
}
