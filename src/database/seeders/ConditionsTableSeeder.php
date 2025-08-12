<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ConditionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('conditions')->insert([
            [
                'name' => '良好',
            ],
            [
                'name' => '目立った傷や汚れなし',
            ],
            [
                'name' => 'やや傷や汚れなし',
            ],
            [
                'name' => '状態が悪い',
            ],
        ]);
    }
}
