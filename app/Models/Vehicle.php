<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'license_plate',
        'brand',
        'model',
        'year',
        'color',
        'vin_number',
        'engine_number',
        'last_mileage',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_mileage' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(ServiceReception::class);
    }

    public function getFullDetailsAttribute(): string
    {
        return "{$this->license_plate} - {$this->brand} {$this->model} ({$this->color})";
    }
}
