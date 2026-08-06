<?php

namespace App\Http\Controllers;

use App\Models\EmployeeVariableEvent;
use App\Models\PayrollRun;
use App\Models\TimeClosing;
use App\Services\PayrollRunProcessingService;
use App\Services\TimeClosingProcessingService;
use App\Services\TimeClosingToPayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Throwable;

class TimeClosingWebProcessController extends Controller
{
    public function show(TimeClosing $timeClosing)
    {
        abort_unless(
            $timeClosing->payroll_competency_id,
            422,
            'Competência da folha não vinculada.'
        );

        abort_if(
            $timeClosing->status === 'canceled',
            422,
            'Fechamento cancelado.'
        );

        $this->initializeWorkflow($timeClosing);

        return view('time-closings.web-process-progress', [
            'closing' => $timeClosing,
            'processUrl' => route(
                'time-closings.web-process.process',
                $timeClosing
            ),
            'statusUrl' => route(
                'time-closings.web-process.status',
                $timeClosing
            ),
            'restartUrl' => route(
                'time-closings.web-process.restart',
                $timeClosing
            ),
            'backUrl' => url('/app/time-closings'),
        ]);
    }

    public function process(
        TimeClosing $timeClosing,
        TimeClosingProcessingService $closingService,
        TimeClosingToPayrollService $eventsService,
        PayrollRunProcessingService $payrollService,
    ): JsonResponse {
        $workflow = $this->workflow($timeClosing);

        try {
            switch ($workflow['stage']) {
                case 'closing_prepare':
                    $closingService->startWebProcess(
                        $timeClosing,
                        force: true
                    );

                    $workflow['stage'] = 'closing';
                    $workflow['stage_percentage'] = 0;
                    $workflow['message'] =
                        'Iniciando recálculo do fechamento.';
                    break;

                case 'closing':
                    $status = $closingService->processWebChunk(
                        $timeClosing,
                        5
                    );

                    $workflow['stage_percentage'] =
                        (int) ($status['percentage'] ?? 0);

                    $workflow['message'] =
                        $status['message']
                        ?? 'Recalculando fechamento.';

                    if (($status['status'] ?? null) === 'completed') {
                        $workflow['stage'] = 'events';
                        $workflow['stage_percentage'] = 0;
                        $workflow['message'] =
                            'Fechamento concluído. Gerando eventos automáticos.';
                    }
                    break;

                case 'events':
                    $eventsService->generate(
                        $timeClosing->fresh(),
                        $timeClosing->payroll_competency_id,
                    );

                    $workflow['events_count'] =
                        EmployeeVariableEvent::query()
                            ->where(
                                'payroll_competency_id',
                                $timeClosing->payroll_competency_id
                            )
                            ->where(
                                'notes',
                                'like',
                                "%fechamento de ponto #{$timeClosing->id}%"
                            )
                            ->count();

                    $workflow['payroll_run_ids'] =
                        $this->payrollRuns($timeClosing)
                            ->pluck('id')
                            ->map(fn ($id) => (int) $id)
                            ->values()
                            ->all();

                    $workflow['payroll_run_index'] = 0;
                    $workflow['stage'] = 'payroll_prepare';
                    $workflow['stage_percentage'] = 0;
                    $workflow['message'] =
                        'Eventos gerados. Preparando folhas.';
                    break;

                case 'payroll_prepare':
                    $run = $this->currentPayrollRun($workflow);

                    if (! $run) {
                        $workflow['stage'] = 'completed';
                        $workflow['stage_percentage'] = 100;
                        $workflow['message'] =
                            'Fechamento, eventos e folhas concluídos.';
                        break;
                    }

                    $payrollService->startAdminWebReprocess(
                        $run,
                        force: true
                    );

                    $workflow['stage'] = 'payroll';
                    $workflow['stage_percentage'] = 0;
                    $workflow['current_payroll_run_id'] = $run->id;
                    $workflow['message'] =
                        "Processando folha #{$run->id}.";
                    break;

                case 'payroll':
                    $run = $this->currentPayrollRun($workflow);

                    if (! $run) {
                        $workflow['stage'] = 'completed';
                        $workflow['stage_percentage'] = 100;
                        $workflow['message'] =
                            'Fechamento, eventos e folhas concluídos.';
                        break;
                    }

                    $status = $payrollService->processAdminWebChunk(
                        $run,
                        3
                    );

                    $workflow['stage_percentage'] =
                        (int) ($status['percentage'] ?? 0);

                    $workflow['message'] =
                        $status['message']
                        ?? "Processando folha #{$run->id}.";

                    if (($status['status'] ?? null) === 'completed') {
                        $workflow['payroll_run_index'] =
                            (int) ($workflow['payroll_run_index'] ?? 0) + 1;

                        $workflow['processed_payroll_runs'] =
                            (int) ($workflow['processed_payroll_runs'] ?? 0) + 1;

                        $workflow['stage'] = 'payroll_prepare';
                        $workflow['stage_percentage'] = 0;
                        $workflow['message'] =
                            'Folha concluída. Preparando próxima folha.';
                    }
                    break;

                case 'completed':
                    break;

                case 'failed':
                    return response()->json(
                        $this->publicStatus($workflow),
                        422
                    );

                default:
                    throw new \RuntimeException(
                        'Etapa desconhecida do processamento: '
                        . ($workflow['stage'] ?? '-')
                    );
            }

            $workflow['updated_at'] = now()->toIso8601String();

            $this->saveWorkflow($timeClosing, $workflow);

            return response()->json(
                $this->publicStatus($workflow)
            );
        } catch (Throwable $e) {
            report($e);

            $workflow['stage'] = 'failed';
            $workflow['message'] = $e->getMessage();
            $workflow['error'] = $e->getMessage();
            $workflow['updated_at'] = now()->toIso8601String();

            $this->saveWorkflow($timeClosing, $workflow);

            return response()->json(
                $this->publicStatus($workflow),
                500
            );
        }
    }

