<?php

namespace Tests\Unit;

use App\Services\InvoiceCalculator;
use PHPUnit\Framework\TestCase;

class InvoiceCalculatorTest extends TestCase
{
    private InvoiceCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new InvoiceCalculator();
    }

    public function test_it_calculates_totals_without_discount(): void
    {
        $result = $this->calculator->calculate([
            ['description' => 'A', 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 15],
            ['description' => 'B', 'quantity' => 1, 'unit_price' => 50, 'tax_rate' => 15],
        ], 'percent', 0);

        $this->assertEqualsWithDelta(250, $result['items_subtotal'], 0.001);
        $this->assertEqualsWithDelta(0, $result['discount_amount'], 0.001);
        $this->assertEqualsWithDelta(37.5, $result['tax_total'], 0.001);
        $this->assertEqualsWithDelta(287.5, $result['grand_total'], 0.001);
        $this->assertEqualsWithDelta(200, $result['items'][0]['line_subtotal'], 0.001);
        $this->assertEqualsWithDelta(30, $result['items'][0]['line_tax'], 0.001);
    }

    public function test_it_applies_percent_discount_before_tax(): void
    {
        $result = $this->calculator->calculate([
            ['description' => 'A', 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 15],
            ['description' => 'B', 'quantity' => 1, 'unit_price' => 50, 'tax_rate' => 15],
        ], 'percent', 10);

        $this->assertEqualsWithDelta(250, $result['items_subtotal'], 0.001);
        $this->assertEqualsWithDelta(25, $result['discount_amount'], 0.001);
        $this->assertEqualsWithDelta(33.75, $result['tax_total'], 0.001);
        $this->assertEqualsWithDelta(258.75, $result['grand_total'], 0.001);
    }

    public function test_it_applies_fixed_discount(): void
    {
        $result = $this->calculator->calculate([
            ['description' => 'A', 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 15],
            ['description' => 'B', 'quantity' => 1, 'unit_price' => 50, 'tax_rate' => 15],
        ], 'fixed', 30);

        $this->assertEqualsWithDelta(30, $result['discount_amount'], 0.001);
        $this->assertEqualsWithDelta(33, $result['tax_total'], 0.001);
        $this->assertEqualsWithDelta(253, $result['grand_total'], 0.001);
    }

    public function test_fixed_discount_is_capped_at_subtotal(): void
    {
        $result = $this->calculator->calculate([
            ['description' => 'A', 'quantity' => 1, 'unit_price' => 250, 'tax_rate' => 15],
        ], 'fixed', 1000);

        $this->assertEqualsWithDelta(250, $result['discount_amount'], 0.001);
        $this->assertEqualsWithDelta(0, $result['tax_total'], 0.001);
        $this->assertEqualsWithDelta(0, $result['grand_total'], 0.001);
    }

    public function test_it_allocates_discount_across_mixed_tax_rates(): void
    {
        $result = $this->calculator->calculate([
            ['description' => 'A', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 15],
            ['description' => 'B', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 5],
        ], 'percent', 10);

        $this->assertEqualsWithDelta(20, $result['discount_amount'], 0.001);
        $this->assertEqualsWithDelta(18, $result['tax_total'], 0.001);
        $this->assertEqualsWithDelta(198, $result['grand_total'], 0.001);
        $this->assertEqualsWithDelta(13.5, $result['items'][0]['line_tax'], 0.001);
        $this->assertEqualsWithDelta(4.5, $result['items'][1]['line_tax'], 0.001);
    }

    public function test_it_handles_empty_items(): void
    {
        $result = $this->calculator->calculate([], 'percent', 10);

        $this->assertEqualsWithDelta(0, $result['items_subtotal'], 0.001);
        $this->assertEqualsWithDelta(0, $result['discount_amount'], 0.001);
        $this->assertEqualsWithDelta(0, $result['tax_total'], 0.001);
        $this->assertEqualsWithDelta(0, $result['grand_total'], 0.001);
        $this->assertSame([], $result['items']);
    }
}
