<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeContract;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmployeeRehireService
{
    public function __construct(
        protected EmployeeContractLifecycleService $contractLifecycleService,
    ) {}

    public function rehire(Employee $employee, array $data): EmployeeContract
    {
        return DB::transaction(function () use ($employee, $data) {
            if (! $employee->exists) {
                throw new RuntimeException('Colaborador inválido para recontratação.');
            }

            $nextSequence = EmployeeContract::query()
                ->where('employee_id', $employee->id)
                ->max('contract_sequence');

            $nextSequence = ((int) $nextSequence) + 1;

            EmployeeContract::query()
                ->where('employee_id', $employee->id)
                ->update(['is_current' => false]);

            $registrationNumber = $data['registration_number']
                ?? (($employee->code ?: str_pad((string) $employee->id, 4, '0', STR_PAD_LEFT)) . '-' . str_pad((string) $nextSequence, 2, '0', STR_PAD_LEFT));

            $contract = EmployeeContract::create([
                'employee_id' => $employee->id,
                'company_id' => $data['company_id'] ?? $employee->company_id,
                'branch_id' => $data['branch_id'] ?? $employee->branch_id,
                'work_id' => $data['work_id'] ?? $employee->work_id,
                'department_id' => $data['department_id'] ?? $employee->department_id,
                'job_role_id' => $data['job_role_id'] ?? $employee->job_role_id,
                'cost_center_id' => $data['cost_center_id'] ?? $employee->cost_center_id,
                'contract_type_id' => $data['contract_type_id'] ?? $employee->contract_type_id,
                'work_shift_id' => $data['work_shift_id'] ?? $employee->work_shift_id,
                'registration_number' => $registrationNumber,
                'contract_sequence' => $nextSequence,
                'status' => 'ativo',
                'is_current' => true,
                'admission_date' => $data['admission_date'] ?? now()->toDateString(),
                'termination_date' => null,
                'salary' => $data['salary'] ?? $employee->salary ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $employee->update([
                'company_id' => $contract->company_id,
                'branch_id' => $contract->branch_id,
                'work_id' => $contract->work_id,
                'department_id' => $contract->department_id,
                'job_role_id' => $contract->job_role_id,
                'cost_center_id' => $contract->cost_center_id,
                'contract_type_id' => $contract->contract_type_id,
                'work_shift_id' => $contract->work_shift_id,
                'admission_date' => $contract->admission_date,
                'termination_date' => null,
                'salary' => $contract->salary,
                'status' => 'active',
                'is_active' => true,
            ]);

            $this->contractLifecycleService->activateContract($contract);

            return $contract;
        });
    }
}