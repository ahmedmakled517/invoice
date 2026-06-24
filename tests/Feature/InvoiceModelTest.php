<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InvoiceModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_has_many_invoices(): void
    {
        $customer = Customer::factory()->create();
        Invoice::factory()->count(2)->create(['customer_id' => $customer->id]);

        $this->assertCount(2, $customer->invoices);
        $this->assertInstanceOf(Invoice::class, $customer->invoices->first());
    }

    public function test_invoice_belongs_to_customer(): void
    {
        $invoice = Invoice::factory()->create();

        $this->assertInstanceOf(Customer::class, $invoice->customer);
    }

    public function test_invoice_has_many_items(): void
    {
        $invoice = Invoice::factory()->create();
        InvoiceItem::factory()->count(3)->create(['invoice_id' => $invoice->id]);

        $this->assertCount(3, $invoice->items);
        $this->assertInstanceOf(InvoiceItem::class, $invoice->items->first());
    }

    public function test_item_belongs_to_invoice(): void
    {
        $item = InvoiceItem::factory()->create();

        $this->assertInstanceOf(Invoice::class, $item->invoice);
    }

    public function test_deleting_invoice_cascades_to_items(): void
    {
        $invoice = Invoice::factory()->create();
        InvoiceItem::factory()->count(2)->create(['invoice_id' => $invoice->id]);

        $invoice->delete();

        $this->assertDatabaseCount('invoice_items', 0);
    }

    public function test_type_label_and_is_quotation_helpers(): void
    {
        $invoice = Invoice::factory()->create(['type' => 'invoice']);
        $quotation = Invoice::factory()->quotation()->create();

        $this->assertSame('فاتورة', $invoice->typeLabel());
        $this->assertFalse($invoice->isQuotation());

        $this->assertSame('عرض سعر', $quotation->typeLabel());
        $this->assertTrue($quotation->isQuotation());
    }

    public function test_date_and_decimal_casts(): void
    {
        $invoice = Invoice::factory()->create([
            'issue_date'     => '2026-01-01',
            'grand_total'    => 100,
        ]);

        $this->assertInstanceOf(Carbon::class, $invoice->issue_date);
        $this->assertSame('100.00', $invoice->grand_total);
    }
}
