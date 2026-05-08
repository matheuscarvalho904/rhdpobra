<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyTimeBankSetting extends Model
{
    protected $fillable = [
        'company_id',
        'enabled',
        'default_overtime_destination',
        'monthly_bank_limit',
        'excess_to_payroll',
        'compensate_delays_with_balance',
        'allow_negative_balance',
        'expiration_days',
        'settings',
    ];

    protected $casts = [
        'enabled' => 'boolean',
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
}