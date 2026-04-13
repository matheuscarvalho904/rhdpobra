<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFixedEvent extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_event_id',
        'amount',
        'quantity',
        'is_active',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'decimal:2',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollEvent(): BelongsTo
    {
        return $this->belongsTo(PayrollEvent::class);
    }
}