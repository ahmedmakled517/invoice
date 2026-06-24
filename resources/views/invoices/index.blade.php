@extends('layouts.app')

@section('title', 'كل المستندات')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="card-title mb-0">كل المستندات</h1>
            <a href="{{ route('invoices.create') }}" class="btn btn-brand btn-sm">+ مستند جديد</a>
        </div>

        <form method="GET" action="{{ route('invoices.index') }}" class="search-bar mb-3">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.4a7.25 7.25 0 1 1-14.5 0 7.25 7.25 0 0 1 14.5 0Z" />
            </svg>
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="ابحث برقم المستند، العميل، النوع، التاريخ، أو الإجمالي...">
            @if ($search !== '')
                <a href="{{ route('invoices.index') }}" class="search-clear" title="مسح البحث">&times;</a>
            @endif
            <button type="submit" class="btn btn-brand">بحث</button>
        </form>

        @if ($search !== '')
            <div class="mb-3 text-muted small">
                نتائج البحث عن "<strong>{{ $search }}</strong>" — {{ $invoices->total() }} نتيجة
            </div>
        @endif

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
            <p class="text-muted mb-0">
                @if ($search !== '')
                    لا توجد نتائج مطابقة لبحثك.
                @else
                    لا توجد مستندات بعد. ابدأ بإنشاء مستند جديد.
                @endif
            </p>
        @endif
    </div>
</div>
@endsection
