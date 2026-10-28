<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'inspector_id',
        'inspected_at',
        'brake_check', // ok, need_attention, bad
        'engine_check',
        'electric_check',
        'tires_check',
        'test_drive_notes',
        'is_passed',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'inspected_at' => 'datetime',
            'is_passed' => 'boolean',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
