<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceOccurrence extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'affects_payroll',
        'affects_hour_bank',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'affects_payroll' => 'boolean',
            'affects_hour_bank' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }
}