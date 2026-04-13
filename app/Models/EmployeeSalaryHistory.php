<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryHistory extends Model
{
    protected $fillable = [
        'employee_id',
        'salary_type',
        'previous_salary',
        'new_salary',
        'effective_date',
        'reason',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'previous_salary' => 'decimal:2',
            'new_salary' => 'decimal:2',
            'effective_date' => 'date',
            'created_by' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}