    public function status(TimeClosing $timeClosing): JsonResponse
    {
        return response()->json(
            $this->publicStatus(
                $this->workflow($timeClosing)
            )
        );
    }

    public function restart(TimeClosing $timeClosing): JsonResponse
    {
        Cache::forget($this->workflowKey($timeClosing));
        Cache::forget(
            "voktar:time-closing:web:{$timeClosing->id}"
        );

        foreach ($this->payrollRuns($timeClosing) as $run) {
            Cache::forget(
                "voktar:payroll-run:web:{$run->id}"
            );
        }

        $workflow = $this->initializeWorkflow(
            $timeClosing,
            force: true
        );

        return response()->json(
            $this->publicStatus($workflow)
        );
    }

    protected function initializeWorkflow(
        TimeClosing $closing,
        bool $force = false
    ): array {
        $key = $this->workflowKey($closing);

        if (! $force && Cache::has($key)) {
            $cached = Cache::get($key);

            if (is_array($cached)) {
                return $cached;
            }
        }

        $runs = $this->payrollRuns($closing);

        if ($runs->isEmpty()) {
            abort(
                422,
                'Nenhuma folha CLT ou Aprendiz encontrada para esta empresa e competência.'
            );
        }

        $workflow = [
            'stage' => 'closing_prepare',
            'stage_percentage' => 0,
            'message' => 'Fluxo preparado.',
            'error' => null,
            'events_count' => 0,
            'payroll_run_ids' => $runs
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            'payroll_run_index' => 0,
            'processed_payroll_runs' => 0,
            'total_payroll_runs' => $runs->count(),
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->saveWorkflow($closing, $workflow);

        return $workflow;
    }

    protected function payrollRuns(TimeClosing $closing)
    {
        return PayrollRun::query()
            ->where('company_id', $closing->company_id)
            ->where(
                'payroll_competency_id',
                $closing->payroll_competency_id
            )
            ->whereIn('run_type', [
                'payroll_clt',
                'payroll_apprentice',
            ])
            ->orderBy('id')
            ->get();
    }

    protected function currentPayrollRun(
        array $workflow
    ): ?PayrollRun {
        $ids = $workflow['payroll_run_ids'] ?? [];
        $index = (int) ($workflow['payroll_run_index'] ?? 0);
        $id = $ids[$index] ?? null;

        return $id ? PayrollRun::find($id) : null;
    }

    /**
     * IMPORTANTE:
     * Não usar Cache::get($key, $this->initializeWorkflow(...)).
     *
     * Em PHP, o segundo argumento é avaliado antes da chamada do método.
     * Isso reiniciava o fluxo a cada consulta e mantinha a tela travada em 5%.
     */
    protected function workflow(TimeClosing $closing): array
    {
        $key = $this->workflowKey($closing);

        if (Cache::has($key)) {
            $cached = Cache::get($key);

            if (is_array($cached)) {
                return $cached;
            }
        }

        return $this->initializeWorkflow(
            $closing,
            force: true
        );
    }

    protected function saveWorkflow(
        TimeClosing $closing,
        array $workflow
    ): void {
        Cache::put(
            $this->workflowKey($closing),
            $workflow,
            now()->addHours(4)
        );
    }

    protected function workflowKey(
        TimeClosing $closing
    ): string {
        return "voktar:closing-workflow:{$closing->id}";
    }

    protected function publicStatus(
        array $workflow
    ): array {
        $stage = $workflow['stage'] ?? 'closing_prepare';

        $stagePercentage = (int) (
            $workflow['stage_percentage'] ?? 0
        );

        $overall = match ($stage) {
            'closing_prepare' => 0,
            'closing' => 5 + (int) floor(
                $stagePercentage * 0.35
            ),
            'events' => 40,
            'payroll_prepare' => 50,
            'payroll' => $this->payrollOverallPercentage(
                $workflow,
                $stagePercentage
            ),
            'completed' => 100,
            'failed' => 0,
            default => 0,
        };

        return [
            'status' => match ($stage) {
                'completed' => 'completed',
                'failed' => 'failed',
                default => 'processing',
            },
            'stage' => $stage,
            'stage_label' => match ($stage) {
                'closing_prepare' => 'Preparando fechamento',
                'closing' => 'Recalculando fechamento',
                'events' => 'Gerando eventos',
                'payroll_prepare' => 'Preparando folha',
                'payroll' => 'Reprocessando folha',
                'completed' => 'Concluído',
                'failed' => 'Falhou',
                default => 'Processando',
            },
            'percentage' => min(100, max(0, $overall)),
            'stage_percentage' => min(
                100,
                max(0, $stagePercentage)
            ),
            'message' =>
                $workflow['message'] ?? 'Processando.',
            'error' => $workflow['error'] ?? null,
            'events_count' =>
                (int) ($workflow['events_count'] ?? 0),
            'processed_payroll_runs' =>
                (int) (
                    $workflow['processed_payroll_runs'] ?? 0
                ),
            'total_payroll_runs' =>
                (int) ($workflow['total_payroll_runs'] ?? 0),
        ];
    }

    protected function payrollOverallPercentage(
        array $workflow,
        int $stagePercentage
    ): int {
        $totalRuns = max(
            1,
            (int) ($workflow['total_payroll_runs'] ?? 1)
        );

        $completedRuns = (int) (
            $workflow['processed_payroll_runs'] ?? 0
        );

        $runFraction = (
            $completedRuns + ($stagePercentage / 100)
        ) / $totalRuns;

        return 50 + (int) floor($runFraction * 50);
    }
}
