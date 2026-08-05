<?php

namespace App\Console\Commands;

use App\Models\TimeEntryImport;
use App\Services\TimeImportAuditService;
use Illuminate\Console\Command;

class AuditTimeImportCommand extends Command
{
    protected $signature = 'point:audit-import {import : ID da importação de ponto}';

    protected $description = 'Audita uma importação de ponto e informa se ela pode ser utilizada no fechamento.';

    public function handle(TimeImportAuditService $auditService): int
    {
        $import = TimeEntryImport::query()->find($this->argument('import'));

        if (! $import) {
            $this->error('Importação não encontrada.');

            return self::FAILURE;
        }

        $audit = $auditService->audit($import);

        $this->table(
            ['Indicador', 'Valor'],
            [
                ['Status', $audit['status']],
                ['Período', $audit['period_start'] . ' até ' . $audit['period_end']],
                ['Payloads recebidos', $audit['payloads_received']],
                ['Marcações recebidas', $audit['marks_received']],
                ['Marcações persistidas', $audit['marks_persisted']],
                ['Inseridas', $audit['marks_inserted']],
                ['Atualizadas', $audit['marks_updated']],
                ['Ignoradas', $audit['marks_ignored']],
                ['Sem vínculo', $audit['employee_not_found']],
                ['Fora do período', $audit['outside_period']],
                ['Dias com marcações ímpares', $audit['odd_days_count']],
                ['Marcações duplicadas por horário', $audit['duplicate_datetime_count']],
                ['Conciliação fechada', $audit['balanced'] ? 'SIM' : 'NÃO'],
                ['Pode fechar o ponto', $audit['can_close'] ? 'SIM' : 'NÃO'],
            ],
        );

        if ($audit['blocking_reasons'] !== []) {
            $this->newLine();
            $this->error('Motivos de bloqueio:');

            foreach ($audit['blocking_reasons'] as $reason) {
                $this->line('- ' . $reason);
            }
        }

        if ($audit['odd_days'] !== []) {
            $this->newLine();
            $this->warn('Primeiros dias com marcações ímpares:');
            $this->table(
                ['Colaborador ID', 'Data', 'Marcações'],
                array_slice(array_map(fn ($row) => [
                    $row['employee_id'],
                    $row['entry_date'],
                    $row['total'],
                ], $audit['odd_days']), 0, 50),
            );
        }

        return $audit['can_close'] ? self::SUCCESS : self::FAILURE;
    }
}
