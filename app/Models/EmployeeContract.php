<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeContract extends Model
{
    protected $fillable = [
        'employee_id',
        'company_id',
        'branch_id',
        'work_id',
        'department_id',
        'job_role_id',
        'cost_center_id',
        'contract_type_id',
        'work_shift_id',
        'registration_number',
        'contract_sequence',
        'admission_date',
        'termination_date',
        'salary',
        'status',
        'termination_reason',
        'is_active',
        'is_current',
        'notes',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'termination_date' => 'date',
        'salary' => 'decimal:2',
        'is_active' => 'boolean',
        'is_current' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(JobRole::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function terminations(): HasMany
    {
        return $this->hasMany(EmployeeTermination::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'ativo';
    }

    public function isInNotice(): bool
    {
        return $this->status === 'em_aviso';
    }

    public function isTerminated(): bool
    {
        return $this->status === 'desligado';
    }
}