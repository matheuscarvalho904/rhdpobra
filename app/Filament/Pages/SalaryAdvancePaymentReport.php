<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Company;
use App\Models\SalaryAdvance;
use App\Models\Work;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SalaryAdvancePaymentReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Relatório de Pgto de Adiantamentos';
    protected static ?string $title = 'Pagamento de Adiantamentos';
    protected static string|\UnitEnum|null $navigationGroup = 'Folha';
    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.salary-advance-payment-report';

    public ?int $company_id = null;
    public ?int $branch_id = null;
    public ?int $work_id = null;
    public ?string $status = null;
    public ?string $payment_method = null;
    public ?string $date_from = null;
    public ?string $date_to = null;

    public array $rows = [];
    public float $totalAmount = 0;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->endOfMonth()->format('Y-m-d');

        $this->generateReport();
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Gerar Relatório')
                ->icon('heroicon-o-funnel')
                ->action(fn () => $this->generateReport()),

            Action::make('pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(fn () => $this->exportPdf()),
        ];
    }

    public function getCompaniesProperty(): array
    {
        return Company::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getBranchesProperty(): array
    {
        return Branch::query()
            ->when($this->company_id, fn (Builder $query) => $query->where('company_id', $this->company_id))
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getWorksProperty(): array
    {
        return Work::query()
            ->when($this->company_id, fn (Builder $query) => $query->where('company_id', $this->company_id))
            ->when($this->branch_id, fn (Builder $query) => $query->where('branch_id', $this->branch_id))
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function updatedCompanyId(): void
    {
        $this->branch_id = null;
        $this->work_id = null;
    }

    public function updatedBranchId(): void
    {
        $this->work_id = null;
    }

    public function generateReport(): void
    {
        $records = $this->getFilteredQuery()
            ->with([
                'employee',
                'company',
                'work',
            ])
            ->orderBy('company_id')
            ->orderBy('work_id')
            ->orderBy('advance_date')
            ->get();

        $grouped = [];
        $total = 0;

        foreach ($records as $record) {
            $companyName = $record->company?->name ?: 'Sem Empresa';
            $workName = $record->work?->name ?: 'Sem Obra';

            $amount = (float) $record->amount;
            $total += $amount;

            $grouped[$companyName][$workName]['rows'][] = [
                'employee_name' => $record->employee?->name ?: '-',
                'code' => $record->employee?->code ?: '-',
                'job_role' => $record->employee?->jobRole?->name ?: '-',
                'advance_date' => optional($record->advance_date)->format('d/m/Y') ?: '-',
                'pix_key_type' => $this->formatPixKeyType($record->pix_key_type),
                'pix_key' => $record->pix_key ?: '-',
                'pix_holder_document' => $record->pix_holder_document ?: '-',
                'payment_method' => $this->formatPaymentMethod($record->payment_method),
                'status' => $this->formatStatus($record->status),
                'amount' => $amount,
            ];

            $grouped[$companyName][$workName]['total'] =
                ($grouped[$companyName][$workName]['total'] ?? 0) + $amount;
        }

        $this->rows = $grouped;
        $this->totalAmount = round($total, 2);
    }

    public function exportPdf()
    {
        $this->generateReport();

        $pdf = Pdf::loadView('pdf.reports.salary-advance-payment-report', [
            'rows' => $this->rows,
            'totalAmount' => $this->totalAmount,
            'filters' => [
                'company' => $this->company_id ? ($this->companies[$this->company_id] ?? 'Todas') : 'Todas',
                'branch' => $this->branch_id ? ($this->branches[$this->branch_id] ?? 'Todas') : 'Todas',
                'work' => $this->work_id ? ($this->works[$this->work_id] ?? 'Todas') : 'Todas',
                'status' => $this->status ? $this->formatStatus($this->status) : 'Todos',
                'payment_method' => $this->payment_method ? $this->formatPaymentMethod($this->payment_method) : 'Todos',
                'date_from' => $this->date_from ? now()->parse($this->date_from)->format('d/m/Y') : '-',
                'date_to' => $this->date_to ? now()->parse($this->date_to)->format('d/m/Y') : '-',
            ],
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'relatorio-pagamento-adiantamentos.pdf'
        );
    }

    protected function getFilteredQuery(): Builder
    {
        return SalaryAdvance::query()
            ->when($this->company_id, fn (Builder $query) => $query->where('company_id', $this->company_id))
            ->when($this->branch_id, fn (Builder $query) => $query->where('branch_id', $this->branch_id))
            ->when($this->work_id, fn (Builder $query) => $query->where('work_id', $this->work_id))
            ->when($this->status, fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->payment_method, fn (Builder $query) => $query->where('payment_method', $this->payment_method))
            ->when($this->date_from, fn (Builder $query) => $query->whereDate('advance_date', '>=', $this->date_from))
            ->when($this->date_to, fn (Builder $query) => $query->whereDate('advance_date', '<=', $this->date_to));
    }

    protected function formatStatus(?string $status): string
    {
        return match ($status) {
            'draft' => 'Rascunho',
            'paid' => 'Pago',
            'canceled' => 'Cancelado',
            'integrated_payroll' => 'Integrado na Folha',
            default => $status ?: '-',
        };
    }

    protected function formatPaymentMethod(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'pix' => 'PIX',
            'bank_transfer' => 'Transferência',
            'cash' => 'Dinheiro',
            default => $paymentMethod ?: '-',
        };
    }

    protected function formatPixKeyType(?string $pixKeyType): string
    {
        return match ($pixKeyType) {
            'cpf' => 'CPF',
            'cnpj' => 'CNPJ',
            'email' => 'E-mail',
            'phone' => 'Telefone',
            'random' => 'Aleatória',
            default => $pixKeyType ?: '-',
        };
    }
}