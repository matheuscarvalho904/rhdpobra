<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWorkShift extends Model
{
    protected $fillable = [
        'employee_id',
        'work_shift_id',
        'start_date',
        'end_date',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'work_shift_id' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }
}