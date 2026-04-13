<?php

namespace App\Filament\Pages;

use App\Models\PayrollRun;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

class PayrollRunPayslips extends Page
{
    protected static ?string $navigationLabel = 'Holerites';

    protected static ?string $title = 'Holerites da Folha';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Folha';

    protected static ?int $navigationSort = 20;

    // ✅ CORRETO NO FILAMENT 5
    protected string $view = 'filament.pages.payroll-run-payslips';

    public PayrollRun $payrollRun;

    public Collection $employees;

    public function mount(PayrollRun $payrollRun): void
    {
        $this->payrollRun = $payrollRun->load([
            'company',
            'branch',
            'work',
            'payrollCompetency',
        ]);

        $this->employees = $payrollRun->items()
            ->with([
                'employee.jobRole',
                'employee.contractType',
            ])
            ->get()
            ->pluck('employee')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}