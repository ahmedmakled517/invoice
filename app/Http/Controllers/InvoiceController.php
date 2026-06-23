<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceCalculator;
use Illuminate\Support\Facades\DB;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('customer')->latest()->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('invoices.create', [
            'customers'      => Customer::orderBy('name')->get(),
            'defaultTaxRate' => config('invoice.default_tax_rate'),
            'currencyLabel'  => config('invoice.currency_label'),
        ]);
    }

    public function store(StoreInvoiceRequest $request, InvoiceCalculator $calculator)
    {
        $data = $request->validated();

        $totals = $calculator->calculate(
            $data['items'],
            $data['discount_type'],
            (float) $data['discount_value'],
        );

        $invoice = DB::transaction(function () use ($data, $totals) {
            $invoice = Invoice::create([
                'type'            => $data['type'],
                'number'          => $this->generateNumber($data['type']),
                'customer_id'     => $data['customer_id'],
                'issue_date'      => $data['issue_date'],
                'due_date'        => $data['due_date'] ?? null,
                'valid_until'     => $data['valid_until'] ?? null,
                'discount_type'   => $data['discount_type'],
                'discount_value'  => $data['discount_value'],
                'currency'        => config('invoice.currency'),
                'items_subtotal'  => $totals['items_subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_total'       => $totals['tax_total'],
                'grand_total'     => $totals['grand_total'],
                'notes'           => $data['notes'] ?? null,
            ]);

            $invoice->items()->createMany($totals['items']);

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'تم حفظ المستند بنجاح برقم ' . $invoice->number);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'items');

        return view('invoices.show', compact('invoice'));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('customer', 'items');

        $tempDir = storage_path('app/mpdf');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $defaults = (new ConfigVariables())->getDefaults();
        $fontDefaults = (new FontVariables())->getDefaults();

        $mpdf = new Mpdf([
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'directionality' => 'rtl',
            'tempDir'        => $tempDir,
            'default_font'   => 'amiri',
            'fontDir'        => array_merge($defaults['fontDir'], [storage_path('fonts')]),
            'fontdata'       => $fontDefaults['fontdata'] + [
                'amiri' => [
                    'R' => 'Amiri-Regular.ttf',
                    'B' => 'Amiri-Bold.ttf',
                ],
            ],
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoArabic = true;

        $mpdf->WriteHTML(view('pdf.invoice', compact('invoice'))->render());

        $filename = $invoice->number . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function generateNumber(string $type): string
    {
        $prefix = config("invoice.number_prefix.{$type}", 'DOC');
        $year = now()->year;

        $sequence = Invoice::where('type', $type)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('%s-%d-%04d', $prefix, $year, $sequence);
    }
}
