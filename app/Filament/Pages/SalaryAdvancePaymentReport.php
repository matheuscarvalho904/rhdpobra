<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Company;
use App\Models\SalaryAdvance;
use App\Models\Work;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class SalaryAdvancePaymentReport extends Page
{
    protected static ?string $navigationLabel = 'Pagto. Adiantamento';
    protected static ?string $title = 'Relatório de Pagamento de Adiantamento';
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|UnitEnum|null $navigationGroup = 'Folha';
    protected static ?int $navigationSort = 31; 

    protected string $view = 'filament.pages.salary-advance-payment-report';

    public ?int $company_id = null;
    public ?int $branch_id = null;
    public ?int $work_id = null;
    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?string $status = 'paid';
    public ?string $payment_method = 'pix';

    public array $companies = [];
    public array $branches = [];
    public array $works = [];

    public array $rows = [];
    public float $totalAmount = 0;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->toDateString();
        $this->date_to = now()->toDateString();

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
        $query = SalaryAdvance::query()
            ->with([
                'employee',
                'employee.jobRole',
                'company',
                'branch',
                'work',
            ])
            ->when($this->company_id, fn ($query) => $query->where('company_id', $this->company_id))
            ->when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id))
            ->when($this->work_id, fn ($query) => $query->where('work_id', $this->work_id))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->payment_method, fn ($query) => $query->where('payment_method', $this->payment_method))
            ->when($this->date_from, fn ($query) => $query->whereDate('advance_date', '>=', $this->date_from))
            ->when($this->date_to, fn ($query) => $query->whereDate('advance_date', '<=', $this->date_to))
            ->orderBy('company_id')
            ->orderBy('work_id')
            ->orderBy('employee_id');

        $advances = $query->get();

        $flatRows = $advances->map(function ($advance) {
            return [
                'employee_name' => $advance->employee->name ?? '-',
                'code' => $advance->employee->code ?? '-',
                'job_role' => $advance->employee->jobRole->name ?? '-',
                'company' => $advance->company->name ?? 'Sem Empresa',
                'branch' => $advance->branch->name ?? 'Sem Filial',
                'work' => $advance->work->name ?? 'Sem Obra',
                'advance_date' => optional($advance->advance_date)->format('d/m/Y') ?? '-',
                'pix_key_type' => $advance->pix_key_type ?: '-',
                'pix_key' => $advance->pix_key ?: '-',
                'pix_holder_document' => $advance->pix_holder_document ?: '-',
                'amount' => (float) $advance->amount,
                'status' => $advance->status_label,
            ];
        });

        $this->rows = $flatRows
            ->groupBy('company')
            ->map(function ($companyRows) {
                return $companyRows
                    ->groupBy('work')
                    ->map(function ($workRows) {
                        return [
                            'rows' => $workRows->values()->toArray(),
                            'total' => (float) $workRows->sum('amount'),
                        ];
                    })
                    ->toArray();
            })
            ->toArray();

        $this->totalAmount = (float) $flatRows->sum('amount');

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

        $pdf = Pdf::loadView('pdf.salary-advance-payment-report', [
            'rows' => $this->rows,
            'totalAmount' => $this->totalAmount,
            'dateFrom' => $this->date_from,
            'dateTo' => $this->date_to,
            'paymentMethod' => $this->payment_method,
            'status' => $this->status,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'relatorio-pagamento-adiantamento.pdf'
        );
    }
}