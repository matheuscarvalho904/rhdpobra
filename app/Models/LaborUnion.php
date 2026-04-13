<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaborUnion extends Model
{
    protected $fillable = [
        'name',
        'code',
        'document',
        'phone',
        'email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}