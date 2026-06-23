@extends('layouts.app')

@section('title', $invoice->number)

@section('content')
@php($cur = config('invoice.currency_label'))

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">{{ $invoice->typeLabel() }} — {{ $invoice->number }}</h1>
    <a href="{{ route('invoices.create') }}" class="btn btn-light btn-sm">+ مستند جديد</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2 class="card-title mb-1">{{ config('invoice.company.name') }}</h2>
                <div class="text-muted small">
                    الرقم الضريبي: {{ config('invoice.company.tax_number') }}<br>
                    {{ config('invoice.company.phone') }} — {{ config('invoice.company.email') }}<br>
                    {{ config('invoice.company.address') }}
                </div>
            </div>
            <div class="col-md-6 text-md-start mt-3 mt-md-0">
                <div><strong>التاريخ:</strong> {{ $invoice->issue_date->format('Y-m-d') }}</div>
                @if ($invoice->isQuotation() && $invoice->valid_until)
                    <div><strong>صالح حتى:</strong> {{ $invoice->valid_until->format('Y-m-d') }}</div>
                @elseif (! $invoice->isQuotation() && $invoice->due_date)
                    <div><strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date->format('Y-m-d') }}</div>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <h3 class="h6 text-muted">بيانات العميل</h3>
            <div class="fw-semibold">{{ $invoice->customer->name }}</div>
            @if ($invoice->customer->tax_number)
                <div class="text-muted small">الرقم الضريبي: {{ $invoice->customer->tax_number }}</div>
            @endif
            @if ($invoice->customer->phone)
                <div class="text-muted small">{{ $invoice->customer->phone }}</div>
            @endif
            @if ($invoice->customer->address)
                <div class="text-muted small">{{ $invoice->customer->address }}</div>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>الوصف</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>الضريبة %</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ number_format($item->tax_rate, 2) }}%</td>
                            <td class="fw-semibold">{{ number_format($item->line_subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="totals-row">
                    <span>الإجمالي قبل الخصم</span>
                    <span>{{ number_format($invoice->items_subtotal, 2) }} {{ $cur }}</span>
                </div>
                <div class="totals-row text-danger">
                    <span>الخصم</span>
                    <span>- {{ number_format($invoice->discount_amount, 2) }} {{ $cur }}</span>
                </div>
                <div class="totals-row">
                    <span>الضريبة</span>
                    <span>{{ number_format($invoice->tax_total, 2) }} {{ $cur }}</span>
                </div>
                <div class="totals-row totals-grand">
                    <span>الإجمالي النهائي</span>
                    <span>{{ number_format($invoice->grand_total, 2) }} {{ $cur }}</span>
                </div>
            </div>
        </div>

        @if ($invoice->notes)
            <div class="mt-4">
                <strong>ملاحظات:</strong>
                <p class="mb-0 text-muted">{{ $invoice->notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
