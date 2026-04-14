<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAccountMapping extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'work_id',
        'cost_center_id',
        'payroll_event_id',
        'event_code',
        'event_type',
        'debit_account',
        'credit_account',
        'history_template',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function payrollEvent(): BelongsTo
    {
        return $this->belongsTo(PayrollEvent::class);
    }
}