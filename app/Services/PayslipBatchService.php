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

    public function generateZip(PayrollRun $payrollRun): string
    {
        $employees = $this->getEmployeesFromPayroll($payrollRun);

        if ($employees->isEmpty()) {
            throw new RuntimeException('Nenhum holerite encontrado para esta folha.');
        }

        $baseDir = storage_path('app/temp/payslips');
        $runDir = $baseDir . DIRECTORY_SEPARATOR . 'run-' . $payrollRun->id . '-' . now()->format('YmdHis');
        $pdfDir = $runDir . DIRECTORY_SEPARATOR . 'pdfs';

        File::ensureDirectoryExists($pdfDir);

        foreach ($employees as $employee) {
            $pdf = $this->payslipService->generate($payrollRun, $employee);

            $fileName = $this->makePdfFileName(
                $employee->name ?? 'colaborador',
                $payrollRun->id
            );

            File::put(
                $pdfDir . DIRECTORY_SEPARATOR . $fileName,
                $pdf->output()
            );
        }

        $zipPath = $runDir . DIRECTORY_SEPARATOR . $this->makeZipFileName($payrollRun->id);

        $this->createZip($pdfDir, $zipPath);

        $this->cleanupOldFiles($baseDir, $runDir);

        return $zipPath;
    }

    protected function getEmployeesFromPayroll(PayrollRun $payrollRun): Collection
    {
        return $payrollRun->items()
            ->with('employee')
            ->get()
            ->pluck('employee')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    protected function createZip(string $sourceDir, string $zipPath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Não foi possível criar o arquivo ZIP dos holerites.');
        }

        foreach (File::files($sourceDir) as $file) {
            $realPath = $file->getRealPath();

            if ($realPath && File::exists($realPath)) {
                $zip->addFile($realPath, $file->getFilename());
            }
        }

        $zip->close();
    }

    protected function makePdfFileName(string $employeeName, int $payrollRunId): string
    {
        $slug = Str::of($employeeName)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-');

        $slug = $slug->isEmpty() ? 'colaborador' : (string) $slug;

        return "holerite-{$slug}-folha-{$payrollRunId}.pdf";
    }

    protected function makeZipFileName(int $payrollRunId): string
    {
        return "holerites-folha-{$payrollRunId}.zip";
    }

    protected function cleanupOldFiles(string $baseDir, ?string $currentRunDir = null): void
    {
        if (!File::exists($baseDir)) {
            return;
        }

        $directories = File::directories($baseDir);

        foreach ($directories as $dir) {
            try {
                if ($currentRunDir && realpath($dir) === realpath($currentRunDir)) {
                    continue;
                }

                if (!File::exists($dir)) {
                    continue;
                }

                $lastModified = File::lastModified($dir);

                if (!is_int($lastModified) || $lastModified <= 0) {
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