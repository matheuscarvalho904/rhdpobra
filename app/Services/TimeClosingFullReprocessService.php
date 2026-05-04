<?php

namespace App\Services;

use App\Models\EmployeeVariableEvent;
use App\Models\PayrollRun;
use App\Models\PointIntegration;
use App\Models\TimeClosing;
use App\Models\TimeEntry;
use App\Models\TimeEntryImportItem;
use App\Services\Integrations\Solides\SolidesPunchImportService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TimeClosingFullReprocessService
{
    public function run(TimeClosing $closing, PayrollRun $payrollRun, ?PointIntegration $integration = null): TimeClosing
    {
        return DB::transaction(function () use ($closing, $payrollRun, $integration) {
            $closing->refresh();

            if (! $closing->payroll_competency_id) {
                throw new RuntimeException('O fechamento não possui competência da folha vinculada.');
            }

            $integration ??= PointIntegration::query()
                ->where('company_id', $closing->company_id)
                ->where('provider', 'solides')
                ->where('active', true)
                ->first();

            if (! $integration) {
                throw new RuntimeException('Nenhuma integração Sólides ativa encontrada para esta empresa.');
            }

            $employeeIds = $closing->items()
                ->whereNotNull('employee_id')
                ->pluck('employee_id')
                ->unique()
                ->values();

            if ($employeeIds->isEmpty()) {
                $employeeIds = TimeEntry::query()
                    ->where('company_id', $closing->company_id)
                    ->whereBetween('entry_date', [
                        $closing->start_date->toDateString(),
                        $closing->end_date->toDateString(),
                    ])
                    ->whereNotNull('employee_id')
                    ->pluck('employee_id')
                    ->unique()
                    ->values();
            }

            EmployeeVariableEvent::query()
                ->where('payroll_competency_id', $closing->payroll_competency_id)
                ->where('notes', 'like', "%fechamento de ponto #{$closing->id}%")
                ->delete();

            $payrollRun->items()->delete();

            if ($employeeIds->isNotEmpty()) {
                TimeEntry::query()
                    ->whereIn('employee_id', $employeeIds)
                    ->whereBetween('entry_date', [
                        $closing->start_date->toDateString(),
                        $closing->end_date->toDateString(),
                    ])
                    ->delete();

                TimeEntryImportItem::query()
                    ->whereIn('employee_id', $employeeIds)
                    ->whereBetween('entry_date', [
                        $closing->start_date->toDateString(),
                        $closing->end_date->toDateString(),
                    ])
                    ->delete();
            }

            app(SolidesPunchImportService::class)->import(
                $integration,
                $closing->start_date->toDateString(),
                $closing->end_date->toDateString()
            );

            $closing = app(TimeClosingProcessingService::class)->process($closing->refresh());

            app(TimeClosingToPayrollService::class)->generate(
                $closing,
                $closing->payroll_competency_id
            );

            app(PayrollRunProcessingService::class)->reprocess($payrollRun);

            return $closing->refresh();
        });
    }
}