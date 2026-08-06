<?php

namespace App\Services\Integrations\Solides;

use App\Models\Employee;
use App\Models\EmployeeExternalMapping;
use App\Models\PointIntegration;
use App\Models\TimeEntry;
use App\Models\TimeEntryImport;
use App\Models\TimeEntryImportItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SolidesPunchImportService
{
    /**
     * Tamanho reduzido para evitar timeout no Laravel Cloud.
     * Cada registro da API pode gerar até duas marcações.
     */
    private const PAGE_SIZE = 50;

    public function import(PointIntegration $integration, string $startDate, string $endDate): TimeEntryImport
    {
        $import = $this->startWebImport($integration, $startDate, $endDate);

        do {
            $status = $this->processNextPage($import->fresh());
        } while (($status['status'] ?? null) === 'processing');

        return $import->fresh();
    }

    public function startWebImport(
        PointIntegration $integration,
        string $startDate,
        string $endDate
    ): TimeEntryImport {
        return TimeEntryImport::create([
            'company_id' => $integration->company_id,
            'point_integration_id' => $integration->id,
            'provider' => 'solides',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'processing',
            'total_records' => 0,
            'imported_records' => 0,
            'ignored_records' => 0,
            'metadata' => [
                'api_total_pages' => null,
                'api_last_page_processed' => -1,
                'api_next_page' => 0,
                'api_size' => self::PAGE_SIZE,
                'period_start' => $startDate,
                'period_end' => $endDate,
                'web_chunk_mode' => true,
                'chunk_version' => 2,
            ],
            'started_at' => now(),
        ]);
    }

    public function processNextPage(TimeEntryImport $import): array
    {
        if (in_array($import->status, ['completed', 'failed'], true)) {
            return $this->statusPayload($import);
        }

        $integration = $import->pointIntegration;

        if (! $integration) {
            $import->update([
                'status' => 'failed',
                'error_message' => 'Integração de ponto não encontrada.',
                'finished_at' => now(),
            ]);

            return $this->statusPayload($import->fresh());
        }

        $metadata = is_array($import->metadata) ? $import->metadata : [];
        $page = max(0, (int) ($metadata['api_next_page'] ?? 0));

        // Importações iniciadas na versão antiga (500 registros por página)
        // devem ser reiniciadas, para não alterar a paginação no meio do processo.
        $storedSize = (int) ($metadata['api_size'] ?? self::PAGE_SIZE);

        if ($storedSize !== self::PAGE_SIZE && $page > 0) {
            $import->update([
                'status' => 'failed',
                'error_message' => 'Esta importação foi iniciada com lote antigo de 500 registros. Inicie um novo reprocessamento.',
                'finished_at' => now(),
            ]);

            return $this->statusPayload($import->fresh());
        }

        $size = self::PAGE_SIZE;

        try {
            $service = new SolidesPointService($integration);

            $result = $service->getPunchesByPeriod(
                startDate: Carbon::parse($import->start_date)->subDay()->toDateString(),
                endDate: Carbon::parse($import->end_date)->addDay()->toDateString(),
                extraParams: [
                    'page' => $page,
                    'size' => $size,
                ],
            );

            if (! ($result['success'] ?? false)) {
                throw new RuntimeException(
                    $result['message'] ?? "Erro ao importar a página {$page} da Sólides."
                );
            }

            $data = $result['data'] ?? [];
            $content = $data['content'] ?? [];

            if (! is_array($content)) {
                $content = [];
            }

            $totalPages = max(1, (int) ($data['totalPages'] ?? 1));
            $pageImported = 0;
            $pageIgnored = 0;

            DB::transaction(function () use (
                $integration,
                $import,
                $content,
                &$pageImported,
                &$pageIgnored
            ): void {
                foreach ($content as $payload) {
                    if (! is_array($payload)) {
                        $pageIgnored++;
                        continue;
                    }

                    $resultItem = $this->processPayload($integration, $import, $payload);

                    $pageImported += (int) $resultItem['imported'];
                    $pageIgnored += (int) $resultItem['ignored'];
                }
            }, 3);

            $nextPage = $page + 1;
            $completed = $nextPage >= $totalPages;

            $metadata = array_merge($metadata, [
                'api_total_pages' => $totalPages,
                'api_last_page_processed' => $page,
                'api_next_page' => $nextPage,
                'api_size' => $size,
                'period_start' => $import->start_date->format('Y-m-d'),
                'period_end' => $import->end_date->format('Y-m-d'),
                'last_page_records' => count($content),
                'updated_at' => now()->toIso8601String(),
                'chunk_version' => 2,
            ]);

            $import->update([
                'status' => $completed ? 'completed' : 'processing',
                'total_records' => (int) $import->total_records + count($content),
                'imported_records' => (int) $import->imported_records + $pageImported,
                'ignored_records' => (int) $import->ignored_records + $pageIgnored,
                'metadata' => $metadata,
                'error_message' => null,
                'finished_at' => $completed ? now() : null,
            ]);

            if ($completed) {
                $integration->update(['last_sync_at' => now()]);
            }
        } catch (Throwable $e) {
            Log::error('Erro no reprocessamento web Sólides/Tangerino', [
                'integration_id' => $integration->id,
                'import_id' => $import->id,
                'page' => $page,
                'page_size' => $size,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $metadata['failed_page'] = $page;
            $metadata['api_next_page'] = $page;
            $metadata['api_size'] = $size;

            $import->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'metadata' => $metadata,
                'finished_at' => now(),
            ]);
        }

        return $this->statusPayload($import->fresh());
    }

    public function statusPayload(TimeEntryImport $import): array
    {
        $metadata = is_array($import->metadata) ? $import->metadata : [];
        $totalPages = max(1, (int) ($metadata['api_total_pages'] ?? 1));
        $nextPage = max(0, (int) ($metadata['api_next_page'] ?? 0));
        $processedPages = min($totalPages, $nextPage);

        return [
            'id' => $import->id,
            'status' => $import->status,
            'total_pages' => $totalPages,
            'processed_pages' => $processedPages,
            'percentage' => $import->status === 'completed'
                ? 100
                : min(99, (int) floor(($processedPages / $totalPages) * 100)),
            'total_records' => (int) $import->total_records,
            'imported_records' => (int) $import->imported_records,
            'ignored_records' => (int) $import->ignored_records,
            'message' => $import->status === 'completed'
                ? 'Importação concluída com sucesso.'
                : ($import->status === 'failed'
                    ? ($import->error_message ?: 'Falha no reprocessamento.')
                    : 'Processando página ' . ($processedPages + 1) . " de {$totalPages}."),
        ];
    }

    protected function processPayload(PointIntegration $integration, TimeEntryImport $import, array $payload): array
    {
        $externalEmployeeId = $this->resolveExternalEmployeeId($payload);
        $externalEmployeeName = $this->resolveExternalEmployeeName($payload);
        $externalEmployeeCode = $this->resolveExternalEmployeeCode($payload);
        $employee = $this->resolveEmployee($externalEmployeeId, $externalEmployeeCode);
        $marks = $this->resolveMarks($payload);

        if (empty($marks)) {
            TimeEntryImportItem::create([
                'time_entry_import_id' => $import->id,
                'employee_id' => $employee?->id,
                'provider' => 'solides',
                'external_id' => (string) ($payload['id'] ?? ''),
                'external_employee_id' => $externalEmployeeId,
                'external_employee_name' => $externalEmployeeName,
                'entry_date' => null,
                'entry_datetime' => null,
                'type' => 'unknown',
                'status' => 'ignored',
                'raw_payload' => $payload,
            ]);

            return ['imported' => 0, 'ignored' => 1];
        }

        $imported = 0;
        $ignored = 0;

        foreach ($marks as $mark) {
            $dateTime = $mark['datetime'];

            $importItem = TimeEntryImportItem::create([
                'time_entry_import_id' => $import->id,
                'employee_id' => $employee?->id,
                'provider' => 'solides',
                'external_id' => $mark['external_id'],
                'external_employee_id' => $externalEmployeeId,
                'external_employee_name' => $externalEmployeeName,
                'entry_date' => $dateTime->toDateString(),
                'entry_datetime' => $dateTime,
                'type' => $mark['type'],
                'status' => $employee ? 'imported' : 'employee_not_found',
                'raw_payload' => $payload,
            ]);

            if (! $employee) {
                $ignored++;
                continue;
            }

            TimeEntry::updateOrCreate(
                [
                    'provider' => 'solides',
                    'external_id' => $mark['external_id'],
                ],
                [
                    'company_id' => $integration->company_id,
                    'employee_id' => $employee->id,
                    'time_entry_import_id' => $import->id,
                    'time_entry_import_item_id' => $importItem->id,
                    'source' => 'api',
                    'external_employee_id' => $externalEmployeeId,
                    'entry_date' => $dateTime->toDateString(),
                    'entry_datetime' => $dateTime,
                    'type' => $mark['type'],
                    'status' => 'valid',
                    'raw_payload' => $payload,
                ]
            );

            $imported++;
        }

        return ['imported' => $imported, 'ignored' => $ignored];
    }

    protected function resolveMarks(array $payload): array
    {
        $marks = [];
        $dateIn = $payload['dateInFull'] ?? $payload['dateIn'] ?? null;
        $dateOut = $payload['dateOutFull'] ?? $payload['dateOut'] ?? null;

        if ($dateIn) {
            $marks[] = [
                'type' => 'entrada',
                'datetime' => $this->timestampToCarbon($dateIn),
                'external_id' => $this->makeExternalMarkId($payload, 'in', $dateIn),
            ];
        }

        if ($dateOut) {
            $marks[] = [
                'type' => 'saida',
                'datetime' => $this->timestampToCarbon($dateOut),
                'external_id' => $this->makeExternalMarkId($payload, 'out', $dateOut),
            ];
        }

        return $marks;
    }

    protected function makeExternalMarkId(array $payload, string $direction, mixed $timestamp): string
    {
        $baseId = (string) ($payload['id'] ?? sha1(json_encode($payload)));
        $nsr = $direction === 'in' ? ($payload['nsrIn'] ?? null) : ($payload['nsrOut'] ?? null);

        return implode('-', array_filter([$baseId, $direction, $nsr, (string) $timestamp]));
    }

    protected function resolveEmployee(?string $externalEmployeeId, ?string $externalEmployeeCode): ?Employee
    {
        if (! $externalEmployeeId && ! $externalEmployeeCode) {
            return null;
        }

        $mapping = EmployeeExternalMapping::query()
            ->where('provider', 'solides')
            ->where(function ($query) use ($externalEmployeeId, $externalEmployeeCode) {
                if ($externalEmployeeId) {
                    $query->orWhere('external_employee_id', $externalEmployeeId);
                }

                if ($externalEmployeeCode) {
                    $query->orWhere('external_code', $externalEmployeeCode);
                }
            })
            ->first();

        return $mapping?->employee;
    }

    protected function resolveExternalEmployeeId(array $payload): ?string
    {
        return $this->stringOrNull(
            $payload['employeeExternalId']
                ?? data_get($payload, 'employee.externalId')
                ?? data_get($payload, 'employee.cpf')
                ?? $payload['employeeId']
                ?? data_get($payload, 'employee.id')
                ?? null
        );
    }

    protected function resolveExternalEmployeeCode(array $payload): ?string
    {
        return $this->stringOrNull(
            $payload['employeeExternalId']
                ?? data_get($payload, 'employee.externalId')
                ?? data_get($payload, 'employee.cpf')
                ?? null
        );
    }

    protected function resolveExternalEmployeeName(array $payload): ?string
    {
        return $this->stringOrNull(
            $payload['employeeName']
                ?? data_get($payload, 'employee.name')
                ?? null
        );
    }

    protected function timestampToCarbon(mixed $timestamp): Carbon
    {
        if (is_numeric($timestamp)) {
            return Carbon::createFromTimestampMs((int) $timestamp);
        }

        return Carbon::parse((string) $timestamp);
    }

    protected function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
