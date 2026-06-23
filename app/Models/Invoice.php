<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'type',
        'number',
        'customer_id',
        'issue_date',
        'due_date',
        'valid_until',
        'discount_type',
        'discount_value',
        'currency',
        'items_subtotal',
        'discount_amount',
        'tax_total',
        'grand_total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'      => 'date',
            'due_date'        => 'date',
            'valid_until'     => 'date',
            'discount_value'  => 'decimal:2',
            'items_subtotal'  => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_total'       => 'decimal:2',
            'grand_total'     => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function typeLabel(): string
    {
        return $this->type === 'quotation' ? 'عرض سعر' : 'فاتورة';
    }

    public function isQuotation(): bool
    {
        return $this->type === 'quotation';
    }
}
