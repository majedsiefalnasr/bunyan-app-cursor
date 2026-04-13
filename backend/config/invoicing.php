<?php

return [
    'seller_name' => env('INVOICING_SELLER_NAME', 'Bunyan'),
    'vat_number' => env('INVOICING_VAT_NUMBER', '300000000000003'),
    'default_vat_rate' => (float) env('INVOICING_DEFAULT_VAT_RATE', 15),
    'due_days' => (int) env('INVOICING_DUE_DAYS', 14),
];
