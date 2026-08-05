<?php

namespace App\Services\Integrations\Solides;

use App\Models\Employee;
use App\Models\EmployeeExternalMapping;
use App\Models\PointIntegration;
use App\Models\TimeEntry;
use App\Models\TimeEntryImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SolidesPunchImportService
{
    private const PAGE_SIZE = 500;
    private const PAGE_ATTEMPTS = 3;

    /** @var array<string, Employee|null> */
    protected array $employeeCache = [];

    public function import(PointIntegration $integration, string $startDate, string $endDate): TimeEntryImport
    {
        $import = TimeEntryImport::create([
            'company_id' => $integration->company_id,
            'point_integration_id' => $integration->id,
            'provider' => 'solides',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'processing',
            'started_at' => now(),
            'metadata' => $this->initialMetadata($startDate, $endDate),
        ]);

        return $this->executeImport($integration, $import, startPage: 0);
    }

    public function resume(TimeEntryImport $import): TimeEntryImport
    {
        $import->loadMissing('pointIntegration');

        $integration = $import->pointIntegration;

        if (! $integration) {
            throw new RuntimeException('A integração de ponto vinculada à importação não foi encontrada.');
        }

        if ($import->provider !== 'solides') {
            throw new RuntimeException('Somente importações da Sólides/Tangerino podem ser retomadas por este serviço.');
        }

        $metadata = is_array($import->metadata) ? $import->metadata : [];
        $lastCompletedPage = (int) ($metadata['api_last_page_processed'] ?? -1);

        $import->update([
            'status' => 'processing',
            'error_message' => null,
            'finished_at' => null,
            'started_at' => $import->started_at ?: now(),
        ]);

        return $this->executeImport(
            integration: $integration,
            import: $import,
            startPage: max(0, $lastCompletedPage + 1),
        );
    }

    protected function executeImport(
        PointIntegration $integration,
        TimeEntryImport $import,
        int $startPage,
    ): TimeEntryImport {
        $service = new SolidesPointService($integration);
        $metadata = array_merge(
            $this->initialMetadata($import->start_date->toDateString(), $import->end_date->toDateString()),
            is_array($import->metadata) ? $import->metadata : [],
        );

        $page = $startPage;
        $totalPages = max(1, (int) ($metadata['api_total_pages'] ?? 1));
        $startedAt = microtime(true);

        try {
            do {
                $result = $this->fetchPageWithRetry(
                    service: $service,
                    startDate: $import->start_date->toDateString(),
                    endDate: $import->end_date->toDateString(),
                    page: $page,
                    size: self::PAGE_SIZE,
                    integrationId: $integration->id,
                    importId: $import->id,
                );

                $data = is_array($result['data'] ?? null) ? $result['data'] : [];
                $content = is_array($data['content'] ?? null) ? $data['content'] : [];

                $totalPages = max(1, (int) ($data['totalPages'] ?? $totalPages));
                $apiTotalElements = isset($data['totalElements']) ? (int) $data['totalElements'] : null;

                $pageStats = DB::transaction(fn (): array => $this->persistPage(
                    integration: $integration,
                    import: $import,
                    payloads: $content,
                    requestedStart: $import->start_date->copy()->startOfDay(),
                    requestedEnd: $import->end_date->copy()->endOfDay(),
                ));

                $metadata = $this->mergeStats($metadata, $pageStats);
                $metadata['api_total_pages'] = $totalPages;
                $metadata['api_last_page_processed'] = $page;
                $metadata['api_size'] = self::PAGE_SIZE;
                $metadata['api_total_elements'] = $apiTotalElements;
                $metadata['progress_percent'] = round((($page + 1) / $totalPages) * 100, 2);
                $metadata['duration_seconds'] = round(microtime(true) - $startedAt, 2);
                $metadata['last_page_finished_at'] = now()->toIso8601String();

                $import->update([
                    'status' => 'processing',
                    'total_records' => (int) ($metadata['payloads_received'] ?? 0),
                    'imported_records' => (int) ($metadata['marks_persisted'] ?? 0),
                    'ignored_records' => (int) ($metadata['marks_ignored'] ?? 0),
                    'metadata' => $metadata,
                ]);

                $page++;
            } while ($page < $totalPages);

            $warningCount = (int) ($metadata['employee_not_found'] ?? 0)
                + (int) ($metadata['invalid_payloads'] ?? 0)
                + (int) ($metadata['invalid_marks'] ?? 0);

            $status = $warningCount > 0 ? 'completed_with_warnings' : 'completed';

            $metadata['progress_percent'] = 100;
            $metadata['duration_seconds'] = round(microtime(true) - $startedAt, 2);
            $metadata['reconciliation'] = [
                'marks_received' => (int) ($metadata['marks_received'] ?? 0),
                'marks_persisted' => (int) ($metadata['marks_persisted'] ?? 0),
                'marks_ignored' => (int) ($metadata['marks_ignored'] ?? 0),
                'outside_period' => (int) ($metadata['outside_period'] ?? 0),
                'balanced' => (int) ($metadata['marks_received'] ?? 0)
                    === (int) ($metadata['marks_persisted'] ?? 0)
                    + (int) ($metadata['marks_ignored'] ?? 0)
                    + (int) ($metadata['outside_period'] ?? 0),
            ];

            $import->update([
                'status' => $status,
                'total_records' => (int) ($metadata['payloads_received'] ?? 0),
                'imported_records' => (int) ($metadata['marks_persisted'] ?? 0),
                'ignored_records' => (int) ($metadata['marks_ignored'] ?? 0),
                'metadata' => $metadata,
                'error_message' => null,
                'finished_at' => now(),
            ]);

            $integration->update(['last_sync_at' => now()]);
        } catch (Throwable $e) {
            $metadata['failed_page'] = $page;
            $metadata['duration_seconds'] = round(microtime(true) - $startedAt, 2);
            $metadata['failed_at'] = now()->toIso8601String();

            Log::error('Erro na importação Sólides/Tangerino', [
                'integration_id' => $integration->id,
                'import_id' => $import->id,
                'start_date' => $import->start_date?->toDateString(),
                'end_date' => $import->end_date?->toDateString(),
                'page' => $page,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            $import->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'metadata' => $metadata,
                'finished_at' => now(),
            ]);
        }

        return $import->refresh();
    }

    protected function fetchPageWithRetry(
        SolidesPointService $service,
        string $startDate,
        string $endDate,
        int $page,
        int $size,
        int $integrationId,
        int $importId,
    ): array {
        $lastMessage = 'Falha desconhecida ao consultar a API.';

        for ($attempt = 1; $attempt <= self::PAGE_ATTEMPTS; $attempt++) {
            $result = $service->getPunchesByPeriod(
                startDate: Carbon::parse($startDate)->subDay()->toDateString(),
                endDate: Carbon::parse($endDate)->addDay()->toDateString(),
                extraParams: [
                    'page' => $page,
                    'size' => $size,
                ],
            );

            if ($result['success'] ?? false) {
                return $result;
            }

            $lastMessage = (string) ($result['message'] ?? $lastMessage);

            Log::warning('Tentativa de página da Sólides falhou', [
                'integration_id' => $integrationId,
                'import_id' => $importId,
                'page' => $page,
                'attempt' => $attempt,
                'message' => $lastMessage,
                'status' => $result['status'] ?? null,
            ]);

            if ($attempt < self::PAGE_ATTEMPTS) {
                sleep($attempt * 2);
            }
        }

        throw new RuntimeException("Falha ao importar a página {$page} após " . self::PAGE_ATTEMPTS . " tentativas: {$lastMessage}");
    }

    protected function persistPage(
        PointIntegration $integration,
        TimeEntryImport $import,
        array $payloads,
        Carbon $requestedStart,
        Carbon $requestedEnd,
    ): array {
        $now = now();
        $importItems = [];
        $entryCandidates = [];

        $stats = [
            'payloads_received' => count($payloads),
            'marks_received' => 0,
            'marks_persisted' => 0,
            'marks_inserted' => 0,
            'marks_updated' => 0,
            'marks_ignored' => 0,
            'duplicates_in_page' => 0,
            'employee_not_found' => 0,
            'invalid_payloads' => 0,
            'invalid_marks' => 0,
            'outside_period' => 0,
        ];

        foreach ($payloads as $payload) {
            if (! is_array($payload)) {
                $stats['invalid_payloads']++;
                $stats['marks_ignored']++;
                continue;
            }

            $externalEmployeeId = $this->resolveExternalEmployeeId($payload);
            $externalEmployeeCode = $this->resolveExternalEmployeeCode($payload);
            $externalEmployeeName = $this->resolveExternalEmployeeName($payload);
            $employee = $this->resolveEmployee($externalEmployeeId, $externalEmployeeCode);
            $marks = $this->resolveMarks($payload);

            if ($marks === []) {
                $stats['marks_ignored']++;

                $importItems[] = [
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
                    'raw_payload' => $this->encodeJson($payload),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                continue;
            }

            foreach ($marks as $mark) {
                $stats['marks_received']++;

                $dateTime = $mark['datetime'] ?? null;

                if (! $dateTime instanceof Carbon) {
                    $stats['invalid_marks']++;
                    $stats['marks_ignored']++;
                    continue;
                }

                $localDateTime = $dateTime->copy()->setTimezone(config('app.timezone', 'America/Cuiaba'));
                $isOutsidePeriod = $localDateTime->lt($requestedStart) || $localDateTime->gt($requestedEnd);
                $status = $isOutsidePeriod
                    ? 'outside_period'
                    : ($employee ? 'imported' : 'employee_not_found');

                $importItems[] = [
                    'time_entry_import_id' => $import->id,
                    'employee_id' => $employee?->id,
                    'provider' => 'solides',
                    'external_id' => $mark['external_id'],
                    'external_employee_id' => $externalEmployeeId,
                    'external_employee_name' => $externalEmployeeName,
                    'entry_date' => $localDateTime->toDateString(),
                    'entry_datetime' => $localDateTime->copy()->utc()->format('Y-m-d H:i:s'),
                    'type' => $mark['type'],
                    'status' => $status,
                    'raw_payload' => $this->encodeJson($payload),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($isOutsidePeriod) {
                    $stats['outside_period']++;
                    continue;
                }

                if (! $employee) {
                    $stats['employee_not_found']++;
                    $stats['marks_ignored']++;
                    continue;
                }

                $key = 'solides|' . $mark['external_id'];

                if (isset($entryCandidates[$key])) {
                    $stats['duplicates_in_page']++;
                }

                $entryCandidates[$key] = [
                    'company_id' => $integration->company_id,
                    'employee_id' => $employee->id,
                    'time_entry_import_id' => $import->id,
                    'provider' => 'solides',
                    'source' => 'api',
                    'external_id' => $mark['external_id'],
                    'external_employee_id' => $externalEmployeeId,
                    'entry_date' => $localDateTime->toDateString(),
                    'entry_datetime' => $localDateTime->copy()->utc()->format('Y-m-d H:i:s'),
                    'type' => $mark['type'],
                    'status' => 'valid',
                    'raw_payload' => $this->encodeJson($payload),
                    'notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($importItems, 1000) as $chunk) {
            DB::table('time_entry_import_items')->insert($chunk);
        }

        if ($entryCandidates === []) {
            return $stats;
        }

        $externalIds = array_values(array_unique(array_column($entryCandidates, 'external_id')));

        $existingIds = TimeEntry::query()
            ->where('provider', 'solides')
            ->whereIn('external_id', $externalIds)
            ->pluck('external_id')
            ->all();

        $existingLookup = array_fill_keys($existingIds, true);

        $importItemIds = DB::table('time_entry_import_items')
            ->where('time_entry_import_id', $import->id)
            ->where('provider', 'solides')
            ->whereIn('external_id', $externalIds)
            ->orderByDesc('id')
            ->get(['id', 'external_id'])
            ->unique('external_id')
            ->pluck('id', 'external_id')
            ->all();

        $rows = [];

        foreach ($entryCandidates as $candidate) {
            $candidate['time_entry_import_item_id'] = $importItemIds[$candidate['external_id']] ?? null;
            $rows[] = $candidate;

            if (isset($existingLookup[$candidate['external_id']])) {
                $stats['marks_updated']++;
            } else {
                $stats['marks_inserted']++;
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('time_entries')->upsert(
                $chunk,
                ['provider', 'external_id'],
                [
                    'company_id',
                    'employee_id',
                    'time_entry_import_id',
                    'time_entry_import_item_id',
                    'source',
                    'external_employee_id',
                    'entry_date',
                    'entry_datetime',
                    'type',
                    'status',
                    'raw_payload',
                    'notes',
                    'updated_at',
                ],
            );
        }

        $stats['marks_persisted'] = count($rows);

        return $stats;
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
        $baseId = (string) ($payload['id'] ?? sha1($this->encodeJson($payload)));
        $nsr = $direction === 'in' ? ($payload['nsrIn'] ?? null) : ($payload['nsrOut'] ?? null);

        return implode('-', array_filter([
            $baseId,
            $direction,
            $nsr,
            (string) $timestamp,
        ], fn ($value) => $value !== null && $value !== ''));
    }

    protected function resolveEmployee(?string $externalEmployeeId, ?string $externalEmployeeCode): ?Employee
    {
        $cacheKey = ($externalEmployeeId ?? '') . '|' . ($externalEmployeeCode ?? '');

        if (array_key_exists($cacheKey, $this->employeeCache)) {
            return $this->employeeCache[$cacheKey];
        }

        if (! $externalEmployeeId && ! $externalEmployeeCode) {
            return $this->employeeCache[$cacheKey] = null;
        }

        $mapping = EmployeeExternalMapping::query()
            ->with('employee')
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

        return $this->employeeCache[$cacheKey] = $mapping?->employee;
    }

    protected function resolveExternalEmployeeId(array $payload): ?string
    {
        return $this->stringOrNull(
            $payload['employeeExternalId']
                ?? data_get($payload, 'employee.externalId')
                ?? data_get($payload, 'employee.cpf')
                ?? $payload['employeeId']
                ?? data_get($payload, 'employee.id')
                ?? null,
        );
    }

    protected function resolveExternalEmployeeCode(array $payload): ?string
    {
        return $this->stringOrNull(
            $payload['employeeExternalId']
                ?? data_get($payload, 'employee.externalId')
                ?? data_get($payload, 'employee.cpf')
                ?? null,
        );
    }

    protected function resolveExternalEmployeeName(array $payload): ?string
    {
        return $this->stringOrNull(
            $payload['employeeName']
                ?? data_get($payload, 'employee.name')
                ?? null,
        );
    }

    protected function timestampToCarbon(mixed $timestamp): Carbon
    {
        if (is_numeric($timestamp)) {
            return Carbon::createFromTimestampMs((int) $timestamp, 'UTC')
                ->setTimezone(config('app.timezone', 'America/Cuiaba'));
        }

        return Carbon::parse((string) $timestamp)
            ->setTimezone(config('app.timezone', 'America/Cuiaba'));
    }

    protected function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    protected function initialMetadata(string $startDate, string $endDate): array
    {
        return [
            'api_total_pages' => 1,
            'api_last_page_processed' => -1,
            'api_size' => self::PAGE_SIZE,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'payloads_received' => 0,
            'marks_received' => 0,
            'marks_persisted' => 0,
            'marks_inserted' => 0,
            'marks_updated' => 0,
            'marks_ignored' => 0,
            'duplicates_in_page' => 0,
            'employee_not_found' => 0,
            'invalid_payloads' => 0,
            'invalid_marks' => 0,
            'outside_period' => 0,
            'progress_percent' => 0,
        ];
    }

    protected function mergeStats(array $metadata, array $pageStats): array
    {
        foreach ($pageStats as $key => $value) {
            $metadata[$key] = (int) ($metadata[$key] ?? 0) + (int) $value;
        }

        return $metadata;
    }

    protected function encodeJson(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
    }
}
