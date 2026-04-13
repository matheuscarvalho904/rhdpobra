<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeVariableEvent extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_event_id',
        'payroll_competency_id',
        'amount',
        'quantity',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollEvent(): BelongsTo
    {
        return $this->belongsTo(PayrollEvent::class);
    }

    public function payrollCompetency(): BelongsTo
    {
        return $this->belongsTo(PayrollCompetency::class);
    }
}