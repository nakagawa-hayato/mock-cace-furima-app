<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Items')->insert([
            [
                'user_id' => '1',
                'condition_id' => '1',
                'name' => '腕時計',
                'bland' => 'Armani',
                'price' => '15000',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image' => 'images/Armani+Mens+Clock.jpg',
            ],
            [
                'user_id' => '1',
                'condition_id' => '2',
                'name' => 'HDD',
                'bland' => 'Toshiba',
                'price' => '5000',
                'description' => '高速で信頼性の高いハードディスク',
                'image' => 'images/HDD+Hard+Disk.jpg',
            ],
            [
                'user_id' => '2',
                'condition_id' => '3',
                'name' => '玉ねぎ3束',
                'bland' => 'mogitate',
                'price' => '300',
                'description' => '新鮮な玉ねぎ3束のセット',
                'image' => 'images/iLoveIMG+d.jpg',
            ],
            [
                'user_id' => '2',
                'condition_id' => '4',
                'name' => '革靴',
                'bland' => 'John Lobb',
                'price' => '4000',
                'description' => 'クラシックなデザインの革靴',
                'image' => 'images/Leather+Shoes+Product+Photo.jpg',
            ],
            [
                'user_id' => '3',
                'condition_id' => '1',
                'name' => 'ノートPC',
                'bland' => 'Apple',
                'price' => '45000',
                'description' => '高性能なノートパソコン',
                'image' => 'images/Living+Room+Laptop.jpg',
            ],
            [
                'user_id' => '3',
                'condition_id' => '2',
                'name' => 'マイク',
                'bland' => 'Panasonic',
                'price' => '8000',
                'description' => '高音質のレコーディング用マイク',
                'image' => 'images/Music+Mic+4632231.jpg',
            ],
            [
                'user_id' => '4',
                'condition_id' => '3',
                'name' => 'ショルダーバック',
                'bland' => 'エルメス',
                'price' => '3500',
                'description' => 'おしゃれなショルダーバッグ',
                'image' => 'images/Purse+fashion+pocket.jpg',
            ],
            [
                'user_id' => '4',
                'condition_id' => '4',
                'name' => 'タンブラー',
                'bland' => '39ショップ',
                'price' => '500',
                'description' => '使いやすいタンブラー',
                'image' => 'images/Tumbler+souvenir.jpg',
            ],
            [
                'user_id' => '5',
                'condition_id' => '1',
                'name' => 'コーヒーミル',
                'bland' => '39ショップ',
                'price' => '4000',
                'description' => '手動のコーヒーミル',
                'image' => 'images/Waitress+with+Coffee+Grinder.jpg',
            ],
            [
                'user_id' => '5',
                'condition_id' => '2',
                'name' => 'メイクセット',
                'bland' => '39ショップ',
                'price' => '2500',
                'description' => '便利なメイクアップセット',
                'image' => 'images/外出メイクアップセット.jpg',
            ],
        ]);

        $sourcePath = database_path('seeders/images');
        $destinationPath = storage_path('app/public/images');

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        $files = File::files($sourcePath);
        foreach ($files as $file) {
            File::copy($file->getPathname(), $destinationPath . '/' . $file->getFilename());
        }
    }
}
