<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_create(): void
    {
        $this->get('/')->assertRedirect(route('invoices.create'));
    }

    public function test_create_page_loads_with_customers(): void
    {
        $customer = Customer::factory()->create(['name' => 'عميل القائمة']);

        $response = $this->get(route('invoices.create'));

        $response->assertOk();
        $response->assertViewIs('invoices.create');
        $response->assertViewHas('customers', fn ($customers) => $customers->contains('id', $customer->id));
    }

    public function test_index_lists_documents(): void
    {
        Invoice::factory()->create(['number' => 'INV-2026-0007']);

        $response = $this->get(route('invoices.index'));

        $response->assertOk();
        $response->assertViewIs('invoices.index');
        $response->assertSee('INV-2026-0007');
    }

    public function test_show_displays_invoice_with_items(): void
    {
        $invoice = Invoice::factory()->create(['number' => 'INV-2026-0009']);
        InvoiceItem::factory()->create([
            'invoice_id'  => $invoice->id,
            'description' => 'بند العرض',
        ]);

        $response = $this->get(route('invoices.show', $invoice));

        $response->assertOk();
        $response->assertViewIs('invoices.show');
        $response->assertSee('INV-2026-0009');
        $response->assertSee('بند العرض');
    }

    public function test_show_returns_404_for_missing_invoice(): void
    {
        $this->get('/invoices/9999')->assertNotFound();
    }
}
