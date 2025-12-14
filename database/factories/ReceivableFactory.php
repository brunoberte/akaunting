<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receivable>
 */
class ReceivableFactory extends Factory
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
            'due_at'        => $this->faker->dateTime(),
            'currency_code' => 'BRL',
            'amount'        => $this->faker->randomFloat(2),
            'title'         => $this->faker->sentence(),
            'customer_id'   => 1,
            'category_id'   => 1,
        ];
    }
}
