<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use App\Models\Order;
use App\Models\Item;
use Exception;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * このシーダーは「ユーザー1 と ユーザー2 がそれぞれ 1 商品ずつ売れた状態」を作ります。
     * ConversationsTableSeeder で使われている item_id (例: 1,2,6) とは被らないように指定しています。
     *
     * 想定: items シーダーで item id は 1..10 の連番で作られている。
     */
    public function run()
    {
        // 明示的に売れた扱いにする item_id と buyer_id のペア
        $pairs = [
            // [ item_id, buyer_id ]
            [3, 2], // item 3 を user 2 が購入（seller は items.user_id=1 の想定）
            [7, 1], // item 7 を user 1 が購入（seller は items.user_id=2 の想定）
        ];

        $created = 0;

        foreach ($pairs as [$itemId, $buyerId]) {
            // items テーブルに該当行があるか
            $item = Item::find($itemId);
            if (! $item) {
                $this->command->warn("OrdersTableSeeder: item_id {$itemId} not found — skipping.");
                continue;
            }

            // 既に order がある or item が売却済みならスキップ
            $orderExists = DB::table('orders')->where('item_id', $itemId)->exists();
            if ($orderExists) {
                $this->command->info("OrdersTableSeeder: order for item_id {$itemId} already exists — skipping.");
                continue;
            }

            if (isset($item->is_sold) && $item->is_sold) {
                $this->command->info("OrdersTableSeeder: item_id {$itemId} is already marked as sold — skipping.");
                continue;
            }

            // トランザクションで作成
            try {
                DB::transaction(function () use ($item, $buyerId, &$created) {
                    // Order 作成
                    Order::create([
                        'user_id' => $buyerId,
                        'item_id' => $item->id,
                        // 住所情報はダミー（必要に応じて変更）
                        'sending_postcode' => '1000001',
                        'sending_address'  => '東京都千代田区千代田1-1',
                        'sending_building' => 'シーダーテストビル',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    // item を売却済みに更新（is_sold カラムがあれば）
                    if (Schema::hasColumn('items', 'is_sold')) {
                        $item->is_sold = true;
                        $item->save();
                    }

                    $created++;
                });
            } catch (Exception $e) {
                $this->command->error("OrdersTableSeeder: failed for item_id {$item->id} — {$e->getMessage()}");
                continue;
            }
        }

        $this->command->info("OrdersTableSeeder: created {$created} orders.");
    }
}
