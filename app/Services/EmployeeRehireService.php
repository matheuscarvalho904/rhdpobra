<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeContract;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmployeeRehireService
{
    public function rehire(Employee $employee, array $data): EmployeeContract
    {
        return DB::transaction(function () use ($employee, $data) {
            $currentContract = $employee->currentContract;

            if ($currentContract && ! $currentContract->isTerminated()) {
                throw new RuntimeException('O colaborador já possui contrato ativo ou em aviso.');
            }

            $lastSequence = (int) $employee->contracts()->max('contract_sequence');
            $newSequence = $lastSequence + 1;

            $registrationNumber = $this->generateRegistrationNumber($employee, $newSequence);

            $employee->contracts()->where('is_current', true)->update([
                'is_current' => false,
            ]);

            $contract = EmployeeContract::create([
                'employee_id' => $employee->id,
                'company_id' => $data['company_id'] ?? $employee->company_id ?? null,
                'branch_id' => $data['branch_id'] ?? $employee->branch_id ?? null,
                'work_id' => $data['work_id'] ?? $employee->work_id ?? null,
                'department_id' => $data['department_id'] ?? $employee->department_id ?? null,
                'job_role_id' => $data['job_role_id'] ?? $employee->job_role_id ?? null,
                'cost_center_id' => $data['cost_center_id'] ?? $employee->cost_center_id ?? null,
                'contract_type_id' => $data['contract_type_id'] ?? $employee->contract_type_id ?? null,
                'work_shift_id' => $data['work_shift_id'] ?? $employee->work_shift_id ?? null,
                'registration_number' => $registrationNumber,
                'contract_sequence' => $newSequence,
                'admission_date' => $data['admission_date'],
                'termination_date' => null,
                'salary' => $data['salary'] ?? $employee->salary ?? 0,
                'status' => 'ativo',
                'termination_reason' => null,
                'is_active' => true,
                'is_current' => true,
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
                'salary' => $contract->salary,
                'status' => 'ativo',
                'is_active' => true,
                'termination_date' => null,
            ]);

            return $contract;
        });
    }

    protected function generateRegistrationNumber(Employee $employee, int $sequence): string
    {
        $base = $employee->code ?: str_pad((string) $employee->id, 5, '0', STR_PAD_LEFT);

        return $sequence === 1
            ? $base
            : $base . '-' . $sequence;
    }
}