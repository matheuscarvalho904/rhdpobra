<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeContract;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmployeeContractLifecycleService
{
    public function activateContract(EmployeeContract $contract): void
    {
        DB::transaction(function () use ($contract) {
            $employee = $contract->employee;

            if (! $employee) {
                throw new RuntimeException('Contrato sem colaborador vinculado.');
            }

            EmployeeContract::query()
                ->where('employee_id', $employee->id)
                ->where('id', '!=', $contract->id)
                ->update([
                    'is_current' => false,
                ]);

            $contract->update([
                'status' => 'ativo',
                'is_current' => true,
            ]);

            $employee->update([
                'status' => 'active',
                'is_active' => true,
                'termination_date' => null,
            ]);
        });
    }

    public function putInNotice(EmployeeContract $contract): void
    {
        DB::transaction(function () use ($contract) {
            $employee = $contract->employee;

            if (! $employee) {
                throw new RuntimeException('Contrato sem colaborador vinculado.');
            }

            $contract->update([
                'status' => 'em_aviso',
                'is_current' => true,
            ]);

            $employee->update([
                'status' => 'em_aviso',
                'is_active' => true,
            ]);
        });
    }

    public function terminateContract(EmployeeContract $contract, ?string $terminationDate = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($contract, $terminationDate, $reason) {
            $employee = $contract->employee;

            if (! $employee) {
                throw new RuntimeException('Contrato sem colaborador vinculado.');
            }

            $contract->update([
                'status' => 'desligado',
                'is_current' => false,
                'termination_date' => $terminationDate ?: now()->toDateString(),
                'termination_reason' => $reason,
            ]);

            $employee->update([
                'status' => 'terminated',
                'is_active' => false,
                'termination_date' => $terminationDate ?: now()->toDateString(),
            ]);
        });
    }
}