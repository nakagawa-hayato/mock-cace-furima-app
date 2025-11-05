<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ConversationsTableSeeder extends Seeder
{
    public function run(): void
    {
        // 簡単にサンプル会話を3件作る
        // item_id は items シーダーで作成される id 1..10 を想定
        DB::table('conversations')->insert([
            [
                'item_id' => 1,
                'seller_id' => 1, // item 1 の出品者 (ItemsTableSeeder では user_id=1)
                'buyer_id' => 2,
                'last_message_at' => Carbon::now()->subMinutes(10),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subMinutes(10),
            ],
            [
                'item_id' => 2,
                'seller_id' => 1,
                'buyer_id' => 3,
                'last_message_at' => Carbon::now()->subHours(2),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subHours(2),
            ],
            [
                'item_id' => 6,
                'seller_id' => 2,
                'buyer_id' => 1,
                'last_message_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(1),
            ],
        ]);
    }
}

