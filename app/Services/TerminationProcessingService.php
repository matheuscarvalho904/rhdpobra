<?php

namespace App\Services;

use App\Models\EmployeeTermination;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TerminationProcessingService
{
    public function __construct(
        protected TerminationCalculationService $terminationCalculationService,
        protected EmployeeContractLifecycleService $contractLifecycleService,
    ) {}

    public function calculate(EmployeeTermination $termination, array $context = []): array
    {
        $termination->loadMissing(['employee', 'contract']);

        $employee = $termination->employee;
        $contract = $termination->contract;

        if (! $employee || ! $contract) {
            throw new RuntimeException('Desligamento sem colaborador ou contrato vinculado.');
        }

        return $this->terminationCalculationService->calculate(
            employee: $employee,
            termination: $termination,
            context: $context,
        );
    }

    public function process(EmployeeTermination $termination, array $context = []): array
    {
        if ($termination->status === 'closed') {
            throw new RuntimeException('Este desligamento já está fechado.');
        }

        return DB::transaction(function () use ($termination, $context) {
            $result = $this->calculate($termination, $context);

            $termination->update([
                'notice_amount' => $result['notice_amount'] ?? 0,
                'termination_amount' => $result['net_amount'] ?? 0,
                'status' => 'in_progress',
            ]);

            return $result;
        });
    }

    public function processAndClose(EmployeeTermination $termination, array $context = []): array
    {
        if ($termination->status === 'closed') {
            throw new RuntimeException('Este desligamento já está fechado.');
        }

        return DB::transaction(function () use ($termination, $context) {
            $result = $this->process($termination, $context);

            $termination->loadMissing(['employee', 'contract']);

            $contract = $termination->contract;
            $employee = $termination->employee;

            if (! $employee || ! $contract) {
                throw new RuntimeException('Desligamento sem colaborador ou contrato vinculado.');
            }

            $this->contractLifecycleService->terminateContract(
                contract: $contract,
                terminationDate: optional($termination->termination_date)->format('Y-m-d'),
                reason: $termination->termination_reason,
            );

            $termination->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            return $result;
        });
    }
}