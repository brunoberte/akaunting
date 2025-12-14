<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Revenue>
 */
class RevenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id'    => 1,
            'account_id'    => 1,
            'paid_at'       => $this->faker->dateTime(),
            'amount'        => $this->faker->randomFloat(2),
            'currency_code' => 'BRL',
            'currency_rate' => 0.00,
            'category_id'   => 1,
            'description'   => $this->faker->sentence(),
        ];
    }
}
