<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MessagesTableSeeder extends Seeder
{
    public function run(): void
    {
        // conversation_id は上の ConversationsTableSeeder の順序に従う (1,2,3)
        DB::table('messages')->insert([
            // Conversation 1 (item 1: seller=1, buyer=2)
            [
                'conversation_id' => 1,
                'user_id' => 2, // buyer が最初に質問
                'body' => 'こちらの商品、まだ在庫ありますか？',
                'image' => null,
                'created_at' => Carbon::now()->subMinutes(30),
                'updated_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'conversation_id' => 1,
                'user_id' => 1, // seller が返信
                'body' => 'はい、まだあります。状態は良好です。',
                'image' => null,
                'created_at' => Carbon::now()->subMinutes(25),
                'updated_at' => Carbon::now()->subMinutes(25),
            ],
            // Conversation 2 (item 2: seller=1, buyer=3)
            [
                'conversation_id' => 2,
                'user_id' => 3,
                'body' => '発送方法は何を予定していますか？',
                'image' => null,
                'created_at' => Carbon::now()->subHours(3),
                'updated_at' => Carbon::now()->subHours(3),
            ],
            [
                'conversation_id' => 2,
                'user_id' => 1,
                'body' => 'クリックポストを予定しています。追跡ありです。',
                'image' => null,
                'created_at' => Carbon::now()->subHours(2)->addMinutes(30),
                'updated_at' => Carbon::now()->subHours(2)->addMinutes(30),
            ],
            // Conversation 3 (item 6: seller=2, buyer=1)
            [
                'conversation_id' => 3,
                'user_id' => 1,
                'body' => '購入を希望します。お支払い方法は何が使えますか？',
                'image' => null,
                'created_at' => Carbon::now()->subDays(1)->addHours(1),
                'updated_at' => Carbon::now()->subDays(1)->addHours(1),
            ],
            [
                'conversation_id' => 3,
                'user_id' => 2,
                'body' => 'カード決済またはコンビニ支払いが可能です。',
                'image' => null,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
        ]);
    }
}
