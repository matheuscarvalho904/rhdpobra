<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    protected $table = 'payroll_runs';

    protected $fillable = [
        'payroll_competency_id',
        'company_id',
        'branch_id',
        'work_id',
        'run_type',
        'description',
        'status',
        'total_gross',
        'total_discounts',
        'total_net',
        'total_fgts',
        'processed_employees',
        'error_message',
        'processed_at',
        'closed_at',
        'processed_by',
        'closed_by',
        'notes',
    ];

    protected $casts = [
        'total_gross' => 'decimal:2',
        'total_discounts' => 'decimal:2',
        'total_net' => 'decimal:2',
        'total_fgts' => 'decimal:2',
        'processed_employees' => 'integer',
        'processed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected $attributes = [
        'run_type' => 'payroll_clt',
        'status' => 'open',
        'total_gross' => 0,
        'total_discounts' => 0,
        'total_net' => 0,
        'total_fgts' => 0,
        'processed_employees' => 0,
    ];

    public function payrollCompetency(): BelongsTo
    {
        return $this->belongsTo(PayrollCompetency::class, 'payroll_competency_id');
    }

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

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollRunItem::class, 'payroll_run_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isError(): bool
    {
        return $this->status === 'error';
    }

    public function isCltRun(): bool
    {
        return $this->run_type === 'payroll_clt';
    }

    public function isApprenticeRun(): bool
    {
        return $this->run_type === 'payroll_apprentice';
    }

    public function isInternshipRun(): bool
    {
        return $this->run_type === 'internship_payment';
    }

    public function isRpaRun(): bool
    {
        return $this->run_type === 'payroll_rpa';
    }

    public function isAccountsPayableRun(): bool
    {
        return $this->run_type === 'accounts_payable';
    }
}