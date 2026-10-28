<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'work_order_id',
        'customer_id',
        'invoice_date',
        'due_date',
        'subtotal_services',
        'subtotal_parts',
        'subtotal',
        'discount',
        'tax_percentage',
        'tax_amount',
        'grand_total',
        'paid_amount',
        'balance_due',
        'status', // unpaid, partially_paid, paid, cancelled
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal_services' => 'decimal:2',
            'subtotal_parts' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'unpaid' => '<span class="badge bg-danger">Belum Lunas</span>',
            'partially_paid' => '<span class="badge bg-warning text-dark">Sebagian</span>',
            'paid' => '<span class="badge bg-success">Lunas</span>',
            'cancelled' => '<span class="badge bg-secondary">Dibatalkan</span>',
            default => '<span class="badge bg-secondary">'.e($this->status).'</span>',
        };
    }
}
