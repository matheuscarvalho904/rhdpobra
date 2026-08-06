<?php

namespace App\Services;

use App\Models\PayrollRun;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class PayslipBatchService
{
    public function __construct(
        protected PayslipService $payslipService,
    ) {}

    public function generateZip(PayrollRun $payrollRun, ?callable $progress = null): string
    {
        return $this->generate($payrollRun, null, $progress);
    }

    public function generatePersistentZip(PayrollRun $payrollRun, ?callable $progress = null): string
    {
        $exportDir = storage_path('app/private/payroll-exports');
        File::ensureDirectoryExists($exportDir);

        return $this->generate(
            $payrollRun,
            $exportDir . DIRECTORY_SEPARATOR . $this->makeZipFileName($payrollRun->id),
            $progress,
        );
    }

    public function persistentZipPath(PayrollRun $payrollRun): string
    {
        return storage_path('app/private/payroll-exports/' . $this->makeZipFileName($payrollRun->id));
    }

    public function hasPersistentZip(PayrollRun $payrollRun): bool
    {
        $path = $this->persistentZipPath($payrollRun);

        return File::exists($path) && File::size($path) > 0;
    }

    protected function generate(
        PayrollRun $payrollRun,
        ?string $destinationPath = null,
        ?callable $progress = null,
    ): string {
        $payrollRun->loadMissing(['company', 'work']);

        $employees = $this->getEmployeesFromPayroll($payrollRun);

        if ($employees->isEmpty()) {
            throw new RuntimeException('Nenhum holerite encontrado para esta folha.');
        }

        $baseDir = storage_path('app/temp/payslips');
        $runDir = $baseDir . DIRECTORY_SEPARATOR . 'run-' . $payrollRun->id . '-' . now()->format('YmdHis');
        $pdfDir = $runDir . DIRECTORY_SEPARATOR . 'pdfs';

        File::ensureDirectoryExists($pdfDir);

        $total = $employees->count();
        $processed = 0;
        $generated = 0;
        $failures = [];

        try {
            foreach ($employees as $employee) {
                $processed++;

                try {
                    $pdf = $this->payslipService->generate($payrollRun, $employee);

                    $fileName = $this->makePdfFileName(
                        $employee->name ?? 'colaborador',
                        $employee->id,
                        $payrollRun->id,
                    );

                    File::put($pdfDir . DIRECTORY_SEPARATOR . $fileName, $pdf->output());

                    unset($pdf);
                    gc_collect_cycles();

                    $generated++;

                    if ($progress) {
                        $progress($processed, $total, $employee, 'success', null);
                    }
                } catch (Throwable $e) {
                    $failures[] = [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name ?? '-',
                        'message' => $e->getMessage(),
                    ];

                    Log::error('Erro ao gerar holerite em lote.', [
                        'payroll_run_id' => $payrollRun->id,
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name ?? null,
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    if ($progress) {
                        $progress($processed, $total, $employee, 'failed', $e->getMessage());
                    }
                }
            }

            if ($generated === 0) {
                throw new RuntimeException('Nenhum holerite pôde ser gerado. Consulte storage/logs/laravel.log.');
            }

            if ($failures !== []) {
                $this->writeFailureReport($pdfDir, $payrollRun->id, $failures);
            }

            $zipPath = $destinationPath ?: $runDir . DIRECTORY_SEPARATOR . $this->makeZipFileName($payrollRun->id);
            File::ensureDirectoryExists(dirname($zipPath));

            $this->createZip($pdfDir, $zipPath);

            if (! File::exists($zipPath) || File::size($zipPath) <= 0) {
                throw new RuntimeException('O ZIP foi criado, mas ficou vazio ou indisponível.');
            }

            $this->cleanupOldFiles($baseDir, $runDir);

            return $zipPath;
        } catch (Throwable $e) {
            Log::error('Falha na geração do ZIP de holerites.', [
                'payroll_run_id' => $payrollRun->id,
                'run_directory' => $runDir,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    protected function getEmployeesFromPayroll(PayrollRun $payrollRun): Collection
    {
        return $payrollRun->items()
            ->with('employee')
            ->select(['id', 'payroll_run_id', 'employee_id'])
            ->get()
            ->pluck('employee')
            ->filter()
            ->unique('id')
            ->sortBy(fn ($employee) => mb_strtoupper((string) ($employee->name ?? '')))
            ->values();
    }

    protected function createZip(string $sourceDir, string $zipPath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Não foi possível criar o arquivo ZIP dos holerites.');
        }

        try {
            foreach (File::files($sourceDir) as $file) {
                $realPath = $file->getRealPath();

                if ($realPath && File::exists($realPath)) {
                    $zip->addFile($realPath, $file->getFilename());
                }
            }
        } finally {
            $zip->close();
        }
    }

    protected function writeFailureReport(string $pdfDir, int $payrollRunId, array $failures): void
    {
        $lines = [
            'VOKTAR RH & DP - FALHAS NA GERAÇÃO DE HOLERITES',
            "Processamento da folha: {$payrollRunId}",
            'Gerado em: ' . now()->format('d/m/Y H:i:s'),
            str_repeat('-', 80),
        ];

        foreach ($failures as $failure) {
            $lines[] = sprintf(
                'ID %s | %s | %s',
                $failure['employee_id'] ?? '-',
                $failure['employee_name'] ?? '-',
                $failure['message'] ?? 'Erro não informado',
            );
        }

        File::put($pdfDir . DIRECTORY_SEPARATOR . 'FALHAS-NA-GERACAO.txt', implode(PHP_EOL, $lines));
    }

    protected function makePdfFileName(string $employeeName, int $employeeId, int $payrollRunId): string
    {
        $slug = Str::of($employeeName)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-');

        $slug = $slug->isEmpty() ? 'colaborador' : (string) $slug;

        return "holerite-{$slug}-emp-{$employeeId}-folha-{$payrollRunId}.pdf";
    }

    protected function makeZipFileName(int $payrollRunId): string
    {
        return "holerites-folha-{$payrollRunId}.zip";
    }

    protected function cleanupOldFiles(string $baseDir, ?string $currentRunDir = null): void
    {
        if (! File::exists($baseDir)) {
            return;
        }

        foreach (File::directories($baseDir) as $dir) {
            try {
                if ($currentRunDir && realpath($dir) === realpath($currentRunDir)) {
                    continue;
                }

                if (! File::exists($dir)) {
                    continue;
                }

                $lastModified = File::lastModified($dir);

                if (! is_int($lastModified) || $lastModified <= 0) {
                    continue;
                }

                $lastModifiedAt = Carbon::createFromTimestamp($lastModified);

                if ($lastModifiedAt->diffInHours(now()) > 6) {
                    File::deleteDirectory($dir);
                }
            } catch (Throwable $e) {
                Log::warning('Erro ao limpar diretório temporário de holerites.', [
                    'directory' => $dir,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
