<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TimeBank;
use App\Models\TimeBankMovement;
use App\Models\TimeClosing;
use Illuminate\Support\Facades\DB;

class TimeBankService
{
    public function applyFromClosing(TimeClosing $closing): void
    {
        DB::transaction(function () use ($closing) {
            $closing->loadMissing('items.employee');

            TimeBankMovement::query()
                ->where('time_closing_id', $closing->id)
                ->where('origin', 'time_closing')
                ->delete();

            foreach ($closing->items as $item) {
                if (! $item->employee) {
                    continue;
                }

                $balance = round(
                    (float) $item->worked_hours - (float) $item->expected_hours,
                    2
                );

                if ($balance == 0.0) {
                    continue;
                }

                if ($balance > 0) {
                    $this->credit(
                        employee: $item->employee,
                        hours: $balance,
                        closing: $closing,
                        description: "Crédito gerado pelo fechamento de ponto #{$closing->id}"
                    );
                }

                if ($balance < 0) {
                    $this->debit(
                        employee: $item->employee,
                        hours: abs($balance),
                        closing: $closing,
                        description: "Débito gerado pelo fechamento de ponto #{$closing->id}"
                    );
                }
            }
        });
    }

    public function credit(Employee $employee, float $hours, ?TimeClosing $closing = null, ?string $description = null): TimeBankMovement
    {
        return $this->movement(
            employee: $employee,
            type: 'credit',
            hours: abs($hours),
            closing: $closing,
            description: $description
        );
    }

    public function debit(Employee $employee, float $hours, ?TimeClosing $closing = null, ?string $description = null): TimeBankMovement
    {
        return $this->movement(
            employee: $employee,
            type: 'debit',
            hours: abs($hours),
            closing: $closing,
            description: $description
        );
    }

    protected function movement(
        Employee $employee,
        string $type,
        float $hours,
        ?TimeClosing $closing = null,
        ?string $description = null
    ): TimeBankMovement {
        return DB::transaction(function () use ($employee, $type, $hours, $closing, $description) {
            $bank = TimeBank::query()->firstOrCreate(
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

            $signedHours = $type === 'credit'
                ? abs($hours)
                : -abs($hours);

            $newBalance = round((float) $bank->net_balance_hours + $signedHours, 2);

            $movement = TimeBankMovement::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'time_bank_id' => $bank->id,
                'time_closing_id' => $closing?->id,
                'payroll_competency_id' => $closing?->payroll_competency_id,
                'type' => $type,
                'origin' => $closing ? 'time_closing' : 'manual',
                'hours' => round(abs($hours), 2),
                'balance_after' => $newBalance,
                'movement_date' => $closing?->end_date?->toDateString() ?? now()->toDateString(),
                'status' => 'confirmed',
                'description' => $description,
                'metadata' => [
                    'source' => $closing ? 'time_closing' : 'manual',
                    'time_closing_id' => $closing?->id,
                ],
            ]);

            $bank->update([
                'net_balance_hours' => $newBalance,
                'positive_balance_hours' => max(0, $newBalance),
                'negative_balance_hours' => abs(min(0, $newBalance)),
                'last_movement_at' => now(),
            ]);

            return $movement;
        });
    }
}