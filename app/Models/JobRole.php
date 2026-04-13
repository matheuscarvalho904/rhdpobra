<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRole extends Model
{
    protected $fillable = [
        'cbo_code_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cbo_code_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function cboCode(): BelongsTo
    {
        return $this->belongsTo(CboCode::class);
    }
}