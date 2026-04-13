<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'company_id',
        'branch_id',
        'work_id',

        'salary_base',
        'total_gross',
        'deduction_total',
        'net_total',

        'base_inss',
        'inss',

        'base_fgts',
        'fgts',

        'base_irrf',
        'irrf',

        'printed_at',
        'sent_at',
        'file_path',
    ];

    protected $casts = [
        'salary_base' => 'decimal:2',
        'total_gross' => 'decimal:2',
        'deduction_total' => 'decimal:2',
        'net_total' => 'decimal:2',

        'base_inss' => 'decimal:2',
        'inss' => 'decimal:2',

        'base_fgts' => 'decimal:2',
        'fgts' => 'decimal:2',

        'base_irrf' => 'decimal:2',
        'irrf' => 'decimal:2',

        'printed_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

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
}