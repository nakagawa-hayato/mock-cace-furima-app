<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'display_name' => $this->faker->name,
            'image' => 'default.png',
            'post_code' => $this->faker->postcode,
            'address' => $this->faker->address,
        ];
    }
}
