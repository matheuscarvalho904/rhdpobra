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
    public function view(
        PayrollRun $payrollRun,
        Employee $employee,
        PayslipService $service,
    ) {
        return $service->stream($payrollRun, $employee);
    }

    public function download(
        PayrollRun $payrollRun,
        Employee $employee,
        PayslipService $service,
    ) {
        return $service->download($payrollRun, $employee);
    }

    /**
     * Toda vez que o usuário clicar em "Gerar Holerites ZIP",
     * invalida o pacote anterior e inicia uma nova geração.
     *
     * O download do arquivo já concluído ocorre somente pela rota
     * downloadPrepared(), exibida ao final da tela de progresso.
     */
    public function downloadAll(
        PayrollRun $payrollRun,
        PayslipBatchService $service,
    ) {
        $service->resetWebGeneration($payrollRun);

        $service->startWebGeneration(
            payrollRun: $payrollRun,
            force: true,
        );

        return view('payroll.payslips-batch-progress', [
            'payrollRun' => $payrollRun->fresh(),
            'processUrl' => route('payroll.payslip.process-chunk', $payrollRun),
            'statusUrl' => route('payroll.payslip.batch-status', $payrollRun),
            'downloadUrl' => route('payroll.payslip.download-prepared', $payrollRun),
            'restartUrl' => route('payroll.payslip.restart-batch', $payrollRun),
        ]);
    }

    public function processChunk(
        PayrollRun $payrollRun,
        PayslipBatchService $service,
    ): JsonResponse {
        return response()->json(
            $service->processWebChunk(
                payrollRun: $payrollRun,
                chunkSize: 5,
            )
        );
    }

    public function batchStatus(
        PayrollRun $payrollRun,
        PayslipBatchService $service,
    ): JsonResponse {
        return response()->json(
            $service->webStatus($payrollRun)
        );
    }

    /**
     * Reinicia manualmente a geração na própria tela de progresso.
     */
    public function restartBatch(
        PayrollRun $payrollRun,
        PayslipBatchService $service,
    ): JsonResponse {
        $service->resetWebGeneration($payrollRun);

        return response()->json(
            $service->startWebGeneration(
                payrollRun: $payrollRun,
                force: true,
            )
        );
    }

    /**
     * Baixa somente o ZIP concluído pela geração atual.
     */
    public function downloadPrepared(
        PayrollRun $payrollRun,
        PayslipBatchService $service,
    ): BinaryFileResponse {
        abort_unless(
            $service->hasPersistentZip($payrollRun),
            404,
            'O ZIP atualizado ainda não está disponível.'
        );

        return response()->download(
            $service->persistentZipPath($payrollRun),
            "holerites-folha-{$payrollRun->id}.zip",
        );
    }
}
