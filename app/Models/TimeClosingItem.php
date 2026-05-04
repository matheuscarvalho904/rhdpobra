<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeClosingItem extends Model
{
    protected $fillable = [
        'time_closing_id',
        'employee_id',
        'worked_hours',
        'expected_hours',
        'overtime_hours',
        'delay_hours',
        'absence_days',
        'entries_count',
        'days_with_entries',
        'status',
        'notes',
        'daily_summary',
    ];

    protected $casts = [
        'daily_summary' => 'array',
    ];

    public function closing(): BelongsTo
    {
        return $this->belongsTo(TimeClosing::class, 'time_closing_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}