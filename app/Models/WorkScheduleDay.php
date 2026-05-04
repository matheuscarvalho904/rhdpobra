<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkScheduleDay extends Model
{
    protected $fillable = [
        'work_schedule_id',
        'weekday',
        'is_working_day',
        'first_start',
        'first_end',
        'second_start',
        'second_end',
        'expected_hours',
        'overtime_50_after_hours',
        'overtime_100_after_hours',
        'holiday_keeps_schedule',
        'holiday_generates_overtime_100',
        'entry_tolerance_minutes',
        'exit_tolerance_minutes',
        'settings',
    ];

    protected $casts = [
        'weekday' => 'integer',
        'is_working_day' => 'boolean',
        'expected_hours' => 'decimal:2',
        'overtime_50_after_hours' => 'decimal:2',
        'overtime_100_after_hours' => 'decimal:2',
        'holiday_keeps_schedule' => 'boolean',
        'holiday_generates_overtime_100' => 'boolean',
        'settings' => 'array',
    ];

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }
}