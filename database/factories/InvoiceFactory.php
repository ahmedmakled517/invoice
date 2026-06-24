<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type'            => 'invoice',
            'number'          => 'INV-' . fake()->unique()->numerify('2026-####'),
            'customer_id'     => Customer::factory(),
            'issue_date'      => '2026-01-01',
            'due_date'        => '2026-01-15',
            'valid_until'     => null,
            'discount_type'   => 'percent',
            'discount_value'  => 0,
            'currency'        => 'SAR',
            'items_subtotal'  => 0,
            'discount_amount' => 0,
            'tax_total'       => 0,
            'grand_total'     => 0,
            'notes'           => null,
        ];
    }

    public function quotation(): static
    {
        return $this->state(fn () => [
            'type'        => 'quotation',
            'number'      => 'QUO-' . fake()->unique()->numerify('2026-####'),
            'due_date'    => null,
            'valid_until' => '2026-02-01',
        ]);
    }
}
