<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Optika Glavas '.fake()->unique()->city(),
            'code' => fake()->unique()->bothify('??-#'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'phone' => fake()->e164PhoneNumber(),
            'is_active' => true,
        ];
    }
}
