<?php

namespace App\Services;

use App\Models\TimeEntry;
use App\Models\TimeEntryImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimeImportAuditService
{
    public function audit(TimeEntryImport $import): array
    {
        $metadata = is_array($import->metadata) ? $import->metadata : [];

        $itemCounts = $import->items()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($value) => (int) $value)
            ->all();

        $databaseMarks = TimeEntry::query()
            ->where('company_id', $import->company_id)
            ->where('provider', $import->provider)
            ->whereBetween('entry_date', [
                $import->start_date->toDateString(),
                $import->end_date->toDateString(),
            ])
            ->count();

        $oddDays = $this->oddDays($import);
        $duplicateDateTimes = $this->duplicateDateTimes($import);

        $reconciliation = is_array($metadata['reconciliation'] ?? null)
            ? $metadata['reconciliation']
            : [];

        $balanced = (bool) ($reconciliation['balanced'] ?? false);
        $completed = in_array($import->status, ['completed', 'completed_with_warnings'], true);
        $hasCriticalErrors = ! $completed || ! $balanced || $oddDays->isNotEmpty();

        return [
            'import_id' => $import->id,
            'status' => $import->status,
            'period_start' => $import->start_date->toDateString(),
            'period_end' => $import->end_date->toDateString(),
            'payloads_received' => (int) ($metadata['payloads_received'] ?? $import->total_records),
            'marks_received' => (int) ($metadata['marks_received'] ?? 0),
            'marks_persisted' => (int) ($metadata['marks_persisted'] ?? $import->imported_records),
            'marks_inserted' => (int) ($metadata['marks_inserted'] ?? 0),
            'marks_updated' => (int) ($metadata['marks_updated'] ?? 0),
            'marks_ignored' => (int) ($metadata['marks_ignored'] ?? $import->ignored_records),
            'outside_period' => (int) ($metadata['outside_period'] ?? 0),
            'employee_not_found' => (int) ($metadata['employee_not_found'] ?? ($itemCounts['employee_not_found'] ?? 0)),
            'database_marks_in_period' => $databaseMarks,
            'item_statuses' => $itemCounts,
            'odd_days_count' => $oddDays->count(),
            'odd_days' => $oddDays->values()->all(),
            'duplicate_datetime_count' => $duplicateDateTimes->count(),
            'duplicate_datetimes' => $duplicateDateTimes->values()->all(),
            'balanced' => $balanced,
            'can_close' => ! $hasCriticalErrors,
            'blocking_reasons' => array_values(array_filter([
                ! $completed ? 'A importação ainda não foi concluída.' : null,
                ! $balanced ? 'A quantidade de marcações recebidas não foi conciliada.' : null,
                $oddDays->isNotEmpty() ? 'Existem dias com quantidade ímpar de marcações.' : null,
            ])),
        ];
    }

    protected function oddDays(TimeEntryImport $import): Collection
    {
        return TimeEntry::query()
            ->select([
                'employee_id',
                'entry_date',
                DB::raw('count(*) as total'),
            ])
            ->where('company_id', $import->company_id)
            ->where('provider', $import->provider)
            ->whereBetween('entry_date', [
                $import->start_date->toDateString(),
                $import->end_date->toDateString(),
            ])
            ->groupBy('employee_id', 'entry_date')
            ->havingRaw('MOD(COUNT(*), 2) <> 0')
            ->orderBy('entry_date')
            ->get()
            ->map(fn ($row) => [
                'employee_id' => (int) $row->employee_id,
                'entry_date' => $row->entry_date->toDateString(),
                'total' => (int) $row->total,
            ]);
    }

    protected function duplicateDateTimes(TimeEntryImport $import): Collection
    {
        return TimeEntry::query()
            ->select([
                'employee_id',
                'entry_datetime',
                DB::raw('count(*) as total'),
            ])
            ->where('company_id', $import->company_id)
            ->where('provider', $import->provider)
            ->whereBetween('entry_date', [
                $import->start_date->toDateString(),
                $import->end_date->toDateString(),
            ])
            ->groupBy('employee_id', 'entry_datetime')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('entry_datetime')
            ->get()
            ->map(fn ($row) => [
                'employee_id' => (int) $row->employee_id,
                'entry_datetime' => $row->entry_datetime->format('Y-m-d H:i:s'),
                'total' => (int) $row->total,
            ]);
    }
}
