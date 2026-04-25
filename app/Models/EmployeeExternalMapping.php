<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeExternalMapping extends Model
{
    protected $fillable = [
        'employee_id',
        'provider',
        'external_employee_id',
        'external_code',
        'external_name',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isSolides(): bool
    {
        return $this->provider === 'solides';
    }
}