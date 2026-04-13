<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'requires_expiration',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requires_expiration' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}