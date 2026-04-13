<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PayrollCompetency;
use App\Models\PayrollRunItem;
use App\Models\Work;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class PayrollPaymentReport extends Page
{
    protected static ?string $navigationLabel = 'Relatório de Pagamento';
    protected static ?string $title = 'Relatório de Pagamento da Folha';
    protected static ?int $navigationSort = 30;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';

    protected string $view = 'filament.pages.payroll-payment-report';

    public ?int $payroll_competency_id = null;
    public ?int $company_id = null;
    public ?int $branch_id = null;
    public ?int $work_id = null;

    public array $competencies = [];
    public array $companies = [];
    public array $branches = [];
    public array $works = [];

    public array $rows = [];
    public float $totalNet = 0;
    public float $totalGross = 0;
    public float $totalDiscounts = 0;
    public float $totalFgts = 0;

    public function mount(): void
    {
        $this->competencies = PayrollCompetency::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->mapWithKeys(fn ($item) => [
                $item->id => sprintf(
                    '%02d/%04d - %s',
                    $item->month,
                    $item->year,
                    match ($item->type) {
                        'monthly' => 'Mensal',
                        'vacation' => 'Férias',
                        'thirteenth' => '13º',
                        'termination' => 'Rescisão',
                        'advance' => 'Adiantamento',
                        default => $item->type,
                    }
                ),
            ])
            ->toArray();

        $this->companies = Company::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->branches = Branch::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->works = Work::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function generateReport(): void
    {
        $baseItems = PayrollRunItem::query()
            ->with([
                'employee',
                'employee.jobRole',
                'payrollRun.company',
                'payrollRun.branch',
                'payrollRun.work',
                'payrollRun.payrollCompetency',
            ])
            ->where('code', 'BRUTO')
            ->where('type', 'resumo')
            ->when($this->payroll_competency_id, function ($query) {
                $query->whereHas('payrollRun', function ($subQuery) {
                    $subQuery->where('payroll_competency_id', $this->payroll_competency_id);
                });
            })
            ->when($this->company_id, function ($query) {
                $query->whereHas('payrollRun', function ($subQuery) {
                    $subQuery->where('company_id', $this->company_id);
                });
            })
            ->when($this->branch_id, function ($query) {
                $query->whereHas('payrollRun', function ($subQuery) {
                    $subQuery->where('branch_id', $this->branch_id);
                });
            })
            ->when($this->work_id, function ($query) {
                $query->whereHas('payrollRun', function ($subQuery) {
                    $subQuery->where('work_id', $this->work_id);
                });
            })
            ->orderBy('employee_id')
            ->orderBy('id')
            ->get();

        $flatRows = $baseItems->map(function ($baseItem) {
            $employee = $baseItem->employee;
            $run = $baseItem->payrollRun;

            if (! $employee || ! $run) {
                return null;
            }

            $summaryItems = PayrollRunItem::query()
                ->where('payroll_run_id', $run->id)
                ->where('employee_id', $employee->id)
                ->where('type', 'resumo')
                ->whereIn('code', ['BRUTO', 'DESCONTOS', 'FGTS'])
                ->get()
                ->keyBy('code');

            $gross = round((float) ($summaryItems->get('BRUTO')->amount ?? 0), 2);
            $discounts = round((float) ($summaryItems->get('DESCONTOS')->amount ?? 0), 2);
            $fgts = round((float) ($summaryItems->get('FGTS')->amount ?? 0), 2);
            $net = round($gross - $discounts, 2);

            if ($net < 0) {
                $net = 0;
            }

            return [
                'employee_name' => $employee->name ?? '-',
                'cpf' => $employee->cpf ?? '-',
                'registration_number' => $employee->registration_number ?? $employee->code ?? '-',
                'job_role' => $employee->jobRole->name ?? '-',
                'company' => $run->company->name ?? 'Sem Empresa',
                'branch' => $run->branch->name ?? 'Sem Filial',
                'work' => $run->work->name ?? 'Sem Obra',
                'pix_key' => $employee->pix_key ?? '-',
                'gross_total' => $gross,
                'discounts_total' => $discounts,
                'net_total' => $net,
                'fgts_total' => $fgts,
            ];
        })->filter()->values();

        $this->rows = $flatRows
            ->groupBy('company')
            ->map(function ($companyRows) {
                return $companyRows
                    ->groupBy('work')
                    ->map(function ($workRows) {
                        return [
                            'rows' => $workRows->values()->toArray(),
                            'total_gross' => round((float) $workRows->sum('gross_total'), 2),
                            'total_discounts' => round((float) $workRows->sum('discounts_total'), 2),
                            'total_net' => round((float) $workRows->sum('net_total'), 2),
                            'total_fgts' => round((float) $workRows->sum('fgts_total'), 2),
                        ];
                    })
                    ->toArray();
            })
            ->toArray();

        $this->totalGross = round((float) $flatRows->sum('gross_total'), 2);
        $this->totalDiscounts = round((float) $flatRows->sum('discounts_total'), 2);
        $this->totalNet = round((float) $flatRows->sum('net_total'), 2);
        $this->totalFgts = round((float) $flatRows->sum('fgts_total'), 2);

        if ($flatRows->isEmpty()) {
            Notification::make()
                ->title('Nenhum registro encontrado para os filtros informados.')
                ->warning()
                ->send();
        }
    }

    public function exportPdf()
    {
        $this->generateReport();

        if (empty($this->rows)) {
            return null;
        }

        $competencyLabel = $this->payroll_competency_id && isset($this->competencies[$this->payroll_competency_id])
            ? $this->competencies[$this->payroll_competency_id]
            : 'Todas';

        $pdf = Pdf::loadView('pdf.payroll-payment-report', [
            'rows' => $this->rows,
            'totalGross' => $this->totalGross,
            'totalDiscounts' => $this->totalDiscounts,
            'totalNet' => $this->totalNet,
            'totalFgts' => $this->totalFgts,
            'competencyLabel' => $competencyLabel,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'relatorio-pagamento-folha.pdf'
        );
    }
}