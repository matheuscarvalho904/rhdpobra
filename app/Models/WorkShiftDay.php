<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkShiftDay extends Model
{
    protected $fillable = [
        'work_shift_id',
        'week_day',
        'start_time',
        'break_start',
        'break_end',
        'end_time',
        'expected_hours',
        'is_off',
    ];

    protected function casts(): array
    {
        return [
            'work_shift_id' => 'integer',
            'week_day' => 'integer',
            'expected_hours' => 'decimal:2',
            'is_off' => 'boolean',
        ];
    }

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }
}