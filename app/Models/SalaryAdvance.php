<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryAdvance extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'work_id',
        'employee_id',
        'payroll_competency_id',
        'advance_date',
        'amount',
        'status',
        'payment_method',
        'pix_key_type',
        'pix_key',
        'pix_holder_name',
        'pix_holder_document',
        'notes',
        'created_by',
        'paid_by',
        'paid_at',
        'integrated_payroll_at',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'integrated_payroll_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function competency()
    {
        return $this->belongsTo(PayrollCompetency::class, 'payroll_competency_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}