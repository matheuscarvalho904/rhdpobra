<?php

namespace App\Services;

use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EmployeeReportService
{
    /*
    |--------------------------------------------------------------------------
    | COLABORADORES POR OBRA
    |--------------------------------------------------------------------------
    */
    public function getEmployeesByWork(array $filters = []): Collection
    {
        return Employee::query()
            ->with([
                'company',
                'branch',
                'work',
                'jobRole',
                'contractType',
            ])
            ->when($filters['company_id'] ?? null, fn (Builder $q, $v) => $q->where('company_id', $v))
            ->when($filters['branch_id'] ?? null, fn (Builder $q, $v) => $q->where('branch_id', $v))
            ->when($filters['work_id'] ?? null, fn (Builder $q, $v) => $q->where('work_id', $v))
            ->when($filters['job_role_id'] ?? null, fn (Builder $q, $v) => $q->where('job_role_id', $v))
            ->when($filters['contract_type_id'] ?? null, fn (Builder $q, $v) => $q->where('contract_type_id', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->orderBy('work_id')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($employee) => $employee->work?->name ?? 'Sem Obra');
    }

    public function generateEmployeesByWorkPdf(array $filters = [])
    {
        $groupedEmployees = $this->getEmployeesByWork($filters);

        return Pdf::loadView('pdf.reports.employees-by-work-report', [
            'groupedEmployees' => $groupedEmployees,
            'filters' => $filters,
        ])->setPaper('a4', 'portrait');
    }

    /*
    |--------------------------------------------------------------------------
    | COLABORADORES GERAL
    |--------------------------------------------------------------------------
    */
    public function getEmployeesGeneral(array $filters = []): Collection
    {
        return Employee::query()
            ->with([
                'company',
                'branch',
                'work',
                'jobRole',
                'contractType',
            ])
            ->when($filters['company_id'] ?? null, fn (Builder $q, $v) => $q->where('company_id', $v))
            ->when($filters['branch_id'] ?? null, fn (Builder $q, $v) => $q->where('branch_id', $v))
            ->when($filters['work_id'] ?? null, fn (Builder $q, $v) => $q->where('work_id', $v))
            ->when($filters['job_role_id'] ?? null, fn (Builder $q, $v) => $q->where('job_role_id', $v))
            ->when($filters['contract_type_id'] ?? null, fn (Builder $q, $v) => $q->where('contract_type_id', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($filters['admission_date_start'] ?? null, fn (Builder $q, $v) => $q->whereDate('admission_date', '>=', $v))
            ->when($filters['admission_date_end'] ?? null, fn (Builder $q, $v) => $q->whereDate('admission_date', '<=', $v))
            ->orderBy('name')
            ->get();
    }

    public function generateEmployeesGeneralPdf(array $filters = [])
    {
        $employees = $this->getEmployeesGeneral($filters);

        return Pdf::loadView('pdf.reports.employees-general-report', [
            'employees' => $employees,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');
    }

    /*
    |--------------------------------------------------------------------------
    | COLABORADORES EM AVISO
    |--------------------------------------------------------------------------
    */
    public function getEmployeesInNotice(array $filters = []): Collection
    {
        return Employee::query()
            ->with([
                'company',
                'branch',
                'work',
                'jobRole',
                'contractType',
                'currentContract',
            ])
            ->whereHas('currentContract', function (Builder $query) use ($filters) {
                $query
                    ->where('is_current', true)
                    ->where('status', 'em_aviso')
                    ->when($filters['company_id'] ?? null, fn (Builder $q, $v) => $q->where('company_id', $v))
                    ->when($filters['branch_id'] ?? null, fn (Builder $q, $v) => $q->where('branch_id', $v))
                    ->when($filters['work_id'] ?? null, fn (Builder $q, $v) => $q->where('work_id', $v));
            })
            ->when($filters['company_id'] ?? null, fn (Builder $q, $v) => $q->where('company_id', $v))
            ->when($filters['branch_id'] ?? null, fn (Builder $q, $v) => $q->where('branch_id', $v))
            ->when($filters['work_id'] ?? null, fn (Builder $q, $v) => $q->where('work_id', $v))
            ->when($filters['job_role_id'] ?? null, fn (Builder $q, $v) => $q->where('job_role_id', $v))
            ->when($filters['contract_type_id'] ?? null, fn (Builder $q, $v) => $q->where('contract_type_id', $v))
            ->orderBy('name')
            ->get();
    }

    public function generateEmployeesInNoticePdf(array $filters = [])
    {
        $employees = $this->getEmployeesInNotice($filters);

        return Pdf::loadView('pdf.reports.employees-in-notice-report', [
            'employees' => $employees,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');
    }
}