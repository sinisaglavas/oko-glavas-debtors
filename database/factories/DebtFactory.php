<?php

namespace Database\Factories;

use App\Models\Debt;
use App\Models\Debtor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'debtor_id' => Debtor::factory(),
            'incurred_on' => fake()->dateTimeBetween('-2 years', 'today')->format('Y-m-d'),
            'original_amount_minor' => fake()->numberBetween(20000, 250000),
            'installment_count' => fake()->numberBetween(2, 24),
            'created_by' => User::factory(),
        ];
    }
}
