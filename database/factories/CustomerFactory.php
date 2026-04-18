<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
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
            'name'          => $this->faker->name(),
            'email'         => $this->faker->unique()->safeEmail(),
            'currency_code' => 'BRL',
            'enabled'       => 1,
        ];
    }
}
