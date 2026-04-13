<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTransfer extends Model
{
    protected $fillable = [
        'employee_id',
        'old_company_id',
        'new_company_id',
        'old_branch_id',
        'new_branch_id',
        'old_work_id',
        'new_work_id',
        'old_department_id',
        'new_department_id',
        'old_cost_center_id',
        'new_cost_center_id',
        'old_job_role_id',
        'new_job_role_id',
        'transfer_date',
        'reason',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'old_company_id' => 'integer',
            'new_company_id' => 'integer',
            'old_branch_id' => 'integer',
            'new_branch_id' => 'integer',
            'old_work_id' => 'integer',
            'new_work_id' => 'integer',
            'old_department_id' => 'integer',
            'new_department_id' => 'integer',
            'old_cost_center_id' => 'integer',
            'new_cost_center_id' => 'integer',
            'old_job_role_id' => 'integer',
            'new_job_role_id' => 'integer',
            'transfer_date' => 'date',
            'created_by' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function oldCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'old_company_id');
    }

    public function newCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'new_company_id');
    }

    public function oldBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'old_branch_id');
    }

    public function newBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'new_branch_id');
    }

    public function oldWork(): BelongsTo
    {
        return $this->belongsTo(Work::class, 'old_work_id');
    }

    public function newWork(): BelongsTo
    {
        return $this->belongsTo(Work::class, 'new_work_id');
    }

    public function oldDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'old_department_id');
    }

    public function newDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'new_department_id');
    }

    public function oldCostCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'old_cost_center_id');
    }

    public function newCostCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'new_cost_center_id');
    }

    public function oldJobRole(): BelongsTo
    {
        return $this->belongsTo(JobRole::class, 'old_job_role_id');
    }

    public function newJobRole(): BelongsTo
    {
        return $this->belongsTo(JobRole::class, 'new_job_role_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}