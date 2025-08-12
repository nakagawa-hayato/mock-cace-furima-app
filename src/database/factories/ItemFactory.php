<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Condition;
use App\Models\User;
use App\Models\Item;

class ItemFactory extends Factory
{
    protected $model = Item::class;
    
    public function definition(): array
    {
        return [
        'name' => $this->faker->word,
        'price' => $this->faker->numberBetween(1000, 5000),
        'is_sold' => false,
        'user_id' => \App\Models\User::factory(),
        'condition_id' => Condition::factory(),
        'bland' => $this->faker->word,
        'description' => $this->faker->sentence(10), // 商品説明
            'image' => 'images/test.jpg', // テスト用のダミーパス（storage/public/images/test.jpg など）
        ];
    }
}
