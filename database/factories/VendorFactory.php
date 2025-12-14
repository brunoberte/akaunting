<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
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
            'name'          => $this->faker->words(3, true),
            'email'         => $this->faker->email(),
            'currency_code' => 'BRL',
            'enabled'       => 1,
        ];
    }
}
