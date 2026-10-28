<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'wo_number',
        'reception_id',
        'customer_id',
        'vehicle_id',
        'mileage',
        'customer_complaint',
        'inspection_result',
        'estimated_cost',
        'actual_total_cost',
        'status', // draft, waiting_approval, approved, in_progress, waiting_parts, final_inspection, completed, invoiced, closed, cancelled
        'start_time',
        'completion_time',
        'notes',
        'cancellation_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'mileage' => 'integer',
            'estimated_cost' => 'decimal:2',
            'actual_total_cost' => 'decimal:2',
            'start_time' => 'datetime',
            'completion_time' => 'datetime',
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

    public function reception(): BelongsTo
    {
        return $this->belongsTo(ServiceReception::class, 'reception_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class)->where('item_type', 'service');
    }

    public function partItems(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class)->where('item_type', 'part');
    }

    public function mechanics(): BelongsToMany
    {
        return $this->belongsToMany(Mechanic::class, 'work_order_mechanics')
            ->withPivot('role_description', 'started_at', 'finished_at', 'notes')
            ->withTimestamps();
    }

    public function workOrderMechanics(): HasMany
    {
        return $this->hasMany(WorkOrderMechanic::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(WorkOrderApproval::class);
    }

    public function latestApproval(): HasOne
    {
        return $this->hasOne(WorkOrderApproval::class)->latestOfMany();
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(WorkOrderInspection::class);
    }

    public function latestInspection(): HasOne
    {
        return $this->hasOne(WorkOrderInspection::class)->latestOfMany();
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'waiting_approval' => '<span class="badge bg-warning text-dark">Menunggu Persetujuan</span>',
            'approved' => '<span class="badge bg-info text-dark">Disetujui</span>',
            'in_progress' => '<span class="badge bg-primary">Sedang Dikerjakan</span>',
            'waiting_parts' => '<span class="badge bg-warning">Menunggu Spare Part</span>',
            'final_inspection' => '<span class="badge bg-purple text-white" style="background-color: #6f42c1;">Pemeriksaan Akhir</span>',
            'completed' => '<span class="badge bg-success">Selesai</span>',
            'invoiced' => '<span class="badge bg-teal text-white" style="background-color: #20c997;">Ditagihkan</span>',
            'closed' => '<span class="badge bg-dark">Ditutup</span>',
            'cancelled' => '<span class="badge bg-danger">Dibatalkan</span>',
            default => '<span class="badge bg-secondary">'.e($this->status).'</span>',
        };
    }
}
