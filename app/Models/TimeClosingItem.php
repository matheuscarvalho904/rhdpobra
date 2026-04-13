<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeClosingItem extends Model
{
    protected $fillable = [
        'time_closing_id',
        'employee_id',
        'expected_minutes',
        'worked_minutes',
        'overtime_50_minutes',
        'overtime_100_minutes',
        'lateness_minutes',
        'absence_minutes',
        'night_minutes',
        'hour_bank_credit_minutes',
        'hour_bank_debit_minutes',
        'dsr_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'time_closing_id' => 'integer',
            'employee_id' => 'integer',
            'expected_minutes' => 'integer',
            'worked_minutes' => 'integer',
            'overtime_50_minutes' => 'integer',
            'overtime_100_minutes' => 'integer',
            'lateness_minutes' => 'integer',
            'absence_minutes' => 'integer',
            'night_minutes' => 'integer',
            'hour_bank_credit_minutes' => 'integer',
            'hour_bank_debit_minutes' => 'integer',
            'dsr_minutes' => 'integer',
        ];
    }

    public function timeClosing(): BelongsTo
    {
        return $this->belongsTo(TimeClosing::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}