<?php

namespace App\Http\Controllers;

use App\Models\TimeEntryImport;
use App\Services\Integrations\Solides\SolidesPunchImportService;
use Illuminate\Http\JsonResponse;

class TimeEntryImportReprocessController extends Controller
{
    public function show(
        TimeEntryImport $timeEntryImport,
        SolidesPunchImportService $service
    ) {
        $integration = $timeEntryImport->pointIntegration;

        abort_unless($integration, 422, 'Integração de ponto não encontrada.');

        $newImport = $service->startWebImport(
            $integration,
            $timeEntryImport->start_date->format('Y-m-d'),
            $timeEntryImport->end_date->format('Y-m-d'),
        );

        return view('time-entry-imports.reprocess-progress', [
            'import' => $newImport,
            'processUrl' => route('time-entry-imports.reprocess.process', $newImport),
            'statusUrl' => route('time-entry-imports.reprocess.status', $newImport),
            'backUrl' => url('/app/time-entry-imports'),
        ]);
    }

    public function process(
        TimeEntryImport $timeEntryImport,
        SolidesPunchImportService $service
    ): JsonResponse {
        return response()->json($service->processNextPage($timeEntryImport));
    }

    public function status(
        TimeEntryImport $timeEntryImport,
        SolidesPunchImportService $service
    ): JsonResponse {
        return response()->json($service->statusPayload($timeEntryImport->fresh()));
    }
}
