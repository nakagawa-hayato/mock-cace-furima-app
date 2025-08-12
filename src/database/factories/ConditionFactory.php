<?php

namespace Database\Factories;
use App\Models\Condition;

use Illuminate\Database\Eloquent\Factories\Factory;

class ConditionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word, // 例: '新品', '中古'など
        ];
    }
}
