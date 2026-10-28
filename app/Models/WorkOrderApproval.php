<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'version',
        'estimated_amount',
        'approved_at',
        'approval_method', // in_person, phone_wa, digital_signature
        'recorded_by',
        'signature_data',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->approval_method) {
            'in_person' => 'Tatap Muka Langsung',
            'phone_wa' => 'Konfirmasi Telepon / WhatsApp',
            'digital_signature' => 'Tanda Tangan Digital',
            default => $this->approval_method,
        };
    }
}
