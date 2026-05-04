<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeBankMovement extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'time_bank_id',
        'time_closing_id',
        'payroll_competency_id',
        'type',
        'origin',
        'hours',
        'balance_after',
        'movement_date',
        'expires_at',
        'status',
        'description',
        'metadata',
    ];

    protected $casts = [
        'hours' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'movement_date' => 'date',
        'expires_at' => 'date',
        'metadata' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function timeBank(): BelongsTo
    {
        return $this->belongsTo(TimeBank::class);
    }

    public function timeClosing(): BelongsTo
    {
        return $this->belongsTo(TimeClosing::class);
    }

    public function payrollCompetency(): BelongsTo
    {
        return $this->belongsTo(PayrollCompetency::class);
    }
}