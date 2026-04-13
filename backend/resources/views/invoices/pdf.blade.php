<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #171717; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 6px; text-align: right; border: 1px solid #ebebeb; }
        th { background: #fafafa; }
        .muted { color: #666; font-size: 11px; }
        .totals { margin-top: 16px; width: 40%; margin-right: auto; margin-left: 0; }
    </style>
</head>
<body>
<h1>فاتورة ضريبية</h1>
<p class="muted">رقم الفاتورة: {{ $invoice->invoice_number }}</p>
<p class="muted">تاريخ الإصدار: {{ $invoice->created_at?->format('Y-m-d') }}</p>
@if($invoice->due_date)
    <p class="muted">تاريخ الاستحقاق: {{ $invoice->due_date->format('Y-m-d') }}</p>
@endif

<table>
    <thead>
    <tr>
        <th>الوصف</th>
        <th>الكمية</th>
        <th>سعر الوحدة</th>
        <th>الإجمالي</th>
    </tr>
    </thead>
    <tbody>
    @foreach($invoice->items as $item)
        <tr>
            <td>{{ $item->description_ar ?: $item->description_en }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format((float) $item->unit_price, 2) }}</td>
            <td>{{ number_format((float) $item->line_total, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>المجموع قبل الضريبة</td>
        <td>{{ number_format((float) $invoice->subtotal, 2) }} ر.س</td>
    </tr>
    <tr>
        <td>ضريبة القيمة المضافة ({{ $invoice->vat_percentage }}%)</td>
        <td>{{ number_format((float) $invoice->vat_amount, 2) }} ر.س</td>
    </tr>
    <tr>
        <td><strong>الإجمالي</strong></td>
        <td><strong>{{ number_format((float) $invoice->total, 2) }} ر.س</strong></td>
    </tr>
</table>

@if(!empty($qrDataUri))
    <div style="margin-top: 24px; text-align: center;">
        <p class="muted">رمز الاستجابة السريعة — ZATCA Phase 1</p>
        <img src="{{ $qrDataUri }}" alt="QR" width="160" height="160"/>
    </div>
@endif
</body>
</html>
