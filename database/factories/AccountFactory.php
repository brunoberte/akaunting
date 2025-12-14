<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id'      => 1,
            'name'            => $this->faker->words(3, true),
            'number'          => $this->faker->numberBetween(1000, 5000),
            'currency_code'   => 'BRL',
            'opening_balance' => 0.00,
            'enabled'         => 1,
        ];
    }
}
