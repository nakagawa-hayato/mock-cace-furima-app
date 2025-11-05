<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RatingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder inserts ratings WITHOUT a `comment` column,
     * because you removed the comment column from the ratings table.
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();

        DB::table('ratings')->insert([
            [
                'rated_user_id' => 1,
                'rater_user_id' => 2,
                'item_id' => 1,
                'score' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'rated_user_id' => 1,
                'rater_user_id' => 3,
                'item_id' => 2,
                'score' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'rated_user_id' => 2,
                'rater_user_id' => 1,
                'item_id' => 6,
                'score' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
