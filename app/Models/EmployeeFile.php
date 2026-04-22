<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFile extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'file_name',
        'file_path',
        'generated_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}