<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->numberBetween(50, 1000);

        return [
            'invoice_id'    => Invoice::factory(),
            'description'   => fake()->sentence(3),
            'quantity'      => $quantity,
            'unit_price'    => $unitPrice,
            'tax_rate'      => 15,
            'line_subtotal' => $quantity * $unitPrice,
            'line_tax'      => round($quantity * $unitPrice * 0.15, 2),
        ];
    }
}
