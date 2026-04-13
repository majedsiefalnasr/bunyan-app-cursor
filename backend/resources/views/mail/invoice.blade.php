<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة</title>
</head>
<body style="font-family: sans-serif;">
<p>رقم الفاتورة: <strong>{{ $invoice->invoice_number }}</strong></p>
<p>الإجمالي: <strong>{{ number_format((float) $invoice->total, 2) }} ر.س</strong></p>
<p>يمكنك مراجعة التفاصيل من لوحة التحكم في منصة بنيان.</p>
</body>
</html>
