<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Services\PayslipBatchService;
use App\Services\PayslipService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PayrollPayslipController extends Controller
{
    public function view(PayrollRun $payrollRun,Employee $employee,PayslipService $service){ return $service->stream($payrollRun,$employee); }
    public function download(PayrollRun $payrollRun,Employee $employee,PayslipService $service){ return $service->download($payrollRun,$employee); }
    public function downloadAll(PayrollRun $payrollRun,PayslipBatchService $service)
    {
        if($service->hasPersistentZip($payrollRun)) return $this->downloadPrepared($payrollRun,$service);
        $service->startWebGeneration($payrollRun);
        return view('payroll.payslips-batch-progress',[
            'payrollRun'=>$payrollRun,
            'processUrl'=>route('payroll.payslip.process-chunk',$payrollRun),
            'statusUrl'=>route('payroll.payslip.batch-status',$payrollRun),
            'downloadUrl'=>route('payroll.payslip.download-prepared',$payrollRun),
            'restartUrl'=>route('payroll.payslip.restart-batch',$payrollRun),
        ]);
    }
    public function processChunk(PayrollRun $payrollRun,PayslipBatchService $service): JsonResponse { return response()->json($service->processWebChunk($payrollRun,5)); }
    public function batchStatus(PayrollRun $payrollRun,PayslipBatchService $service): JsonResponse { return response()->json($service->webStatus($payrollRun)); }
    public function restartBatch(PayrollRun $payrollRun,PayslipBatchService $service): JsonResponse { $service->resetWebGeneration($payrollRun); return response()->json($service->startWebGeneration($payrollRun,true)); }
    public function downloadPrepared(PayrollRun $payrollRun,PayslipBatchService $service): BinaryFileResponse
    {
        abort_unless($service->hasPersistentZip($payrollRun),404,'ZIP ainda não disponível.');
        return response()->download($service->persistentZipPath($payrollRun),'holerites-folha-'.$payrollRun->id.'.zip');
    }
}
