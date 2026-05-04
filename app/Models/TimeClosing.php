<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeClosing extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'employee_count',
        'total_worked_hours',
        'total_overtime_hours',
        'total_delay_hours',
        'total_absence_days',
        'processed_at',
        'closed_at',
        'notes',
        'metadata',
        'payroll_competency_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'processed_at' => 'datetime',
        'closed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TimeClosingItem::class);
    }
    public function payrollCompetency()
{
    return $this->belongsTo(PayrollCompetency::class);
}
}