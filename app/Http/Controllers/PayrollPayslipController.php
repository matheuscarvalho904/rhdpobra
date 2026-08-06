<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Services\PayslipBatchService;
use App\Services\PayslipService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PayrollPayslipController extends Controller
{
    public function view(PayrollRun $payrollRun, Employee $employee, PayslipService $service)
    {
        return $service->stream($payrollRun, $employee);
    }

    public function download(PayrollRun $payrollRun, Employee $employee, PayslipService $service)
    {
        return $service->download($payrollRun, $employee);
    }

    public function downloadAll(
        PayrollRun $payrollRun,
        PayslipBatchService $service,
    ): BinaryFileResponse|Response {
        if ($service->hasPersistentZip($payrollRun)) {
            return response()->download(
                $service->persistentZipPath($payrollRun),
                "holerites-folha-{$payrollRun->id}.zip",
            );
        }

        $employeeCount = $payrollRun->items()
            ->whereNotNull('employee_id')
            ->distinct()
            ->count('employee_id');

        if ($employeeCount <= 25) {
            $zipPath = $service->generateZip($payrollRun);

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return response(
            'O ZIP desta folha ainda não foi preparado. '
            . "Execute no terminal: php artisan payroll:generate-payslips-zip {$payrollRun->id}. "
            . 'Depois clique novamente em Holerites ZIP.',
            409,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }
}
