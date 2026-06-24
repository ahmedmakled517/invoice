<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceCalculator;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(InvoiceCalculator $calculator): void
    {
        $customers = Customer::all();

        if ($customers->isEmpty()) {
            return;
        }

        $year = now()->year;

        $samples = [
            [
                'type'           => 'invoice',
                'number'         => "INV-{$year}-0001",
                'customer_id'    => $customers[0]->id,
                'discount_type'  => 'percent',
                'discount_value' => 10,
                'due_date'       => now()->addDays(15)->toDateString(),
                'notes'          => 'شكراً لتعاملكم معنا.',
                'items'          => [
                    ['description' => 'تصميم وتطوير موقع إلكتروني', 'quantity' => 2, 'unit_price' => 1500, 'tax_rate' => 15],
                    ['description' => 'استضافة سنوية', 'quantity' => 1, 'unit_price' => 600, 'tax_rate' => 15],
                    ['description' => 'صيانة شهرية', 'quantity' => 3, 'unit_price' => 250, 'tax_rate' => 15],
                ],
            ],
            [
                'type'           => 'quotation',
                'number'         => "QUO-{$year}-0001",
                'customer_id'    => $customers[1]->id,
                'discount_type'  => 'fixed',
                'discount_value' => 200,
                'valid_until'    => now()->addDays(30)->toDateString(),
                'notes'          => 'العرض ساري لمدة 30 يوماً من تاريخ الإصدار.',
                'items'          => [
                    ['description' => 'حملة تسويق رقمي متكاملة', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 15],
                    ['description' => 'إدارة حسابات التواصل الاجتماعي', 'quantity' => 2, 'unit_price' => 1200, 'tax_rate' => 15],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $totals = $calculator->calculate($sample['items'], $sample['discount_type'], (float) $sample['discount_value']);

            $invoice = Invoice::create([
                'type'            => $sample['type'],
                'number'          => $sample['number'],
                'customer_id'     => $sample['customer_id'],
                'issue_date'      => now()->toDateString(),
                'due_date'        => $sample['due_date'] ?? null,
                'valid_until'     => $sample['valid_until'] ?? null,
                'discount_type'   => $sample['discount_type'],
                'discount_value'  => $sample['discount_value'],
                'currency'        => config('invoice.currency'),
                'items_subtotal'  => $totals['items_subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_total'       => $totals['tax_total'],
                'grand_total'     => $totals['grand_total'],
                'notes'           => $sample['notes'],
            ]);

            $invoice->items()->createMany($totals['items']);
        }
    }
}
