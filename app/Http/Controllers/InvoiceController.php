<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $invoices = Invoice::with('customer')
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('issue_date', 'like', "%{$search}%")
                    ->orWhere('grand_total', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('tax_number', 'like', "%{$search}%"));

                if (str_contains('فاتورة', $search) || str_contains('invoice', mb_strtolower($search))) {
                    $q->orWhere('type', 'invoice');
                }

                if (str_contains('عرض سعر', $search) || str_contains('quotation', mb_strtolower($search))) {
                    $q->orWhere('type', 'quotation');
                }
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', compact('invoices', 'search'));
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

        $mpdf = new Mpdf([
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'directionality' => 'rtl',
            'tempDir'        => $tempDir,
            'default_font'   => 'xbriyaz',
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
