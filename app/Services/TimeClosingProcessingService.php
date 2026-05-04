<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\TimeClosing;
use App\Models\TimeClosingItem;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimeClosingProcessingService
{
    public function process(TimeClosing $closing): TimeClosing
    {
        return DB::transaction(function () use ($closing) {
            $closing->items()->delete();

            $employees = $this->employeesForClosing($closing);

            $employeeCount = 0;
            $totalWorkedHours = 0.0;
            $totalOvertimeHours = 0.0;
            $totalDelayHours = 0.0;
            $totalAbsenceDays = 0.0;

            foreach ($employees as $employee) {
                $summary = $this->calculateEmployee($closing, $employee);

                if ($summary['period_invalid']) {
                    continue;
                }

                TimeClosingItem::create([
                    'time_closing_id' => $closing->id,
                    'employee_id' => $employee->id,
                    'worked_hours' => $summary['worked_hours'],
                    'expected_hours' => $summary['expected_hours'],
                    'overtime_hours' => $summary['overtime_hours'],
                    'delay_hours' => $summary['delay_hours'],
                    'absence_days' => $summary['absence_days'],
                    'entries_count' => $summary['entries_count'],
                    'days_with_entries' => $summary['days_with_entries'],
                    'status' => $summary['status'],
                    'notes' => $summary['notes'],
                    'daily_summary' => $summary['daily_summary'],
                ]);

                $employeeCount++;
                $totalWorkedHours += $summary['worked_hours'];
                $totalOvertimeHours += $summary['overtime_hours'];
                $totalDelayHours += $summary['delay_hours'];
                $totalAbsenceDays += $summary['absence_days'];
            }

            $closing->update([
                'status' => 'processed',
                'employee_count' => $employeeCount,
                'total_worked_hours' => round($totalWorkedHours, 2),
                'total_overtime_hours' => round($totalOvertimeHours, 2),
                'total_delay_hours' => round($totalDelayHours, 2),
                'total_absence_days' => round($totalAbsenceDays, 2),
                'processed_at' => now(),
            ]);

            return $closing->refresh();
        });
    }

    protected function employeesForClosing(TimeClosing $closing): Collection
    {
        return Employee::query()
            ->with([
                'currentContract',
                'workSchedules.workSchedule.days',
            ])
            ->where('company_id', $closing->company_id)
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhere('status', 'active');
            })
            ->whereHas('currentContract', function ($contractQuery) use ($closing) {
                $contractQuery
                    ->where('is_current', true)
                    ->where(function ($query) use ($closing) {
                        $query
                            ->whereNull('admission_date')
                            ->orWhereDate('admission_date', '<=', $closing->end_date->toDateString());
                    });
            })
            ->orderBy('name')
            ->get();
    }

    protected function calculateEmployee(TimeClosing $closing, Employee $employee): array
    {
        $period = $this->resolveEmployeeClosingPeriod($closing, $employee);

        if (! $period) {
            return [
                'period_invalid' => true,
                'worked_hours' => 0,
                'expected_hours' => 0,
                'overtime_hours' => 0,
                'delay_hours' => 0,
                'absence_days' => 0,
                'entries_count' => 0,
                'days_with_entries' => 0,
                'status' => 'ignored',
                'notes' => 'Colaborador fora do período do fechamento.',
                'daily_summary' => [],
            ];
        }

        $entries = TimeEntry::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('entry_date', [
                $period['start']->toDateString(),
                $period['end']->toDateString(),
            ])
            ->orderBy('entry_datetime')
            ->get()
            ->groupBy(fn (TimeEntry $entry) => $entry->entry_date->toDateString());

        $workedHours = 0.0;
        $expectedHours = 0.0;
        $overtimeHours = 0.0;
        $delayHours = 0.0;
        $absenceDays = 0.0;
        $daysWithEntries = 0;
        $warnings = [];
        $dailySummary = [];

        foreach (CarbonPeriod::create($period['start'], $period['end']) as $date) {
            /** @var Carbon $date */
            $dateString = $date->toDateString();
            $dayEntries = $entries->get($dateString, collect())->values();

            $expectedDailyHours = $this->expectedHoursForDate($date, $employee);
            $dayWorkedMinutes = $this->calculateWorkedMinutes($dayEntries);
            $dayWorkedHours = round($dayWorkedMinutes / 60, 2);

            $dayOvertimeHours = 0.0;
            $dayDelayHours = 0.0;
            $dayAbsenceDays = 0.0;

            if ($dayEntries->isNotEmpty()) {
                $daysWithEntries++;
            }

            if ($dayEntries->count() % 2 !== 0) {
                $warnings[] = "Marcações ímpares em {$date->format('d/m/Y')}.";
            }

            if ($expectedDailyHours > 0) {
                if ($dayWorkedHours <= 0) {
                    $dayAbsenceDays = 1.0;
                    $dayDelayHours = $expectedDailyHours;
                } elseif ($dayWorkedHours < $expectedDailyHours) {
                    $dayDelayHours = round($expectedDailyHours - $dayWorkedHours, 2);
                } elseif ($dayWorkedHours > $expectedDailyHours) {
                    $dayOvertimeHours = round($dayWorkedHours - $expectedDailyHours, 2);
                }
            } elseif ($dayWorkedHours > 0) {
                $dayOvertimeHours = $dayWorkedHours;
            }

            $workedHours += $dayWorkedHours;
            $expectedHours += $expectedDailyHours;
            $overtimeHours += $dayOvertimeHours;
            $delayHours += $dayDelayHours;
            $absenceDays += $dayAbsenceDays;

            $dailySummary[] = [
                'date' => $dateString,
                'weekday' => $date->locale('pt_BR')->dayName,
                'is_sunday' => $date->isSunday(),
                'is_saturday' => $date->isSaturday(),
                'is_holiday' => $this->isHoliday($date),
                'worked_hours' => round($dayWorkedHours, 2),
                'expected_hours' => round($expectedDailyHours, 2),
                'overtime_hours' => round($dayOvertimeHours, 2),
                'delay_hours' => round($dayDelayHours, 2),
                'absence_days' => round($dayAbsenceDays, 2),
                'entries_count' => $dayEntries->count(),
                'status' => $this->resolveDailyStatus($dayEntries, $dayAbsenceDays, $dayDelayHours, $dayOvertimeHours),
                'notes' => $this->resolveDailyNotes($dayEntries, $dayAbsenceDays, $dayDelayHours, $dayOvertimeHours),
            ];
        }

        $dailySummary[] = [
            'date' => 'TOTAL',
            'weekday' => null,
            'is_sunday' => false,
            'is_saturday' => false,
            'is_holiday' => false,
            'worked_hours' => round($workedHours, 2),
            'expected_hours' => round($expectedHours, 2),
            'overtime_hours' => round($overtimeHours, 2),
            'delay_hours' => round($delayHours, 2),
            'absence_days' => round($absenceDays, 2),
            'entries_count' => $entries->flatten(1)->count(),
            'status' => $this->resolvePeriodStatus($overtimeHours, $delayHours, $absenceDays),
            'notes' => 'Fechamento calculado por jornada: extras, atrasos e faltas automáticos.',
            'period_start' => $period['start']->toDateString(),
            'period_end' => $period['end']->toDateString(),
        ];

        return [
            'period_invalid' => false,
            'worked_hours' => round($workedHours, 2),
            'expected_hours' => round($expectedHours, 2),
            'overtime_hours' => round($overtimeHours, 2),
            'delay_hours' => round($delayHours, 2),
            'absence_days' => round($absenceDays, 2),
            'entries_count' => $entries->flatten(1)->count(),
            'days_with_entries' => $daysWithEntries,
            'status' => count($warnings) ? 'warning' : 'processed',
            'notes' => count($warnings) ? implode("\n", $warnings) : null,
            'daily_summary' => $dailySummary,
        ];
    }

    protected function resolveDailyStatus(
        Collection $dayEntries,
        float $absenceDays,
        float $delayHours,
        float $overtimeHours
    ): string {
        if ($dayEntries->count() % 2 !== 0) {
            return 'warning';
        }

        if ($absenceDays > 0) {
            return 'absence';
        }

        if ($delayHours > 0) {
            return 'delay';
        }

        if ($overtimeHours > 0) {
            return 'overtime';
        }

        return 'ok';
    }

    protected function resolveDailyNotes(
        Collection $dayEntries,
        float $absenceDays,
        float $delayHours,
        float $overtimeHours
    ): ?string {
        if ($dayEntries->count() % 2 !== 0) {
            return 'Marcações em quantidade ímpar.';
        }

        if ($absenceDays > 0) {
            return 'Falta automática: não houve marcação em dia com jornada prevista.';
        }

        if ($delayHours > 0) {
            return 'Atraso automático: horas trabalhadas abaixo da jornada prevista.';
        }

        if ($overtimeHours > 0) {
            return 'Hora extra automática: horas trabalhadas acima da jornada prevista.';
        }

        return null;
    }

    protected function resolvePeriodStatus(float $overtimeHours, float $delayHours, float $absenceDays): string
    {
        if ($absenceDays > 0) {
            return 'period_absence';
        }

        if ($delayHours > 0) {
            return 'period_delay';
        }

        if ($overtimeHours > 0) {
            return 'period_overtime';
        }

        return 'period_ok';
    }

    protected function resolveEmployeeClosingPeriod(TimeClosing $closing, Employee $employee): ?array
    {
        $start = Carbon::parse($closing->start_date)->startOfDay();
        $end = Carbon::parse($closing->end_date)->endOfDay();

        $admissionDate = $this->resolveEmployeeAdmissionDate($employee);

        if ($admissionDate && $admissionDate->greaterThan($start)) {
            $start = $admissionDate->copy()->startOfDay();
        }

        $terminationDate = $this->resolveEmployeeTerminationDate($employee);

        if ($terminationDate && $terminationDate->lessThan($end)) {
            $end = $terminationDate->copy()->endOfDay();
        }

        if ($start->greaterThan($end)) {
            return null;
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    protected function expectedHoursForDate(Carbon $date, Employee $employee): float
    {
        $employeeSchedule = $employee->workSchedules()
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $date->toDateString())
            ->where(function ($query) use ($date) {
                $query
                    ->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $date->toDateString());
            })
            ->with('workSchedule.days')
            ->latest('start_date')
            ->first();

        if (! $employeeSchedule || ! $employeeSchedule->workSchedule) {
            return $this->fallbackExpectedHoursForDate($date);
        }

        $schedule = $employeeSchedule->workSchedule;
        $settings = is_array($schedule->settings) ? $schedule->settings : [];
        $dayConfig = $schedule->days->firstWhere('weekday', $date->dayOfWeek);

        if ($date->isSunday()) {
            return (float) ($settings['sunday_expected_hours'] ?? 0);
        }

        if ($date->isSaturday()) {
            return (float) ($settings['saturday_expected_hours'] ?? $dayConfig?->expected_hours ?? 4);
        }

        if ($this->isHoliday($date)) {
            $keepHoliday = (bool) ($settings['holiday_keeps_expected_hours'] ?? false);

            return $keepHoliday
                ? (float) ($dayConfig?->expected_hours ?? 8)
                : 0.0;
        }

        if (! $dayConfig) {
            return 0.0;
        }

        if (! $dayConfig->is_working_day) {
            return 0.0;
        }

        return (float) $dayConfig->expected_hours;
    }

    protected function fallbackExpectedHoursForDate(Carbon $date): float
    {
        if ($date->isSunday()) {
            return 0.0;
        }

        if ($date->isSaturday()) {
            return 4.0;
        }

        if ($this->isHoliday($date)) {
            return 0.0;
        }

        return 8.0;
    }

    protected function isHoliday(Carbon $date): bool
    {
        return Holiday::query()
            ->where('is_active', true)
            ->whereDate('holiday_date', $date->toDateString())
            ->exists();
    }

    protected function calculateWorkedMinutes(Collection $entries): int
    {
        $minutes = 0;
        $ordered = $entries->sortBy('entry_datetime')->values();

        for ($i = 0; $i < $ordered->count(); $i += 2) {
            $in = $ordered->get($i);
            $out = $ordered->get($i + 1);

            if (! $in || ! $out) {
                continue;
            }

            $minutes += $in->entry_datetime->diffInMinutes($out->entry_datetime);
        }

        return $minutes;
    }

    protected function resolveEmployeeAdmissionDate(Employee $employee): ?CarbonInterface
    {
        return $this->parseDate(
            $employee->currentContract?->admission_date
                ?? $employee->currentContract?->hire_date
                ?? $employee->admission_date
                ?? $employee->hire_date
                ?? $employee->admitted_at
                ?? $employee->start_date
                ?? null
        );
    }

    protected function resolveEmployeeTerminationDate(Employee $employee): ?CarbonInterface
    {
        return $this->parseDate(
            $employee->currentContract?->termination_date
                ?? $employee->termination_date
                ?? $employee->dismissal_date
                ?? $employee->demission_date
                ?? $employee->end_date
                ?? null
        );
    }

    protected function parseDate(mixed $value): ?CarbonInterface
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->copy();
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}