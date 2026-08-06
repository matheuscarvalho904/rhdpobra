<?php

namespace App\Services;

use App\Models\EmployeeVariableEvent;
use App\Models\PayrollRun;
use App\Models\TimeClosing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TimeClosingFullReprocessService
{
    /**
     * Recalcula o fechamento usando as marcações já importadas,
     * recria somente os eventos automáticos e reprocessa as folhas vinculadas.
     *
     * Não apaga marcações e não chama novamente a API da Sólides.
     */
    public function run(TimeClosing $closing, ?PayrollRun $legacyPayrollRun = null): TimeClosing
    {
        $closing->refresh();

        if (! $closing->payroll_competency_id) {
            throw new RuntimeException(
                'O fechamento não possui competência da folha vinculada.'
            );
        }

        if ($closing->status === 'canceled') {
            throw new RuntimeException(
                'Fechamento cancelado não pode ser reprocessado.'
            );
        }

        $runs = $this->payrollRunsForClosing($closing);

        if ($runs->isEmpty() && $legacyPayrollRun) {
            $runs = collect([$legacyPayrollRun]);
        }

        if ($runs->isEmpty()) {
            throw new RuntimeException(
                'Nenhuma folha CLT ou Aprendiz foi encontrada para esta empresa e competência.'
            );
        }

        /*
         * Etapa 1 — Remove somente eventos automáticos gerados por este fechamento.
         * Eventos lançados manualmente permanecem intactos.
         */
        DB::transaction(function () use ($closing): void {
            EmployeeVariableEvent::query()
                ->where('payroll_competency_id', $closing->payroll_competency_id)
                ->where('notes', 'like', "%fechamento de ponto #{$closing->id}%")
                ->delete();
        });

        /*
         * Etapa 2 — Recalcula os itens e totais do fechamento com as marcações
         * que já estão em time_entries.
         */
        $closing = app(TimeClosingProcessingService::class)
            ->process($closing->fresh());

        /*
         * Etapa 3 — O próprio serviço limpa novamente, de forma idempotente,
         * os eventos automáticos e os movimentos de banco relacionados antes
         * de criar HE 50%, HE 100%, DSR, faltas e atrasos.
         */
        app(TimeClosingToPayrollService::class)->generate(
            $closing,
            $closing->payroll_competency_id
        );

        /*
         * Etapa 4 — Reprocessa todas as folhas CLT e Aprendiz da competência.
         * Folhas anteriormente fechadas ficam como "processed" para conferência.
         */
        foreach ($runs as $run) {
            app(PayrollRunProcessingService::class)
                ->forceReprocess($run->fresh());
        }

        return $closing->refresh();
    }

    protected function payrollRunsForClosing(TimeClosing $closing): Collection
    {
        return PayrollRun::query()
            ->where('company_id', $closing->company_id)
            ->where('payroll_competency_id', $closing->payroll_competency_id)
            ->whereIn('run_type', [
                'payroll_clt',
                'payroll_apprentice',
            ])
            ->orderBy('id')
            ->get();
    }
}
