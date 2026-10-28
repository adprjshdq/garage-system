<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type',
        'prefix',
        'current_sequence',
        'year',
        'month',
    ];

    protected function casts(): array
    {
        return [
            'current_sequence' => 'integer',
            'year' => 'integer',
            'month' => 'integer',
        ];
    }
}
