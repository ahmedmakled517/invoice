@php($cur = config('invoice.currency_label'))
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: xbriyaz, sans-serif; font-size: 12px; color: #1f2937; direction: rtl; }
        .title { font-size: 22px; font-weight: bold; color: #2563eb; }
        .muted { color: #6b7280; font-size: 11px; line-height: 1.6; }
        .box { width: 100%; border-collapse: collapse; }
        .box td { vertical-align: top; }
        .section-title { font-size: 13px; font-weight: bold; margin: 14px 0 6px; color: #374151; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.items th { background: #eef2ff; border: 1px solid #c7d2fe; padding: 7px; text-align: center; font-weight: bold; }
        table.items td { border: 1px solid #d1d5db; padding: 7px; text-align: center; }
        table.items td.desc { text-align: right; }
        table.totals { width: 45%; border-collapse: collapse; margin-top: 12px; }
        table.totals td { padding: 5px 8px; }
        table.totals td.label { color: #6b7280; }
        table.totals td.val { text-align: left; font-weight: bold; }
        .grand td { font-size: 15px; color: #2563eb; border-top: 2px solid #e5e7eb; }
        .notes { margin-top: 16px; font-size: 11px; color: #4b5563; }
    </style>
</head>
<body dir="rtl">
    <table class="box" dir="rtl">
        <tr>
            <td style="width:60%">
                <img src="{{ str_replace('\\', '/', public_path('images/logo.png')) }}" width="52" height="52" style="vertical-align: middle;">
                <span class="title" style="vertical-align: middle;">{{ config('invoice.company.name') }}</span>
                <div class="muted">
                    الرقم الضريبي: {{ config('invoice.company.tax_number') }}<br>
                    {{ config('invoice.company.phone') }} — {{ config('invoice.company.email') }}<br>
                    {{ config('invoice.company.address') }}
                </div>
            </td>
            <td style="width:40%; text-align:left">
                <div class="title" style="font-size:18px; color:#111827">{{ $invoice->typeLabel() }}</div>
                <div class="muted">
                    رقم المستند: {{ $invoice->number }}<br>
                    التاريخ: {{ $invoice->issue_date->format('Y-m-d') }}<br>
                    @if ($invoice->isQuotation() && $invoice->valid_until)
                        صالح حتى: {{ $invoice->valid_until->format('Y-m-d') }}
                    @elseif (! $invoice->isQuotation() && $invoice->due_date)
                        تاريخ الاستحقاق: {{ $invoice->due_date->format('Y-m-d') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">بيانات العميل</div>
    <div class="muted">
        <strong style="color:#111827">{{ $invoice->customer->name }}</strong><br>
        @if ($invoice->customer->tax_number) الرقم الضريبي: {{ $invoice->customer->tax_number }}<br> @endif
        @if ($invoice->customer->phone) {{ $invoice->customer->phone }}<br> @endif
        @if ($invoice->customer->address) {{ $invoice->customer->address }} @endif
    </div>

    <table class="items" dir="rtl">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>الوصف</th>
                <th style="width:70px">الكمية</th>
                <th style="width:90px">سعر الوحدة</th>
                <th style="width:70px">الضريبة %</th>
                <th style="width:100px">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="desc">{{ $item->description }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->tax_rate, 2) }}%</td>
                    <td>{{ number_format($item->line_subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals" align="left" dir="rtl">
        <tr>
            <td class="label">الإجمالي قبل الخصم</td>
            <td class="val">{{ number_format($invoice->items_subtotal, 2) }} {{ $cur }}</td>
        </tr>
        <tr>
            <td class="label">الخصم</td>
            <td class="val">- {{ number_format($invoice->discount_amount, 2) }} {{ $cur }}</td>
        </tr>
        <tr>
            <td class="label">الضريبة</td>
            <td class="val">{{ number_format($invoice->tax_total, 2) }} {{ $cur }}</td>
        </tr>
        <tr class="grand">
            <td class="label">الإجمالي النهائي</td>
            <td class="val">{{ number_format($invoice->grand_total, 2) }} {{ $cur }}</td>
        </tr>
    </table>

    @if ($invoice->notes)
        <div class="notes"><strong>ملاحظات:</strong> {{ $invoice->notes }}</div>
    @endif
</body>
</html>
