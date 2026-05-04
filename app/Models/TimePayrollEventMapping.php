<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimePayrollEventMapping extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'payroll_event_id',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payrollEvent(): BelongsTo
    {
        return $this->belongsTo(PayrollEvent::class);
    }
}