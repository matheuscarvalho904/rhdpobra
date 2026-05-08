<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTimeBankSetting extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'use_company_rules',
        'time_bank_enabled',
        'overtime_destination',
        'monthly_bank_limit',
        'excess_to_payroll',
        'compensate_delays_with_balance',
        'allow_negative_balance',
        'settings',
    ];

    protected $casts = [
        'use_company_rules' => 'boolean',
        'time_bank_enabled' => 'boolean',
        'excess_to_payroll' => 'boolean',
        'compensate_delays_with_balance' => 'boolean',
        'allow_negative_balance' => 'boolean',
        'monthly_bank_limit' => 'decimal:2',
        'settings' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}