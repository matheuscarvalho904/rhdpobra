<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollRun;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class PayrollRunProcessingService
{
    public function __construct(
        protected PayrollCalculationService $payrollCalculationService,
    ) {}

    public function process(PayrollRun $payrollRun): void
    {
        DB::transaction(function () use ($payrollRun) {
            $this->clearPreviousProcessing($payrollRun);

            $employees = $this->getEmployees($payrollRun);

            $totals = [
                'gross' => 0.0,
                'discounts' => 0.0,
                'net' => 0.0,
                'fgts' => 0.0,
                'processed_employees' => 0,
            ];

            foreach ($employees as $employee) {
                $result = $this->processEmployeeByRunType($payrollRun, $employee);

                if (! $this->isValidResult($result)) {
                    continue;
                }

                $normalized = $this->normalizeCalculationResult($result);

                $this->storePayrollItems($payrollRun, $employee, $normalized);

                $totals['gross'] += $normalized['gross_amount'];
                $totals['discounts'] += $normalized['total_discounts'];
                $totals['net'] += $normalized['net_amount'];
                $totals['fgts'] += $normalized['fgts_amount'];
                $totals['processed_employees']++;
            }

            $payrollRun->update([
                'total_gross' => $this->money($totals['gross']),
                'total_discounts' => $this->money($totals['discounts']),
                'total_net' => $this->money($totals['net']),
                'total_fgts' => $this->money($totals['fgts']),
                'processed_employees' => (int) $totals['processed_employees'],
                'status' => 'processed',
                'processed_at' => now(),
                'error_message' => null,
            ]);
        });
    }

    public function reprocess(PayrollRun $payrollRun): void
    {
        $payrollRun->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        try {
            $this->process($payrollRun);
        } catch (Throwable $e) {
            $payrollRun->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function processEmployeeByRunType(PayrollRun $payrollRun, Employee $employee): array
    {
        return match ($payrollRun->run_type) {
            'payroll_clt',
            'payroll_apprentice',
            'payroll_rpa',
            'internship_payment'
                => $this->processPayrollEmployee($employee, $payrollRun),

            'accounts_payable' => $this->processAccountsPayable($employee, $payrollRun),

            default => [],
        };
    }

    protected function processPayrollEmployee(Employee $employee, PayrollRun $payrollRun): array
    {
        $competency = $payrollRun->payrollCompetency;

        return $this->payrollCalculationService->calculate(
            $employee,
            [
                'payroll_competency_id' => $payrollRun->payroll_competency_id,
                'run_type' => $payrollRun->run_type,

                'period_start' => $competency?->period_start?->format('Y-m-d'),
                'period_end' => $competency?->period_end?->format('Y-m-d'),
                'payment_date' => $competency?->payment_date?->format('Y-m-d'),

                'competency_month' => $competency?->month,
                'competency_year' => $competency?->year,
                'dependents' => (int) ($employee->dependents_count ?? 0),

                'salary_divisor_mode' => 'fixed_30',
                'prorate_salary_on_admission' => true,
            ]
        );
    }

    protected function processAccountsPayable(Employee $employee, PayrollRun $payrollRun): array
    {
        return [];
    }

    protected function clearPreviousProcessing(PayrollRun $payrollRun): void
    {
        $payrollRun->items()->delete();

        $payrollRun->update([
            'total_gross' => 0,
            'total_discounts' => 0,
            'total_net' => 0,
            'total_fgts' => 0,
            'processed_employees' => 0,
            'status' => 'processing',
            'processed_at' => null,
            'error_message' => null,
        ]);
    }

    protected function storePayrollItems(PayrollRun $payrollRun, Employee $employee, array $result): void
    {
        foreach (($result['items'] ?? []) as $item) {
            $amount = $this->money(abs((float) ($item['amount'] ?? 0)));

            if ($amount <= 0) {
                continue;
            }

            $type = $this->normalizeItemType($item['type'] ?? null);

            if ($type === null) {
                continue;
            }

            $payrollRun->items()->create([
                'employee_id' => $employee->id,
                'payroll_event_id' => $item['payroll_event_id'] ?? null,
                'code' => $this->truncate($item['code'] ?? null, 30),
                'description' => $this->truncate($item['description'] ?? null, 255),
                'type' => $type,
                'reference' => $this->normalizeReference($item['reference'] ?? null),
                'amount' => $amount,
                'source' => $this->truncate($item['source'] ?? null, 30),
            ]);
        }

        $this->storeSummaryItems($payrollRun, $employee, $result);
    }

    protected function storeSummaryItems(PayrollRun $payrollRun, Employee $employee, array $result): void
    {
        $grossAmount = $this->money((float) ($result['gross_amount'] ?? 0));
        $totalDiscounts = $this->money((float) ($result['total_discounts'] ?? 0));
        $netAmount = $this->money((float) ($result['net_amount'] ?? 0));
        $fgtsAmount = $this->money((float) ($result['fgts_amount'] ?? 0));

        $summaryItems = [
            [
                'code' => 'BRUTO',
                'description' => 'Total Bruto do Colaborador',
                'type' => 'resumo',
                'reference' => 0,
                'amount' => $grossAmount,
                'source' => 'summary',
            ],
            [
                'code' => 'DESCONTOS',
                'description' => 'Total de Descontos do Colaborador',
                'type' => 'resumo',
                'reference' => 0,
                'amount' => $totalDiscounts,
                'source' => 'summary',
            ],
            [
                'code' => 'LIQUIDO',
                'description' => 'Valor Líquido do Colaborador',
                'type' => 'resumo',
                'reference' => 0,
                'amount' => $netAmount,
                'source' => 'summary',
            ],
        ];

        if ($fgtsAmount > 0) {
            $summaryItems[] = [
                'code' => 'FGTS',
                'description' => 'FGTS do Colaborador',
                'type' => 'resumo',
                'reference' => 0,
                'amount' => $fgtsAmount,
                'source' => 'summary',
            ];
        }

        foreach ($summaryItems as $item) {
            if ((float) $item['amount'] < 0) {
                continue;
            }

            $payrollRun->items()->create([
                'employee_id' => $employee->id,
                'payroll_event_id' => null,
                'code' => $item['code'],
                'description' => $item['description'],
                'type' => $item['type'],
                'reference' => $this->normalizeReference($item['reference']),
                'amount' => $this->money((float) $item['amount']),
                'source' => $item['source'],
            ]);
        }
    }

    protected function getEmployees(PayrollRun $payrollRun): Collection
    {
        return Employee::query()
            ->with([
                'company',
                'branch',
                'work',
                'contractType',
                'jobRole',
            ])
            ->where('is_active', true)
            ->when($payrollRun->company_id, fn ($q) => $q->where('company_id', $payrollRun->company_id))
            ->when($payrollRun->branch_id, fn ($q) => $q->where('branch_id', $payrollRun->branch_id))
            ->when($payrollRun->work_id, fn ($q) => $q->where('work_id', $payrollRun->work_id))
            ->where(function ($query) use ($payrollRun) {
                match ($payrollRun->run_type) {
                    'payroll_clt' => $query
                        ->where('processing_type', 'payroll_clt')
                        ->where('generates_payroll', true)
                        ->where(function ($subQuery) {
                            $subQuery
                                ->whereDoesntHave('contractType', fn ($q) => $q->where('code', 'APRENDIZ'))
                                ->orWhereNull('contract_type_id');
                        }),

                    'payroll_apprentice' => $query
                        ->where('processing_type', 'payroll_clt')
                        ->where('generates_payroll', true)
                        ->whereHas('contractType', fn ($q) => $q->where('code', 'APRENDIZ')),

                    'payroll_rpa' => $query
                        ->where('processing_type', 'payroll_rpa')
                        ->where('generates_payroll', true),

                    'internship_payment' => $query
                        ->where('processing_type', 'internship_payment')
                        ->where('generates_payroll', true),

                    'accounts_payable' => $query
                        ->where('processing_type', 'accounts_payable')
                        ->where('generates_accounts_payable', true),

                    default => $query->whereRaw('1 = 0'),
                };
            })
            ->orderBy('name')
            ->get();
    }

    protected function isValidResult(array $result): bool
    {
        return ! empty($result)
            && (
                array_key_exists('gross_amount', $result)
                || array_key_exists('net_amount', $result)
                || ! empty($result['items'] ?? [])
            );
    }

    protected function normalizeCalculationResult(array $result): array
    {
        $grossAmount = $this->money(abs((float) ($result['gross_amount'] ?? 0)));
        $fgtsAmount = $this->money(abs((float) ($result['fgts_amount'] ?? 0)));
        $salaryAdvanceDiscount = $this->money(abs((float) ($result['salary_advance_discount'] ?? 0)));
        $eventDiscounts = $this->money(abs((float) ($result['event_discounts_total'] ?? 0)));
        $inssAmount = $this->money(abs((float) ($result['inss_amount'] ?? 0)));
        $irrfAmount = $this->money(abs((float) ($result['irrf_amount'] ?? 0)));

        $totalDiscounts = $this->money(
            abs((float) ($result['total_discounts'] ?? (
                $eventDiscounts
                + $salaryAdvanceDiscount
                + $inssAmount
                + $irrfAmount
            )))
        );

        $netAmount = $this->money((float) ($result['net_amount'] ?? ($grossAmount - $totalDiscounts)));

        if ($netAmount < 0) {
            $netAmount = 0.0;
        }

        $result['gross_amount'] = $grossAmount;
        $result['fgts_amount'] = $fgtsAmount;
        $result['salary_advance_discount'] = $salaryAdvanceDiscount;
        $result['event_discounts_total'] = $eventDiscounts;
        $result['inss_amount'] = $inssAmount;
        $result['irrf_amount'] = $irrfAmount;
        $result['total_discounts'] = $totalDiscounts;
        $result['net_amount'] = $netAmount;
        $result['items'] = is_array($result['items'] ?? null) ? array_values($result['items']) : [];

        return $result;
    }

    protected function normalizeItemType(mixed $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $type = mb_strtolower(trim((string) $type));

        return match ($type) {
            'provento', 'earning', 'vencimento', 'credito', 'crédito' => 'provento',
            'desconto', 'deduction', 'debito', 'débito' => 'desconto',
            'informativo', 'info' => 'informativo',
            'resumo' => 'resumo',
            default => null,
        };
    }

    protected function normalizeReference(mixed $reference): float
    {
        if ($reference === null || $reference === '') {
            return 0;
        }

        if (is_numeric($reference)) {
            return round((float) $reference, 2);
        }

        $value = trim((string) $reference);

        if (str_contains($value, '/')) {
            return 0;
        }

        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? round((float) $value, 2) : 0;
    }

    protected function truncate(mixed $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? mb_substr($value, 0, $limit) : null;
    }

    protected function money(float $value): float
    {
        return round($value, 2);
    }
}