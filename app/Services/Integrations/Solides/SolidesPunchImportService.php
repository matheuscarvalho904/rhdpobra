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
use Throwable;

class SolidesPunchImportService
{
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
        ]);

        try {
            DB::transaction(function () use ($integration, $startDate, $endDate, $import): void {
                $service = new SolidesPointService($integration);

                $result = $service->getPunchesByPeriod(
                    startDate: $startDate,
                    endDate: \Carbon\Carbon::parse($endDate)->addDay()->toDateString(),
                    extraParams: [
                        'page' => 0,
                        'size' => 500,
                    ],
                );

                if (! ($result['success'] ?? false)) {
                    throw new \RuntimeException($result['message'] ?? 'Erro ao importar marcações da Sólides.');
                }

                $data = $result['data'] ?? [];
                $content = $data['content'] ?? [];

                $imported = 0;
                $ignored = 0;

                foreach ($content as $payload) {
                    $resultItem = $this->processPayload($integration, $import, $payload);

                    $imported += $resultItem['imported'];
                    $ignored += $resultItem['ignored'];
                }

                $import->update([
                    'status' => 'completed',
                    'total_records' => count($content),
                    'imported_records' => $imported,
                    'ignored_records' => $ignored,
                    'metadata' => [
                        'api_total_elements' => $data['totalElements'] ?? null,
                        'api_total_pages' => $data['totalPages'] ?? null,
                        'api_page' => $data['number'] ?? null,
                        'api_size' => $data['size'] ?? null,
                    ],
                    'finished_at' => now(),
                ]);

                $integration->update([
                    'last_sync_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            Log::error('Erro na importação Sólides/Tangerino', [
                'integration_id' => $integration->id,
                'import_id' => $import->id,
                'error' => $e->getMessage(),
            ]);

            $import->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }

        return $import->refresh();
    }

    protected function processPayload(PointIntegration $integration, TimeEntryImport $import, array $payload): array
    {
        $externalEmployeeId = $this->resolveExternalEmployeeId($payload);
        $externalEmployeeName = $this->resolveExternalEmployeeName($payload);
        $externalEmployeeCode = $this->resolveExternalEmployeeCode($payload);

        $employee = $this->resolveEmployee($externalEmployeeId, $externalEmployeeCode);

        $imported = 0;
        $ignored = 0;

        $marks = $this->resolveMarks($payload);

        if (empty($marks)) {
            TimeEntryImportItem::create([
                'time_entry_import_id' => $import->id,
                'employee_id' => $employee?->id,
                'provider' => 'solides',
                'external_id' => (string) ($payload['id'] ?? null),
                'external_employee_id' => $externalEmployeeId,
                'external_employee_name' => $externalEmployeeName,
                'entry_date' => null,
                'entry_datetime' => null,
                'type' => 'unknown',
                'status' => 'ignored',
                'raw_payload' => $payload,
            ]);

            return [
                'imported' => 0,
                'ignored' => 1,
            ];
        }

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

        return [
            'imported' => $imported,
            'ignored' => $ignored,
        ];
    }

    protected function resolveMarks(array $payload): array
    {
        $marks = [];

        $dateIn = $payload['dateInFull']
            ?? $payload['dateIn']
            ?? null;

        $dateOut = $payload['dateOutFull']
            ?? $payload['dateOut']
            ?? null;

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

        $nsr = $direction === 'in'
            ? ($payload['nsrIn'] ?? null)
            : ($payload['nsrOut'] ?? null);

        return implode('-', array_filter([
            $baseId,
            $direction,
            $nsr,
            (string) $timestamp,
        ]));
    }

    protected function resolveEmployee(?string $externalEmployeeId, ?string $externalEmployeeCode): ?Employee
    {
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
            $payload['employeeId']
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