<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Services\PayslipBatchService;
use App\Services\PayslipService;
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

    public function downloadAll(PayrollRun $payrollRun, PayslipBatchService $service): BinaryFileResponse
    {
        $zipPath = $service->generateZip($payrollRun);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}