<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TimeBank;
use App\Models\TimeBankMovement;
use App\Models\TimeClosing;
use App\Models\PayrollCompetency;
use Illuminate\Support\Facades\DB;

class TimeBankService
{
    public function getOrCreateBank(Employee $employee): TimeBank
    {
        return TimeBank::firstOrCreate(
            [
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
            ],
            [
                'positive_balance_hours' => 0,
                'negative_balance_hours' => 0,
                'net_balance_hours' => 0,
                'is_active' => true,
                'settings' => [],
            ]
        );
    }

    public function credit(
        Employee $employee,
        float $hours,
        ?TimeClosing $closing = null,
        ?PayrollCompetency $competency = null,
        ?string $description = null,
        array $metadata = []
    ): TimeBankMovement {
        return DB::transaction(function () use ($employee, $hours, $closing, $competency, $description, $metadata) {
            $hours = round(abs($hours), 2);

            $bank = $this->getOrCreateBank($employee);

            $newBalance = round((float) $bank->net_balance_hours + $hours, 2);

            $movement = TimeBankMovement::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'time_bank_id' => $bank->id,
                'time_closing_id' => $closing?->id,
                'payroll_competency_id' => $competency?->id,
                'type' => 'credit',
                'origin' => $closing ? 'time_closing' : 'manual',
                'hours' => $hours,
                'balance_after' => $newBalance,
                'movement_date' => now()->toDateString(),
                'expires_at' => null,
                'status' => 'confirmed',
                'description' => $description ?: 'Crédito automático de banco de horas.',
                'metadata' => $metadata,
            ]);

            $this->recalculate($bank);

            return $movement;
        });
    }

    public function debit(
        Employee $employee,
        float $hours,
        ?TimeClosing $closing = null,
        ?PayrollCompetency $competency = null,
        ?string $description = null,
        array $metadata = []
    ): TimeBankMovement {
        return DB::transaction(function () use ($employee, $hours, $closing, $competency, $description, $metadata) {
            $hours = round(abs($hours), 2);

            $bank = $this->getOrCreateBank($employee);

            $newBalance = round((float) $bank->net_balance_hours - $hours, 2);

            $movement = TimeBankMovement::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'time_bank_id' => $bank->id,
                'time_closing_id' => $closing?->id,
                'payroll_competency_id' => $competency?->id,
                'type' => 'debit',
                'origin' => $closing ? 'time_closing' : 'manual',
                'hours' => $hours,
                'balance_after' => $newBalance,
                'movement_date' => now()->toDateString(),
                'expires_at' => null,
                'status' => 'confirmed',
                'description' => $description ?: 'Débito automático de banco de horas.',
                'metadata' => $metadata,
            ]);

            $this->recalculate($bank);

            return $movement;
        });
    }

    public function compensateDelay(
        Employee $employee,
        float $delayHours,
        ?TimeClosing $closing = null,
        ?PayrollCompetency $competency = null
    ): array {
        $bank = $this->getOrCreateBank($employee);

        $available = max(0, (float) $bank->net_balance_hours);
        $delayHours = round(abs($delayHours), 2);

        $compensated = min($available, $delayHours);
        $remaining = round($delayHours - $compensated, 2);

        if ($compensated > 0) {
            $this->debit(
                employee: $employee,
                hours: $compensated,
                closing: $closing,
                competency: $competency,
                description: 'Compensação automática de atraso com saldo de banco de horas.',
                metadata: [
                    'source' => 'delay_compensation',
                    'delay_hours' => $delayHours,
                    'compensated_hours' => $compensated,
                    'remaining_hours' => $remaining,
                ]
            );
        }

        return [
            'delay_hours' => $delayHours,
            'compensated_hours' => $compensated,
            'remaining_hours' => $remaining,
        ];
    }

    public function applyOvertime(
        Employee $employee,
        float $overtimeHours,
        string $destination,
        float $monthlyLimit = 20,
        bool $excessToPayroll = true,
        ?TimeClosing $closing = null,
        ?PayrollCompetency $competency = null
    ): array {
        $overtimeHours = round(abs($overtimeHours), 2);
        $monthlyLimit = round(abs($monthlyLimit), 2);

        if ($overtimeHours <= 0 || $destination === 'payroll') {
            return [
                'bank_hours' => 0.0,
                'payroll_hours' => $overtimeHours,
            ];
        }

        if ($destination === 'time_bank') {
            $this->credit(
                employee: $employee,
                hours: $overtimeHours,
                closing: $closing,
                competency: $competency,
                description: 'Crédito de horas extras no banco de horas.',
                metadata: [
                    'source' => 'overtime_to_time_bank',
                    'overtime_hours' => $overtimeHours,
                ]
            );

            return [
                'bank_hours' => $overtimeHours,
                'payroll_hours' => 0.0,
            ];
        }

        if ($destination === 'mixed') {
            $bankHours = min($overtimeHours, $monthlyLimit);
            $payrollHours = round($overtimeHours - $bankHours, 2);

            if ($bankHours > 0) {
                $this->credit(
                    employee: $employee,
                    hours: $bankHours,
                    closing: $closing,
                    competency: $competency,
                    description: 'Crédito híbrido de horas extras no banco de horas.',
                    metadata: [
                        'source' => 'mixed_overtime',
                        'overtime_hours' => $overtimeHours,
                        'bank_hours' => $bankHours,
                        'payroll_hours' => $payrollHours,
                    ]
                );
            }

            if (! $excessToPayroll) {
                $payrollHours = 0.0;
            }

            return [
                'bank_hours' => $bankHours,
                'payroll_hours' => $payrollHours,
            ];
        }

        return [
            'bank_hours' => 0.0,
            'payroll_hours' => $overtimeHours,
        ];
    }

    public function clearClosingMovements(TimeClosing $closing): void
    {
        TimeBankMovement::query()
            ->where('time_closing_id', $closing->id)
            ->where('origin', 'time_closing')
            ->delete();

        TimeBank::query()
            ->where('company_id', $closing->company_id)
            ->get()
            ->each(fn (TimeBank $bank) => $this->recalculate($bank));
    }

    public function recalculate(TimeBank $bank): TimeBank
    {
        $credits = TimeBankMovement::query()
            ->where('time_bank_id', $bank->id)
            ->where('status', 'confirmed')
            ->whereIn('type', ['credit', 'adjustment'])
            ->sum('hours');

        $debits = TimeBankMovement::query()
            ->where('time_bank_id', $bank->id)
            ->where('status', 'confirmed')
            ->whereIn('type', ['debit', 'payout', 'expiration'])
            ->sum('hours');

        $net = round((float) $credits - (float) $debits, 2);

        $bank->update([
            'positive_balance_hours' => $net > 0 ? $net : 0,
            'negative_balance_hours' => $net < 0 ? abs($net) : 0,
            'net_balance_hours' => $net,
            'last_movement_at' => now(),
        ]);

        return $bank->refresh();
    }
}