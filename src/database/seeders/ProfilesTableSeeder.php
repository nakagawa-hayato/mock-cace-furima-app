<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProfilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $profiles = [
            [
                'id' => 1,
                'user_id' => 1,
                'display_name' => 'Taro',
                'post_code' => '111-1111',
                'address' => '北海道',
                'building' => '自宅',
                'image' => 'https://placehold.jp/150x150.png', // サンプル画像URL
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'display_name' => 'Jiro',
                'post_code' => '222-2222',
                'address' => '東京',
                'building' => 'ビル',
                'image' => 'https://placehold.jp/150x150.png',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'display_name' => 'Saburo',
                'post_code' => '333-3333',
                'address' => '愛知',
                'building' => '社宅',
                'image' => 'https://placehold.jp/150x150.png',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'display_name' => 'Shiro',
                'post_code' => '444-4444',
                'address' => '富山',
                'building' => '小屋',
                'image' => 'https://placehold.jp/150x150.png',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'user_id' => 5,
                'display_name' => 'Goro',
                'post_code' => '555-5555',
                'address' => '沖縄',
                'building' => '別荘',
                'image' => 'https://placehold.jp/150x150.png',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('profiles')->insert($profiles);
    }
}
