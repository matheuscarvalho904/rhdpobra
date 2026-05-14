<?php

namespace App\Services;

use App\Models\PayrollRun;
use App\Models\TimeClosing;
use Illuminate\Support\Facades\DB;
use Throwable;

class TimeClosingService
{
    public function __construct(
        protected TimeClosingToPayrollService $timeClosingToPayrollService,
        protected PayrollRunProcessingService $payrollRunProcessingService,
    ) {}

    public function sendToPayroll(TimeClosing $closing, bool $reprocessPayroll = true): TimeClosing
    {
        return DB::transaction(function () use ($closing, $reprocessPayroll) {
            $closing->refresh();

            $this->timeClosingToPayrollService->generate($closing);

            $closing->update([
                'status' => 'processed',
                'processed_at' => $closing->processed_at ?: now(),
            ]);

            if ($reprocessPayroll && $closing->payroll_competency_id) {
                $runs = PayrollRun::query()
                    ->where('payroll_competency_id', $closing->payroll_competency_id)
                    ->where('company_id', $closing->company_id)
                    ->whereIn('run_type', [
                        'payroll_clt',
                        'payroll_apprentice',
                    ])
                    ->get();

                foreach ($runs as $run) {
                    try {
                        $this->payrollRunProcessingService->reprocess($run);
                    } catch (Throwable $e) {
                        report($e);
                    }
                }
            }

            return $closing->refresh();
        });
    }
}