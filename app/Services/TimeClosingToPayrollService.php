<?php

namespace App\Services;

use App\Models\CompanyTimeBankSetting;
use App\Models\Employee;
use App\Models\EmployeeTimeBankSetting;
use App\Models\EmployeeVariableEvent;
use App\Models\Holiday;
use App\Models\TimeClosing;
use App\Models\TimePayrollEventMapping;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TimeClosingToPayrollService
{
    public function __construct(
        protected TimeBankService $timeBankService,
    ) {}

    public function generate(TimeClosing $closing, ?int $payrollCompetencyId = null): void
    {
        $closing->loadMissing(['items.employee.currentContract']);

        $payrollCompetencyId ??= $closing->payroll_competency_id;

        if (! $payrollCompetencyId) {
            return;
        }

        $this->clearPreviousEvents($closing, $payrollCompetencyId);
        $this->timeBankService->clearClosingMovements($closing);

        $events = $this->resolvePayrollEvents($closing->company_id);

        foreach ($closing->items as $item) {
            if (! $item->employee_id || ! $item->employee) {
                continue;
            }

            $employee = $item->employee;
            $settings = $this->resolveTimeBankSettings($employee);
            $split = $this->splitOvertimeBySchedule($employee, $item);

            $amount50 = 0.0;
            $amount100 = 0.0;
            $payrollHours50 = $split['hours_50'];
            $payrollHours100 = $split['hours_100'];

            if ($settings['enabled'] && $split['hours_50'] > 0) {
                $result50 = $this->timeBankService->applyOvertime(
                    employee: $employee,
                    overtimeHours: $split['hours_50'],
                    destination: $settings['destination'],
                    monthlyLimit: $settings['monthly_limit'],
                    excessToPayroll: $settings['excess_to_payroll'],
                    closing: $closing,
                );

                $payrollHours50 = (float) $result50['payroll_hours'];
            }

            if ($settings['enabled'] && $split['hours_100'] > 0) {
                $result100 = $this->timeBankService->applyOvertime(
                    employee: $employee,
                    overtimeHours: $split['hours_100'],
                    destination: $settings['destination'],
                    monthlyLimit: $settings['monthly_limit'],
                    excessToPayroll: $settings['excess_to_payroll'],
                    closing: $closing,
                );

                $payrollHours100 = (float) $result100['payroll_hours'];
            }

            if ($payrollHours50 > 0 && $events['overtime_50']) {
                $amount50 = $this->calculateOvertime50($item, $payrollHours50);

                $this->upsertVariableEvent(
                    employeeId: $item->employee_id,
                    payrollEventId: $events['overtime_50'],
                    payrollCompetencyId: $payrollCompetencyId,
                    amount: $amount50,
                    quantity: $payrollHours50,
                    reference: $payrollHours50,
                    notes: "Gerado pelo fechamento de ponto #{$closing->id} - Hora Extra 50%."
                );
            }

            if ($payrollHours100 > 0 && $events['overtime_100']) {
                $amount100 = $this->calculateOvertime100($item, $payrollHours100);

                $this->upsertVariableEvent(
                    employeeId: $item->employee_id,
                    payrollEventId: $events['overtime_100'],
                    payrollCompetencyId: $payrollCompetencyId,
                    amount: $amount100,
                    quantity: $payrollHours100,
                    reference: $payrollHours100,
                    notes: "Gerado pelo fechamento de ponto #{$closing->id} - Hora Extra 100%."
                );
            }

            if (($amount50 + $amount100) > 0 && $events['dsr_overtime']) {
                $this->generateDsrOvertimeEvent(
                    closing: $closing,
                    item: $item,
                    payrollEventId: $events['dsr_overtime'],
                    payrollCompetencyId: $payrollCompetencyId,
                    overtimeAmount: $amount50 + $amount100,
                    overtimeHours: $payrollHours50 + $payrollHours100,
                );
            }

            $delayHours = (float) $item->delay_hours;

            if ($settings['enabled'] && $settings['compensate_delays'] && $delayHours > 0) {
                $compensation = $this->timeBankService->compensateDelay(
                    employee: $employee,
                    delayHours: $delayHours,
                    closing: $closing,
                );

                $delayHours = (float) $compensation['remaining_hours'];
            }

            if ((float) $item->absence_days > 0 && $events['absence']) {
                $absenceAmount = $this->calculateAbsence($item, (float) $item->absence_days);

                $this->upsertVariableEvent(
                    employeeId: $item->employee_id,
                    payrollEventId: $events['absence'],
                    payrollCompetencyId: $payrollCompetencyId,
                    amount: -$absenceAmount,
                    quantity: (float) $item->absence_days,
                    reference: (float) $item->absence_days,
                    notes: "Gerado pelo fechamento de ponto #{$closing->id} - Falta."
                );
            }

            if ($delayHours > 0 && $events['delay']) {
                $delayAmount = $this->calculateDelay($item, $delayHours);

                $this->upsertVariableEvent(
                    employeeId: $item->employee_id,
                    payrollEventId: $events['delay'],
                    payrollCompetencyId: $payrollCompetencyId,
                    amount: -$delayAmount,
                    quantity: $delayHours,
                    reference: $delayHours,
                    notes: "Gerado pelo fechamento de ponto #{$closing->id} - Atraso."
                );
            }

            if ((float) $item->absence_days > 0 && $events['dsr_absence']) {
                $dsrLostDays = $this->calculateDsrLostDays($item);

                if ($dsrLostDays > 0) {
                    $dsrAbsenceAmount = $this->calculateAbsence($item, $dsrLostDays);

                    $this->upsertVariableEvent(
                        employeeId: $item->employee_id,
                        payrollEventId: $events['dsr_absence'],
                        payrollCompetencyId: $payrollCompetencyId,
                        amount: -$dsrAbsenceAmount,
                        quantity: $dsrLostDays,
                        reference: $dsrLostDays,
                        notes: "Gerado pelo fechamento de ponto #{$closing->id} - DSR perdido por falta."
                    );
                }
            }
        }
    }

    protected function resolveTimeBankSettings(Employee $employee): array
    {
        $employeeSetting = EmployeeTimeBankSetting::query()
            ->where('employee_id', $employee->id)
            ->first();

        if ($employeeSetting && ! $employeeSetting->use_company_rules) {
            return [
                'enabled' => (bool) $employeeSetting->time_bank_enabled,
                'destination' => $employeeSetting->overtime_destination ?: 'payroll',
                'monthly_limit' => (float) $employeeSetting->monthly_bank_limit,
                'excess_to_payroll' => (bool) $employeeSetting->excess_to_payroll,
                'compensate_delays' => (bool) $employeeSetting->compensate_delays_with_balance,
            ];
        }

        $companySetting = CompanyTimeBankSetting::query()
            ->where('company_id', $employee->company_id)
            ->first();

        if (! $companySetting) {
            return [
                'enabled' => false,
                'destination' => 'payroll',
                'monthly_limit' => 0,
                'excess_to_payroll' => true,
                'compensate_delays' => false,
            ];
        }

        return [
            'enabled' => (bool) $companySetting->enabled,
            'destination' => $companySetting->default_overtime_destination ?: 'payroll',
            'monthly_limit' => (float) $companySetting->monthly_bank_limit,
            'excess_to_payroll' => (bool) $companySetting->excess_to_payroll,
            'compensate_delays' => (bool) $companySetting->compensate_delays_with_balance,
        ];
    }

    protected function splitOvertimeBySchedule(Employee $employee, $item): array
    {
        $itemTotal = round((float) $item->overtime_hours, 2);

        if ($itemTotal <= 0) {
            return ['hours_50' => 0.0, 'hours_100' => 0.0];
        }

        $hours50 = 0.0;
        $hours100 = 0.0;

        foreach (($item->daily_summary ?? []) as $day) {
            if (($day['date'] ?? null) === 'TOTAL') {
                continue;
            }

            $date = Carbon::parse($day['date']);
            $worked = (float) ($day['worked_hours'] ?? 0);
            $expected = (float) ($day['expected_hours'] ?? 0);

            $extra = max(0, round($worked - $expected, 2));

            if ($extra <= 0) {
                continue;
            }

            $isHoliday = (bool) ($day['is_holiday'] ?? false);
            $isSunday = (bool) ($day['is_sunday'] ?? $date->isSunday());

            if ($isSunday || $isHoliday) {
                $hours100 += $extra;
            } else {
                $hours50 += $extra;
            }
        }

        $hours50 = round($hours50, 2);
        $hours100 = round($hours100, 2);
        $totalSplit = round($hours50 + $hours100, 2);

        if ($totalSplit > $itemTotal) {
            $excess = round($totalSplit - $itemTotal, 2);

            if ($hours50 >= $excess) {
                $hours50 = round($hours50 - $excess, 2);
            } else {
                $remaining = round($excess - $hours50, 2);
                $hours50 = 0.0;
                $hours100 = max(0, round($hours100 - $remaining, 2));
            }
        }

        if (round($hours50 + $hours100, 2) < $itemTotal) {
            $missing = round($itemTotal - ($hours50 + $hours100), 2);
            $hours50 = round($hours50 + $missing, 2);
        }

        return [
            'hours_50' => round($hours50, 2),
            'hours_100' => round($hours100, 2),
        ];
    }

    protected function clearPreviousEvents(TimeClosing $closing, int $payrollCompetencyId): void
    {
        EmployeeVariableEvent::query()
            ->where('payroll_competency_id', $payrollCompetencyId)
            ->where('notes', 'like', "%fechamento de ponto #{$closing->id}%")
            ->delete();
    }

    protected function generateDsrOvertimeEvent(
        TimeClosing $closing,
        $item,
        int $payrollEventId,
        int $payrollCompetencyId,
        float $overtimeAmount,
        float $overtimeHours
    ): void {
        $calendar = $this->countDsrCalendarDays($closing);

        if ($calendar['working_days'] <= 0 || $calendar['dsr_days'] <= 0) {
            return;
        }

        $amount = round(($overtimeAmount / $calendar['working_days']) * $calendar['dsr_days'], 2);

        if ($amount <= 0) {
            return;
        }

        $this->upsertVariableEvent(
            employeeId: $item->employee_id,
            payrollEventId: $payrollEventId,
            payrollCompetencyId: $payrollCompetencyId,
            amount: $amount,
            quantity: $calendar['dsr_days'],
            reference: $overtimeHours,
            notes: "Gerado pelo fechamento de ponto #{$closing->id} - DSR sobre horas extras."
        );
    }

    protected function calculateDsrLostDays($item): float
    {
        $hasAbsence = collect($item->daily_summary ?? [])
            ->filter(function ($day) {
                return ($day['date'] ?? null) !== 'TOTAL'
                    && ! (bool) ($day['is_sunday'] ?? false)
                    && ! (bool) ($day['is_saturday'] ?? false)
                    && ! (bool) ($day['is_holiday'] ?? false)
                    && (float) ($day['absence_days'] ?? 0) > 0;
            })
            ->isNotEmpty();

        return $hasAbsence ? 1.0 : 0.0;
    }

    protected function countDsrCalendarDays(TimeClosing $closing): array
    {
        $workingDays = 0;
        $dsrDays = 0;

        foreach (CarbonPeriod::create($closing->start_date, $closing->end_date) as $date) {
            if ($date->isSunday() || $this->isHolidayDate($date)) {
                $dsrDays++;
                continue;
            }

            $workingDays++;
        }

        return [
            'working_days' => $workingDays,
            'dsr_days' => $dsrDays,
        ];
    }

    protected function isHolidayDate(Carbon $date): bool
    {
        return Holiday::query()
            ->where('is_active', true)
            ->whereDate('holiday_date', $date->toDateString())
            ->exists();
    }

    protected function upsertVariableEvent(
        int $employeeId,
        int $payrollEventId,
        int $payrollCompetencyId,
        float $amount,
        float $quantity,
        float $reference,
        string $notes
    ): void {
        EmployeeVariableEvent::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'payroll_event_id' => $payrollEventId,
                'payroll_competency_id' => $payrollCompetencyId,
                'notes' => $notes,
            ],
            [
                'amount' => round($amount, 2),
                'quantity' => round($quantity, 2),
                'reference' => round($reference, 2),
            ]
        );
    }

    protected function resolvePayrollEvents(?int $companyId): array
    {
        return [
            'overtime_50' => $this->resolvePayrollEventId('overtime_50', $companyId),
            'overtime_100' => $this->resolvePayrollEventId('overtime_100', $companyId),
            'dsr_overtime' => $this->resolvePayrollEventId('dsr_overtime', $companyId),
            'absence' => $this->resolvePayrollEventId('absence', $companyId),
            'delay' => $this->resolvePayrollEventId('delay', $companyId),
            'dsr_absence' => $this->resolvePayrollEventId('dsr_absence', $companyId),
        ];
    }

    protected function resolvePayrollEventId(string $type, ?int $companyId): ?int
    {
        $mapping = TimePayrollEventMapping::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->where(function ($query) use ($companyId) {
                $query->whereNull('company_id');

                if ($companyId) {
                    $query->orWhere('company_id', $companyId);
                }
            })
            ->orderByRaw('CASE WHEN company_id IS NULL THEN 1 ELSE 0 END')
            ->first();

        return $mapping?->payroll_event_id;
    }

    protected function calculateOvertime50($item, float $hours): float
    {
        return round($hours * $this->hourValue($item) * 1.5, 2);
    }

    protected function calculateOvertime100($item, float $hours): float
    {
        return round($hours * $this->hourValue($item) * 2, 2);
    }

    protected function calculateAbsence($item, float $days): float
    {
        $salary = $this->salary($item);

        if ($salary <= 0 || $days <= 0) {
            return 0.0;
        }

        return round(($salary / 30) * $days, 2);
    }

    protected function calculateDelay($item, float $hours): float
    {
        if ($hours <= 0) {
            return 0.0;
        }

        return round($hours * $this->hourValue($item), 2);
    }

    protected function hourValue($item): float
    {
        $salary = $this->salary($item);

        return $salary > 0 ? ($salary / 220) : 0;
    }

    protected function salary($item): float
    {
        return (float) (
            $item->employee?->currentContract?->salary
            ?? $item->employee?->salary
            ?? 0
        );
    }
}