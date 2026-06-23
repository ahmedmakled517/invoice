@extends('layouts.app')

@section('title', 'كل المستندات')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="card-title mb-0">كل المستندات</h1>
            <a href="{{ route('invoices.create') }}" class="btn btn-brand btn-sm">+ مستند جديد</a>
        </div>

        @if ($invoices->count())
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الرقم</th>
                            <th>النوع</th>
                            <th>العميل</th>
                            <th>تاريخ الإصدار</th>
                            <th>الإجمالي</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td class="fw-semibold">{{ $invoice->number }}</td>
                                <td>{{ $invoice->typeLabel() }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->issue_date->format('Y-m-d') }}</td>
                                <td>{{ number_format($invoice->grand_total, 2) }} {{ config('invoice.currency_label') }}</td>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $invoices->links() }}</div>
        @else
            <p class="text-muted mb-0">لا توجد مستندات بعد. ابدأ بإنشاء مستند جديد.</p>
        @endif
    </div>
</div>
@endsection
