<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'schedule_type',
        'is_active',
        'works_on_holidays',
        'uses_time_bank',
        'daily_tolerance_minutes',
        'monthly_tolerance_minutes',
        'weekly_hours',
        'monthly_hours',
        'notes',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'works_on_holidays' => 'boolean',
        'uses_time_bank' => 'boolean',
        'weekly_hours' => 'decimal:2',
        'monthly_hours' => 'decimal:2',
        'settings' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(WorkScheduleDay::class);
    }

    public function employeeSchedules(): HasMany
    {
        return $this->hasMany(EmployeeWorkSchedule::class);
    }
}