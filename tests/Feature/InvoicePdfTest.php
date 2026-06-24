<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_an_inline_pdf(): void
    {
        $invoice = Invoice::factory()->create(['number' => 'INV-2026-0100']);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->get(route('invoices.pdf', $invoice));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_pdf_returns_404_for_missing_invoice(): void
    {
        $this->get('/invoices/9999/pdf')->assertNotFound();
    }
}
