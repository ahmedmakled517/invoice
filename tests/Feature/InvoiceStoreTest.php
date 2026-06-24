<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceStoreTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'type'           => 'invoice',
            'customer_id'    => Customer::factory()->create()->id,
            'issue_date'     => '2026-01-01',
            'discount_type'  => 'percent',
            'discount_value' => 10,
            'items'          => [
                ['description' => 'بند أول', 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 15],
                ['description' => 'بند ثانٍ', 'quantity' => 1, 'unit_price' => 50, 'tax_rate' => 15],
            ],
        ], $overrides);
    }

    public function test_it_stores_invoice_and_recalculates_totals_ignoring_client_values(): void
    {
        $response = $this->post(route('invoices.store'), $this->validPayload([
            'items_subtotal' => 999999,
            'tax_total'      => 0,
            'grand_total'    => 1,
        ]));

        $invoice = Invoice::first();

        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertEqualsWithDelta(250, $invoice->items_subtotal, 0.001);
        $this->assertEqualsWithDelta(25, $invoice->discount_amount, 0.001);
        $this->assertEqualsWithDelta(33.75, $invoice->tax_total, 0.001);
        $this->assertEqualsWithDelta(258.75, $invoice->grand_total, 0.001);
    }

    public function test_it_persists_line_items_with_computed_values(): void
    {
        $this->post(route('invoices.store'), $this->validPayload());

        $invoice = Invoice::with('items')->first();

        $this->assertCount(2, $invoice->items);
        $this->assertEqualsWithDelta(200, $invoice->items[0]->line_subtotal, 0.001);
        $this->assertEqualsWithDelta(27, $invoice->items[0]->line_tax, 0.001);
    }

    public function test_it_generates_sequential_document_numbers(): void
    {
        $this->post(route('invoices.store'), $this->validPayload());
        $this->post(route('invoices.store'), $this->validPayload());

        $numbers = Invoice::orderBy('id')->pluck('number')->all();

        $this->assertSame('INV-2026-0001', $numbers[0]);
        $this->assertSame('INV-2026-0002', $numbers[1]);
    }

    public function test_it_stores_quotation_with_valid_until(): void
    {
        $this->post(route('invoices.store'), $this->validPayload([
            'type'        => 'quotation',
            'valid_until' => '2026-02-01',
        ]));

        $invoice = Invoice::first();

        $this->assertSame('quotation', $invoice->type);
        $this->assertStringStartsWith('QUO-', $invoice->number);
        $this->assertSame('2026-02-01', $invoice->valid_until->toDateString());
    }

    public function test_it_applies_fixed_discount(): void
    {
        $this->post(route('invoices.store'), $this->validPayload([
            'discount_type'  => 'fixed',
            'discount_value' => 30,
        ]));

        $invoice = Invoice::first();

        $this->assertEqualsWithDelta(30, $invoice->discount_amount, 0.001);
        $this->assertEqualsWithDelta(253, $invoice->grand_total, 0.001);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->from(route('invoices.create'))->post(route('invoices.store'), []);

        $response->assertRedirect(route('invoices.create'));
        $response->assertSessionHasErrors(['type', 'customer_id', 'issue_date', 'discount_type', 'items']);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_it_rejects_invoice_without_items(): void
    {
        $response = $this->post(route('invoices.store'), $this->validPayload(['items' => []]));

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_it_rejects_unknown_customer(): void
    {
        $response = $this->post(route('invoices.store'), $this->validPayload(['customer_id' => 9999]));

        $response->assertSessionHasErrors('customer_id');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_it_caps_percentage_discount_at_100(): void
    {
        $response = $this->post(route('invoices.store'), $this->validPayload([
            'discount_type'  => 'percent',
            'discount_value' => 150,
        ]));

        $response->assertSessionHasErrors('discount_value');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_it_rejects_due_date_before_issue_date(): void
    {
        $response = $this->post(route('invoices.store'), $this->validPayload([
            'issue_date' => '2026-01-10',
            'due_date'   => '2026-01-01',
        ]));

        $response->assertSessionHasErrors('due_date');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_it_rejects_invalid_item_quantity(): void
    {
        $response = $this->post(route('invoices.store'), $this->validPayload([
            'items' => [
                ['description' => 'بند', 'quantity' => 0, 'unit_price' => 100, 'tax_rate' => 15],
            ],
        ]));

        $response->assertSessionHasErrors('items.0.quantity');
        $this->assertDatabaseCount('invoices', 0);
    }
}
