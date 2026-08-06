<?php

namespace App\Console\Commands;

use App\Models\PayrollRun;
use App\Services\PayslipBatchService;
use Illuminate\Console\Command;
use Throwable;

class GeneratePayslipsZipCommand extends Command
{
    protected $signature = 'payroll:generate-payslips-zip
                            {payrollRunId : ID do processamento da folha}
                            {--force : Regenerar mesmo quando já existir ZIP}';

    protected $description = 'Gera o ZIP dos holerites fora da requisição HTTP, evitando timeout.';

    public function handle(PayslipBatchService $service): int
    {
        $payrollRunId = (int) $this->argument('payrollRunId');

        $payrollRun = PayrollRun::query()
            ->with(['company', 'work'])
            ->find($payrollRunId);

        if (! $payrollRun) {
            $this->error("Processamento da folha #{$payrollRunId} não encontrado.");
            return self::FAILURE;
        }

        if ($service->hasPersistentZip($payrollRun) && ! $this->option('force')) {
            $this->warn('Já existe um ZIP pronto para esta folha:');
            $this->line($service->persistentZipPath($payrollRun));
            $this->newLine();
            $this->line('Use --force para gerar novamente.');
            return self::SUCCESS;
        }

        $employeeCount = $payrollRun->items()
            ->whereNotNull('employee_id')
            ->distinct()
            ->count('employee_id');

        if ($employeeCount <= 0) {
            $this->error('Nenhum colaborador encontrado nesta folha.');
            return self::FAILURE;
        }

        $this->info("Folha #{$payrollRun->id}");
        $this->line('Empresa: ' . ($payrollRun->company?->name ?? '-'));
        $this->line('Obra: ' . ($payrollRun->work?->name ?? '-'));
        $this->line("Colaboradores: {$employeeCount}");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($employeeCount);
        $progressBar->start();
        $failed = 0;

        try {
            $zipPath = $service->generatePersistentZip(
                $payrollRun,
                function (int $processed, int $total, $employee, string $status, ?string $message) use ($progressBar, &$failed): void {
                    if ($status === 'failed') {
                        $failed++;
                    }

                    $progressBar->advance();
                },
            );

            $progressBar->finish();
            $this->newLine(2);
            $this->info('Geração concluída.');
            $this->line("ZIP: {$zipPath}");
            $this->line("Falhas: {$failed}");

            if ($failed > 0) {
                $this->warn('O ZIP contém FALHAS-NA-GERACAO.txt com os colaboradores que falharam.');
            }

            $this->newLine();
            $this->info('Agora clique novamente em "Holerites ZIP" na tela da folha.');
            return self::SUCCESS;
        } catch (Throwable $e) {
            $progressBar->finish();
            $this->newLine(2);
            $this->error('Falha ao gerar os holerites: ' . $e->getMessage());
            $this->line('Consulte storage/logs/laravel.log.');
            return self::FAILURE;
        }
    }
}
