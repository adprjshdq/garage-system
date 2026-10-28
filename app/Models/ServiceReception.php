<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceReception extends Model
{
    use HasFactory;

    protected $fillable = [
        'reception_number',
        'customer_id',
        'vehicle_id',
        'receiver_id',
        'mileage_in',
        'customer_complaint',
        'initial_condition',
        'accessories_note',
        'photo_path',
        'reception_time',
        'estimated_completion_time',
        'status', // waiting_inspection, in_inspection, processed, completed, cancelled
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'mileage_in' => 'integer',
            'reception_time' => 'datetime',
            'estimated_completion_time' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class, 'reception_id');
    }
}
