<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\Work;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class EmployeesGeneralReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|UnitEnum|null $navigationGroup = 'RH e DP';
    protected static ?string $navigationLabel = 'Relatório Geral';
    protected static ?string $title = 'Relatório Geral de Colaboradores';
    protected static ?int $navigationSort = 29;

    protected string $view = 'filament.pages.employees-general-report';

    public ?array $data = [];

    public Collection $employees;

    public function mount(): void
    {
        $this->form->fill();
        $this->employees = collect();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('work_id')
                ->label('Obra')
                ->options(fn () => Work::query()
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray())
                ->searchable()
                ->preload(),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                    'terminated' => 'Demitido',
                ])
                ->searchable(),

            Forms\Components\DatePicker::make('hire_date_from')
                ->label('Admissão de')
                ->native(false)
                ->displayFormat('d/m/Y'),

            Forms\Components\DatePicker::make('hire_date_until')
                ->label('Admissão até')
                ->native(false)
                ->displayFormat('d/m/Y'),
        ];
    }

    public function generate(): void
    {
        $filters = $this->form->getState();

        $this->employees = $this->queryEmployees($filters)->get();
    }

    public function exportPdf()
    {
        $filters = $this->form->getState();

        $employees = $this->queryEmployees($filters)->get();

        $pdf = Pdf::loadView('pdf.employees.general', [
            'employees' => $employees,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'relatorio-geral-colaboradores.pdf'
        );
    }

    private function queryEmployees(array $filters): Builder
    {
        return Employee::query()
            ->with([
                'work',
                'department',
                'jobRole',
            ])
            ->when(
                filled($filters['work_id'] ?? null),
                fn (Builder $query) => $query->where('work_id', $filters['work_id'])
            )
            ->when(
                filled($filters['status'] ?? null),
                fn (Builder $query) => $query->where('status', $filters['status'])
            )
            ->when(
                filled($filters['hire_date_from'] ?? null),
                fn (Builder $query) => $query->whereDate('hire_date', '>=', $filters['hire_date_from'])
            )
            ->when(
                filled($filters['hire_date_until'] ?? null),
                fn (Builder $query) => $query->whereDate('hire_date', '<=', $filters['hire_date_until'])
            )
            ->orderBy('name');
    }
}