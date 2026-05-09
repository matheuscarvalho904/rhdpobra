<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeVariableEvent;
use App\Models\PayrollCompetency;
use App\Models\PayrollEvent;
use App\Models\TimeBank;
use App\Models\TimeBankMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TimeBankPayoutService
{
    public function payout(
        Employee $employee,
        PayrollCompetency $competency,
        float $hours,
        ?string $description = null,
    ): void {

        DB::transaction(function () use (
            $employee,
            $competency,
            $hours,
            $description
        ) {

            $timeBank = TimeBank::query()
                ->firstOrCreate(
                    [
                        'company_id' => $employee->company_id,
                        'employee_id' => $employee->id,
                    ],
                    [
                        'positive_balance_hours' => 0,
                        'negative_balance_hours' => 0,
                        'net_balance_hours' => 0,
                        'is_active' => true,
                    ]
                );

            if ((float) $timeBank->net_balance_hours < $hours) {
                throw new RuntimeException(
                    'Saldo insuficiente no banco de horas.'
                );
            }

            $salary = (float) (
                $employee->currentContract?->salary
                ?? $employee->salary
                ?? 0
            );

            if ($salary <= 0) {
                throw new RuntimeException(
                    'Colaborador sem salário configurado.'
                );
            }

            $hourValue = round($salary / 220, 2);

            $amount = round($hourValue * $hours, 2);

            $payrollEvent = PayrollEvent::query()
                ->where('code', 'BANCO_HORAS_PAGO')
                ->first();

            if (! $payrollEvent) {
                throw new RuntimeException(
                    'Evento BANCO_HORAS_PAGO não encontrado.'
                );
            }

            $newBalance = round(
                (float) $timeBank->net_balance_hours - $hours,
                2
            );

            $timeBank->update([
                'positive_balance_hours' => max(0, $newBalance),
                'net_balance_hours' => $newBalance,
                'last_movement_at' => now(),
            ]);

            TimeBankMovement::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'time_bank_id' => $timeBank->id,

                'payroll_competency_id' => $competency->id,

                'type' => 'payout',

                'origin' => 'payroll',

                'hours' => -$hours,

                'balance_after' => $newBalance,

                'movement_date' => now()->toDateString(),

                'status' => 'confirmed',

                'description' => $description
                    ?: "Pagamento de {$hours}h banco de horas.",

                'metadata' => [
                    'hour_value' => $hourValue,
                    'amount' => $amount,
                ],
            ]);

            EmployeeVariableEvent::updateOrCreate(

                [
                    'employee_id' => $employee->id,
                    'payroll_event_id' => $payrollEvent->id,
                    'payroll_competency_id' => $competency->id,
                ],

                [
                    'amount' => $amount,
                    'quantity' => $hours,
                    'reference' => $hours,
                    'notes' => $description
                        ?: "Pagamento banco de horas ({$hours}h).",
                ]
            );
        });
    }
}