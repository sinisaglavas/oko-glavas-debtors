<?php

namespace Database\Factories;

use App\Models\Debt;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'debt_id' => Debt::factory(),
            'amount_minor' => fake()->numberBetween(1000, 20000),
            'paid_on' => fake()->dateTimeBetween('-1 year', 'today')->format('Y-m-d'),
            'note' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
            'submission_token' => fake()->unique()->uuid(),
        ];
    }
}
