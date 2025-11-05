<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MessageReadsTableSeeder extends Seeder
{
    public function run(): void
    {
        // message_reads は message_id と user_id の組合せで read_at を記録する
        // ここでは一部メッセージを既読にし、他は未読にしておく
        DB::table('message_reads')->insert([
            // conversation 1 の最初のメッセージ（id=1）の既読を seller(1) が確認済みにする
            [
                'message_id' => 1,
                'user_id' => 1, // seller が buyer のメッセージを既読
                'read_at' => Carbon::now()->subMinutes(20),
            ],
            // conversation 1 の2番目メッセージ（id=2）を buyer が既読にした例
            [
                'message_id' => 2,
                'user_id' => 2,
                'read_at' => Carbon::now()->subMinutes(10),
            ],
            // conversation 2 の最初メッセージは seller(1) が未読のままにする（read_at=null） -> insertではnull許容のため omit も可
            [
                'message_id' => 3,
                'user_id' => 1,
                'read_at' => null,
            ],
            // conversation 3 の seller の返信（id=6）を buyer が既読
            [
                'message_id' => 6,
                'user_id' => 1,
                'read_at' => Carbon::now()->subHours(18),
            ],
        ]);
    }
}

