<?php

namespace App\Services;

use App\Models\EmployeeContract;
use App\Models\EmployeeTermination;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmployeeNoticeService
{
    public function startNotice(EmployeeContract $contract, array $data): EmployeeTermination
    {
        return DB::transaction(function () use ($contract, $data) {
            if ($contract->isTerminated()) {
                throw new RuntimeException('O contrato já está desligado.');
            }

            if ($contract->terminations()->whereIn('status', ['draft', 'in_progress'])->exists()) {
                throw new RuntimeException('Já existe um desligamento em aberto para este contrato.');
            }

            $noticeType = $data['notice_type'] ?? null;
            $noticeStartDate = $data['notice_start_date'] ?? null;
            $noticeDays = (int) ($data['notice_days'] ?? 30);

            $noticeEndDate = null;
            $projectedEndDate = null;
            $lastWorkedDate = $data['last_worked_date'] ?? null;

            if ($noticeType === 'worked' && $noticeStartDate) {
                $noticeEndDate = now()->parse($noticeStartDate)->addDays($noticeDays - 1)->format('Y-m-d');
                $projectedEndDate = $noticeEndDate;
                $lastWorkedDate = $lastWorkedDate ?: $noticeEndDate;
            }

            if ($noticeType === 'indemnified') {
                $projectedEndDate = $data['projected_end_date']
                    ?? now()->parse($data['termination_date'])->addDays($noticeDays)->format('Y-m-d');
            }

            $termination = EmployeeTermination::create([
                'employee_id' => $contract->employee_id,
                'employee_contract_id' => $contract->id,
                'termination_date' => $data['termination_date'],
                'last_worked_date' => $lastWorkedDate,
                'dismissal_type' => $data['dismissal_type'] ?? null,
                'termination_reason' => $data['termination_reason'] ?? null,
                'notice_type' => $noticeType,
                'notice_start_date' => $noticeStartDate,
                'notice_end_date' => $noticeEndDate,
                'notice_days' => $noticeDays,
                'projected_end_date' => $projectedEndDate,
                'reduction_type' => $data['reduction_type'] ?? 'none',
                'is_notice_projected' => (bool) ($data['is_notice_projected'] ?? false),
                'notice_amount' => $data['notice_amount'] ?? 0,
                'termination_amount' => $data['termination_amount'] ?? 0,
                'status' => 'in_progress',
                'notes' => $data['notes'] ?? null,
            ]);

            $contract->update([
                'status' => 'em_aviso',
                'is_active' => true,
                'termination_reason' => $data['termination_reason'] ?? null,
            ]);

            $contract->employee->update([
                'status' => 'em_aviso',
                'is_active' => true,
            ]);

            return $termination;
        });
    }

    public function closeTermination(EmployeeTermination $termination): void
    {
        DB::transaction(function () use ($termination) {
            $contract = $termination->contract;
            $employee = $termination->employee;

            $contract->update([
                'status' => 'desligado',
                'is_active' => false,
                'is_current' => false,
                'termination_date' => $termination->termination_date,
                'termination_reason' => $termination->termination_reason,
            ]);

            $employee->update([
                'status' => 'desligado',
                'is_active' => false,
                'termination_date' => $termination->termination_date,
            ]);

            $termination->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);
        });
    }
}