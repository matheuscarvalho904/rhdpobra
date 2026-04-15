<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTermination extends Model
{
    protected $fillable = [
        'employee_id',
        'employee_contract_id',
        'termination_date',
        'last_worked_date',
        'dismissal_type',
        'termination_reason',
        'notice_type',
        'notice_start_date',
        'notice_end_date',
        'notice_days',
        'projected_end_date',
        'reduction_type',
        'is_notice_projected',
        'notice_amount',
        'termination_amount',
        'status',
        'notes',
        'closed_at',
    ];

    protected $casts = [
        'termination_date' => 'date',
        'last_worked_date' => 'date',
        'notice_start_date' => 'date',
        'notice_end_date' => 'date',
        'projected_end_date' => 'date',
        'is_notice_projected' => 'boolean',
        'notice_amount' => 'decimal:2',
        'termination_amount' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(EmployeeContract::class, 'employee_contract_id');
    }

    public function isWorkedNotice(): bool
    {
        return $this->notice_type === 'worked';
    }

    public function isIndemnifiedNotice(): bool
    {
        return $this->notice_type === 'indemnified';
    }
}