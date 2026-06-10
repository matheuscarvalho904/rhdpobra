<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\Employee;
use App\Models\TimeEntry;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\URL;
use UnitEnum;

class TimePointInconsistencies extends Page
{
    protected static ?string $navigationLabel = 'Inconsistências';
    protected static ?string $title = 'Inconsistências de Ponto';
    protected static ?int $navigationSort = 13;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static string|UnitEnum|null $navigationGroup = 'Ponto';

    protected string $view = 'filament.pages.time-point-inconsistencies';

    public ?int $company_id = null;
    public ?string $start_date = null;
    public ?string $end_date = null;

    public array $companies = [];
    public array $rows = [];
    public int $totalInconsistencies = 0;
    public int $totalWithoutSolides = 0;
    public int $totalWithoutSchedule = 0;
    public int $totalPunchProblems = 0;

    public function mount(): void
    {
        $this->companies = Company::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
    }

    public function generateReport(): void
    {
        $this->rows = [];
        $this->totalInconsistencies = 0;
        $this->totalWithoutSolides = 0;
        $this->totalWithoutSchedule = 0;
        $this->totalPunchProblems = 0;

        $this->loadEmployeeInconsistencies();
        $this->loadPunchInconsistencies();

        $this->rows = collect($this->rows)
            ->sortBy([
                ['employee_name', 'asc'],
                ['date', 'asc'],
                ['type', 'asc'],
            ])
            ->values()
            ->toArray();

        $this->totalInconsistencies = count($this->rows);

        if ($this->totalInconsistencies === 0) {
            Notification::make()
                ->title('Nenhuma inconsistência encontrada')
                ->body('Não foram localizados problemas para os filtros informados.')
                ->success()
                ->send();
        }
    }

    protected function loadEmployeeInconsistencies(): void
    {
        $employees = Employee::query()
            ->with(['company', 'solidesMapping', 'workSchedules'])
            ->where('is_active', true)
            ->when($this->company_id, fn ($query) => $query->where('company_id', $this->company_id))
            ->orderBy('name')
            ->get();

        foreach ($employees as $employee) {
            if (! $employee->solidesMapping) {
                $this->totalWithoutSolides++;

                $this->rows[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name ?? '-',
                    'company' => $employee->company?->name ?? 'Sem Empresa',
                    'date' => '-',
                    'type' => 'Sem vínculo Sólides',
                    'severity' => 'warning',
                    'description' => 'Colaborador ativo sem vínculo externo com a Sólides/Tangerino.',
                    'entries_count' => null,
                    'adjust_url' => $this->adjustUrl($employee->id, null),
                ];
            }

            $hasActiveSchedule = $employee->workSchedules
                ->where('is_active', true)
                ->filter(function ($schedule) {
                    if (! $this->start_date && ! $this->end_date) {
                        return true;
                    }

                    $scheduleStart = $schedule->start_date;
                    $scheduleEnd = $schedule->end_date;

                    $periodStart = $this->start_date ? Carbon::parse($this->start_date) : null;
                    $periodEnd = $this->end_date ? Carbon::parse($this->end_date) : null;

                    if ($periodEnd && $scheduleStart && $scheduleStart->gt($periodEnd)) {
                        return false;
                    }

                    if ($periodStart && $scheduleEnd && $scheduleEnd->lt($periodStart)) {
                        return false;
                    }

                    return true;
                })
                ->isNotEmpty();

            if (! $hasActiveSchedule) {
                $this->totalWithoutSchedule++;

                $this->rows[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name ?? '-',
                    'company' => $employee->company?->name ?? 'Sem Empresa',
                    'date' => '-',
                    'type' => 'Sem jornada',
                    'severity' => 'danger',
                    'description' => 'Colaborador ativo sem jornada vinculada para o período informado.',
                    'entries_count' => null,
                    'adjust_url' => $this->adjustUrl($employee->id, null),
                ];
            }
        }
    }

    protected function loadPunchInconsistencies(): void
    {
        if (! $this->start_date || ! $this->end_date) {
            Notification::make()
                ->title('Período obrigatório')
                ->body('Informe data inicial e final para analisar marcações.')
                ->warning()
                ->send();

            return;
        }

        $groups = TimeEntry::query()
            ->with(['employee.company'])
            ->where('status', 'valid')
            ->whereBetween('entry_date', [$this->start_date, $this->end_date])
            ->when($this->company_id, fn ($query) => $query->where('company_id', $this->company_id))
            ->orderBy('employee_id')
            ->orderBy('entry_date')
            ->get()
            ->groupBy(fn (TimeEntry $entry) => $entry->employee_id . '|' . $entry->entry_date?->format('Y-m-d'));

        foreach ($groups as $group) {
            /** @var \Illuminate\Support\Collection $group */
            $first = $group->first();
            $employee = $first?->employee;

            if (! $employee) {
                continue;
            }

            $date = $first->entry_date?->format('Y-m-d');
            $count = $group->count();

            $type = null;
            $description = null;
            $severity = 'warning';

            if ($count === 1) {
                $type = 'Apenas uma batida';
                $description = 'Foi encontrada apenas 1 marcação no dia. Verifique entrada/saída faltante.';
                $severity = 'danger';
            } elseif ($count % 2 !== 0) {
                $type = 'Quantidade ímpar de batidas';
                $description = "Foram encontradas {$count} marcações. Normalmente deve existir par de entrada/saída.";
                $severity = 'danger';
            } elseif ($count > 4) {
                $type = 'Mais de 4 batidas';
                $description = "Foram encontradas {$count} marcações no dia. Verifique duplicidades ou ajustes manuais.";
                $severity = 'warning';
            }

            if (! $type) {
                continue;
            }

            $this->totalPunchProblems++;

            $this->rows[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name ?? '-',
                'company' => $employee->company?->name ?? 'Sem Empresa',
                'date' => $date ? Carbon::parse($date)->format('d/m/Y') : '-',
                'raw_date' => $date,
                'type' => $type,
                'severity' => $severity,
                'description' => $description,
                'entries_count' => $count,
                'adjust_url' => $this->adjustUrl($employee->id, $date),
            ];
        }
    }

    protected function adjustUrl(?int $employeeId, ?string $date): string
    {
        $params = [];

        if ($employeeId) {
            $params['employee_id'] = $employeeId;
        }

        if ($date) {
            $params['start_date'] = $date;
            $params['end_date'] = $date;
        } else {
            if ($this->start_date) {
                $params['start_date'] = $this->start_date;
            }

            if ($this->end_date) {
                $params['end_date'] = $this->end_date;
            }
        }

        return \App\Filament\Resources\TimeEntries\TimeEntryResource::getUrl('index', $params);
    }

    public function clearReport(): void
    {
        $this->rows = [];
        $this->totalInconsistencies = 0;
        $this->totalWithoutSolides = 0;
        $this->totalWithoutSchedule = 0;
        $this->totalPunchProblems = 0;
    }
}
