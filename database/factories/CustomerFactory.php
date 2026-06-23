<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
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
            'name'       => fake()->company(),
            'email'      => fake()->unique()->safeEmail(),
            'phone'      => '+9665' . fake()->numerify('########'),
            'tax_number' => '3' . fake()->numerify('############') . '3',
            'address'    => fake()->city(),
        ];
    }
}
