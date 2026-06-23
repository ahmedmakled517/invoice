<?php

return [

    'company' => [
        'name'       => env('INVOICE_COMPANY_NAME', 'شركة المثال للتجارة'),
        'tax_number' => env('INVOICE_COMPANY_TAX_NUMBER', '300000000000003'),
        'phone'      => env('INVOICE_COMPANY_PHONE', '+966 11 000 0000'),
        'email'      => env('INVOICE_COMPANY_EMAIL', 'info@example.com'),
        'address'    => env('INVOICE_COMPANY_ADDRESS', 'الرياض، المملكة العربية السعودية'),
    ],

    'currency'       => env('INVOICE_CURRENCY', 'SAR'),
    'currency_label' => env('INVOICE_CURRENCY_LABEL', 'ر.س'),

    'default_tax_rate' => (float) env('INVOICE_DEFAULT_TAX_RATE', 15),

    'number_prefix' => [
        'invoice'   => 'INV',
        'quotation' => 'QUO',
    ],

];
