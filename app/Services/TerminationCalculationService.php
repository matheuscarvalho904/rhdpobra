<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeTermination;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class TerminationCalculationService
{
    public function calculate(Employee $employee, EmployeeTermination $termination, array $context = []): array
    {
        $contract = $employee->currentContract ?? $termination->contract;

        if (! $contract) {
            throw new InvalidArgumentException('Contrato atual não encontrado para cálculo da rescisão.');
        }

        $salary = $this->money((float) (
            $context['salary']
                ?? $contract->salary
                ?? $employee->salary
                ?? 0
        ));

        if ($salary <= 0) {
            throw new InvalidArgumentException('Salário inválido para cálculo da rescisão.');
        }

        $admissionDate = $this->parseDate(
            $context['admission_date']
                ?? $contract->admission_date
                ?? $employee->admission_date
        );

        $terminationDate = $this->parseDate(
            $termination->termination_date
                ?? $context['termination_date']
        );

        if (! $terminationDate) {
            throw new InvalidArgumentException('Data de desligamento não informada.');
        }

        $noticeType = $termination->notice_type ?? 'worked';
        $noticeDays = (int) ($termination->notice_days ?? 0);
        $projectedEndDate = $this->parseDate(
            $termination->projected_end_date
                ?? $context['projected_end_date']
        );

        $fgtsBalance = $this->money((float) ($context['fgts_balance'] ?? 0));
        $unusedVacationPeriods = (int) ($context['unused_vacation_periods'] ?? 0);

        $salaryBalance = $this->calculateSalaryBalance($salary, $terminationDate);
        $noticePay = $this->calculateNoticePay(
            salary: $salary,
            noticeType: $noticeType,
            noticeDays: $noticeDays
        );

        $thirteenthData = $this->calculateThirteenthProportional(
            salary: $salary,
            terminationDate: $terminationDate,
            projectedEndDate: $projectedEndDate,
            useProjectedDate: $noticeType === 'indemnified'
        );

        $vacationOverdueData = $this->calculateOverdueVacations(
            salary: $salary,
            overduePeriods: $unusedVacationPeriods
        );

        $vacationProportionalData = $this->calculateProportionalVacations(
            salary: $salary,
            admissionDate: $admissionDate,
            terminationDate: $terminationDate,
            projectedEndDate: $projectedEndDate,
            useProjectedDate: $noticeType === 'indemnified'
        );

        $grossRescission = $this->money(
            $salaryBalance['amount']
            + $noticePay['amount']
            + $thirteenthData['amount']
            + $vacationOverdueData['vacation_amount']
            + $vacationOverdueData['one_third_amount']
            + $vacationProportionalData['vacation_amount']
            + $vacationProportionalData['one_third_amount']
        );

        $inssBase = $this->money(
            $salaryBalance['amount']
            + $noticePay['amount']
            + $thirteenthData['amount']
        );

        $inssAmount = $employee->has_inss
            ? $this->calculateInss($inssBase, $context)
            : 0.0;

        $irrfData = $employee->has_irrf
            ? $this->calculateIrrf(
                grossTaxableValue: $inssBase,
                inssAmount: $inssAmount,
                context: $context
            )
            : $this->emptyIrrfData();

        $fgtsMonthBase = $this->money(
            $salaryBalance['amount']
            + $noticePay['fgts_base']
            + $thirteenthData['fgts_base']
        );

        $fgtsRate = (float) ($employee->fgts_rate ?? 8.00);
        $fgtsMonthAmount = $employee->has_fgts
            ? $this->calculateFgts($fgtsMonthBase, $fgtsRate)
            : 0.0;

        $fgtsFineBase = $this->money($fgtsBalance + $fgtsMonthAmount);
        $fgtsFineAmount = $employee->has_fgts
            ? $this->money($fgtsFineBase * 0.40)
            : 0.0;

        $totalDiscounts = $this->money(
            $inssAmount
            + $irrfData['irrf_amount']
        );

        $netRescission = $this->money($grossRescission - $totalDiscounts);

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'employee_contract_id' => $contract->id ?? null,
            'termination_id' => $termination->id,
            'document_type' => 'rescisao',

            'salary' => $salary,
            'termination_date' => optional($terminationDate)->format('Y-m-d'),
            'notice_type' => $noticeType,
            'notice_days' => $noticeDays,
            'projected_end_date' => optional($projectedEndDate)->format('Y-m-d'),

            'salary_balance' => $salaryBalance['amount'],
            'salary_balance_days' => $salaryBalance['days'],

            'notice_amount' => $noticePay['amount'],
            'notice_reference_days' => $noticePay['reference_days'],

            'thirteenth_amount' => $thirteenthData['amount'],
            'thirteenth_avos' => $thirteenthData['avos'],

            'vacation_overdue_amount' => $vacationOverdueData['vacation_amount'],
            'vacation_overdue_one_third' => $vacationOverdueData['one_third_amount'],
            'vacation_overdue_periods' => $vacationOverdueData['periods'],

            'vacation_proportional_amount' => $vacationProportionalData['vacation_amount'],
            'vacation_proportional_one_third' => $vacationProportionalData['one_third_amount'],
            'vacation_proportional_avos' => $vacationProportionalData['avos'],

            'gross_amount' => $grossRescission,
            'inss_amount' => $inssAmount,
            'irrf_amount' => $irrfData['irrf_amount'],
            'total_discounts' => $totalDiscounts,
            'net_amount' => $netRescission,

            'base_inss' => $inssBase,
            'base_irrf' => $irrfData['irrf_base'],

            'fgts_rate' => $fgtsRate,
            'fgts_month_base' => $fgtsMonthBase,
            'fgts_month_amount' => $fgtsMonthAmount,
            'fgts_balance' => $fgtsBalance,
            'fgts_fine_base' => $fgtsFineBase,
            'fgts_fine_amount' => $fgtsFineAmount,

            'items' => $this->buildTerminationItems(
                salaryBalance: $salaryBalance,
                noticePay: $noticePay,
                thirteenthData: $thirteenthData,
                vacationOverdueData: $vacationOverdueData,
                vacationProportionalData: $vacationProportionalData,
                inssAmount: $inssAmount,
                irrfAmount: $irrfData['irrf_amount'],
                fgtsMonthAmount: $fgtsMonthAmount,
                fgtsFineAmount: $fgtsFineAmount
            ),
        ];
    }

    protected function calculateSalaryBalance(float $salary, CarbonInterface $terminationDate): array
    {
        $daysWorked = min((int) $terminationDate->day, 30);
        $amount = $this->money(($salary / 30) * $daysWorked);

        return [
            'days' => $daysWorked,
            'amount' => $amount,
        ];
    }

    protected function calculateNoticePay(float $salary, string $noticeType, int $noticeDays): array
    {
        if ($noticeDays <= 0) {
            return [
                'reference_days' => 0,
                'amount' => 0.0,
                'fgts_base' => 0.0,
            ];
        }

        if ($noticeType === 'worked') {
            return [
                'reference_days' => $noticeDays,
                'amount' => 0.0,
                'fgts_base' => 0.0,
            ];
        }

        $amount = $this->money(($salary / 30) * $noticeDays);

        return [
            'reference_days' => $noticeDays,
            'amount' => $amount,
            'fgts_base' => $amount,
        ];
    }

    protected function calculateThirteenthProportional(
        float $salary,
        CarbonInterface $terminationDate,
        ?CarbonInterface $projectedEndDate = null,
        bool $useProjectedDate = false
    ): array {
        $referenceDate = $useProjectedDate && $projectedEndDate
            ? $projectedEndDate
            : $terminationDate;

        $avos = max(0, min(12, (int) $referenceDate->month));
        $amount = $this->money(($salary / 12) * $avos);

        return [
            'avos' => $avos,
            'amount' => $amount,
            'fgts_base' => $amount,
        ];
    }

    protected function calculateOverdueVacations(float $salary, int $overduePeriods): array
    {
        if ($overduePeriods <= 0) {
            return [
                'periods' => 0,
                'vacation_amount' => 0.0,
                'one_third_amount' => 0.0,
            ];
        }

        $vacationAmount = $this->money($salary * $overduePeriods);
        $oneThird = $this->money($vacationAmount / 3);

        return [
            'periods' => $overduePeriods,
            'vacation_amount' => $vacationAmount,
            'one_third_amount' => $oneThird,
        ];
    }

    protected function calculateProportionalVacations(
        float $salary,
        ?CarbonInterface $admissionDate,
        CarbonInterface $terminationDate,
        ?CarbonInterface $projectedEndDate = null,
        bool $useProjectedDate = false
    ): array {
        if (! $admissionDate) {
            return [
                'avos' => 0,
                'vacation_amount' => 0.0,
                'one_third_amount' => 0.0,
            ];
        }

        $referenceDate = $useProjectedDate && $projectedEndDate
            ? $projectedEndDate
            : $terminationDate;

        $months = (($referenceDate->year - $admissionDate->year) * 12)
            + ($referenceDate->month - $admissionDate->month);

        $avos = max(0, min(12, $months % 12));

        if ((int) $referenceDate->day >= 15) {
            $avos = min(12, $avos + 1);
        }

        $vacationAmount = $this->money(($salary / 12) * $avos);
        $oneThird = $this->money($vacationAmount / 3);

        return [
            'avos' => $avos,
            'vacation_amount' => $vacationAmount,
            'one_third_amount' => $oneThird,
        ];
    }

    protected function buildTerminationItems(
        array $salaryBalance,
        array $noticePay,
        array $thirteenthData,
        array $vacationOverdueData,
        array $vacationProportionalData,
        float $inssAmount,
        float $irrfAmount,
        float $fgtsMonthAmount,
        float $fgtsFineAmount
    ): array {
        $items = [];

        if ($salaryBalance['amount'] > 0) {
            $items[] = [
                'code' => 'SALDO_SAL',
                'description' => 'Saldo de Salário',
                'type' => 'provento',
                'reference' => (float) ($salaryBalance['days'] ?? 0),
                'amount' => $this->money($salaryBalance['amount']),
                'source' => 'termination',
            ];
        }

        if ($noticePay['amount'] > 0) {
            $items[] = [
                'code' => 'AVISO',
                'description' => 'Aviso Prévio Indenizado',
                'type' => 'provento',
                'reference' => (float) ($noticePay['reference_days'] ?? 0),
                'amount' => $this->money($noticePay['amount']),
                'source' => 'termination',
            ];
        }

        if ($thirteenthData['amount'] > 0) {
            $items[] = [
                'code' => '13PROP',
                'description' => '13º Proporcional',
                'type' => 'provento',
                'reference' => (float) ($thirteenthData['avos'] ?? 0),
                'amount' => $this->money($thirteenthData['amount']),
                'source' => 'termination',
            ];
        }

        if ($vacationOverdueData['vacation_amount'] > 0) {
            $items[] = [
                'code' => 'FERIAS_VENC',
                'description' => 'Férias Vencidas',
                'type' => 'provento',
                'reference' => (float) ($vacationOverdueData['periods'] ?? 0),
                'amount' => $this->money($vacationOverdueData['vacation_amount']),
                'source' => 'termination',
            ];
        }

        if ($vacationOverdueData['one_third_amount'] > 0) {
            $items[] = [
                'code' => 'FERIAS_VENC_13',
                'description' => '1/3 sobre Férias Vencidas',
                'type' => 'provento',
                'reference' => 0,
                'amount' => $this->money($vacationOverdueData['one_third_amount']),
                'source' => 'termination',
            ];
        }

        if ($vacationProportionalData['vacation_amount'] > 0) {
            $items[] = [
                'code' => 'FERIAS_PROP',
                'description' => 'Férias Proporcionais',
                'type' => 'provento',
                'reference' => (float) ($vacationProportionalData['avos'] ?? 0),
                'amount' => $this->money($vacationProportionalData['vacation_amount']),
                'source' => 'termination',
            ];
        }

        if ($vacationProportionalData['one_third_amount'] > 0) {
            $items[] = [
                'code' => 'FERIAS_PROP_13',
                'description' => '1/3 sobre Férias Proporcionais',
                'type' => 'provento',
                'reference' => 0,
                'amount' => $this->money($vacationProportionalData['one_third_amount']),
                'source' => 'termination',
            ];
        }

        if ($inssAmount > 0) {
            $items[] = [
                'code' => 'INSS',
                'description' => 'Desconto INSS',
                'type' => 'desconto',
                'reference' => 0,
                'amount' => $this->money($inssAmount),
                'source' => 'termination',
            ];
        }

        if ($irrfAmount > 0) {
            $items[] = [
                'code' => 'IRRF',
                'description' => 'Desconto IRRF',
                'type' => 'desconto',
                'reference' => 0,
                'amount' => $this->money($irrfAmount),
                'source' => 'termination',
            ];
        }

        if ($fgtsMonthAmount > 0) {
            $items[] = [
                'code' => 'FGTS',
                'description' => 'FGTS da Rescisão',
                'type' => 'informativo',
                'reference' => 0,
                'amount' => $this->money($fgtsMonthAmount),
                'source' => 'termination',
            ];
        }

        if ($fgtsFineAmount > 0) {
            $items[] = [
                'code' => 'MULTA_FGTS',
                'description' => 'Multa de 40% do FGTS',
                'type' => 'provento',
                'reference' => 40,
                'amount' => $this->money($fgtsFineAmount),
                'source' => 'termination',
            ];
        }

        return array_values($items);
    }

    protected function calculateInss(float $baseValue, array $context = []): float
    {
        if (isset($context['inss_amount'])) {
            return $this->money((float) $context['inss_amount']);
        }

        if ($baseValue <= 0) {
            return 0.0;
        }

        $brackets = $context['inss_brackets'] ?? [
            ['limit' => 1621.00, 'rate' => 0.075],
            ['limit' => 2902.84, 'rate' => 0.09],
            ['limit' => 4354.27, 'rate' => 0.12],
            ['limit' => 8475.55, 'rate' => 0.14],
        ];

        $contribution = 0.0;
        $previousLimit = 0.0;
        $salaryBase = min($baseValue, (float) end($brackets)['limit']);

        foreach ($brackets as $bracket) {
            $limit = (float) $bracket['limit'];
            $rate = (float) $bracket['rate'];

            if ($salaryBase <= $previousLimit) {
                break;
            }

            $taxableSlice = min($salaryBase, $limit) - $previousLimit;

            if ($taxableSlice > 0) {
                $contribution += $taxableSlice * $rate;
            }

            $previousLimit = $limit;
        }

        return $this->money($contribution);
    }

    protected function calculateIrrf(float $grossTaxableValue, float $inssAmount, array $context = []): array
    {
        if (isset($context['irrf_amount'])) {
            $manualAmount = $this->money((float) $context['irrf_amount']);

            return [
                'irrf_amount' => $manualAmount,
                'irrf_base' => $this->money((float) ($context['irrf_base'] ?? 0)),
                'gross_taxable' => $this->money($grossTaxableValue),
                'legal_deductions' => $this->money($inssAmount),
                'simplified_deduction' => 0.0,
                'deduction_method' => 'manual',
                'reduction_amount' => 0.0,
            ];
        }

        if ($grossTaxableValue <= 0) {
            return $this->emptyIrrfData();
        }

        $dependentDeduction = (float) ($context['irrf_dependent_deduction'] ?? 189.59);
        $simplifiedDeduction = (float) ($context['irrf_simplified_deduction'] ?? 607.20);
        $dependents = (int) ($context['dependents'] ?? $context['irrf_dependents'] ?? 0);
        $alimonyDeduction = (float) ($context['alimony_deduction'] ?? 0);
        $otherLegalDeductions = (float) ($context['other_legal_deductions'] ?? 0);

        $legalDeductions = $this->money(
            $inssAmount
            + ($dependents * $dependentDeduction)
            + $alimonyDeduction
            + $otherLegalDeductions
        );

        $forceMethod = $context['irrf_deduction_method'] ?? null;

        $useSimplified = match ($forceMethod) {
            'simplified' => true,
            'legal' => false,
            default => $simplifiedDeduction > $legalDeductions,
        };

        $chosenDeduction = $useSimplified ? $simplifiedDeduction : $legalDeductions;
        $irrfBase = max(0, $this->money($grossTaxableValue - $chosenDeduction));

        $grossTax = $this->calculateProgressiveIrrfFromBase($irrfBase, $context);
        $reductionAmount = $this->calculateMonthlyIrrfReduction($grossTaxableValue, $grossTax, $context);
        $netIrrf = max(0, $this->money($grossTax - $reductionAmount));

        return [
            'irrf_amount' => $netIrrf,
            'irrf_base' => $irrfBase,
            'gross_taxable' => $this->money($grossTaxableValue),
            'legal_deductions' => $legalDeductions,
            'simplified_deduction' => $this->money($simplifiedDeduction),
            'deduction_method' => $useSimplified ? 'simplified' : 'legal',
            'reduction_amount' => $this->money($reductionAmount),
        ];
    }

    protected function calculateProgressiveIrrfFromBase(float $baseValue, array $context = []): float
    {
        if ($baseValue <= 0) {
            return 0.0;
        }

        $table = $context['irrf_table'] ?? [
            ['up_to' => 2428.80, 'rate' => 0.00, 'deduction' => 0.00],
            ['up_to' => 2826.65, 'rate' => 0.075, 'deduction' => 182.16],
            ['up_to' => 3751.05, 'rate' => 0.15, 'deduction' => 394.16],
            ['up_to' => 4664.68, 'rate' => 0.225, 'deduction' => 675.49],
            ['up_to' => null, 'rate' => 0.275, 'deduction' => 908.73],
        ];

        foreach ($table as $row) {
            $upTo = $row['up_to'];

            if ($upTo === null || $baseValue <= (float) $upTo) {
                return max(
                    0,
                    $this->money(($baseValue * (float) $row['rate']) - (float) $row['deduction'])
                );
            }
        }

        return 0.0;
    }

    protected function calculateMonthlyIrrfReduction(float $grossTaxableValue, float $grossTax, array $context = []): float
    {
        if ($grossTax <= 0) {
            return 0.0;
        }

        $limitReductionUpTo5000 = (float) ($context['irrf_reduction_limit_5000'] ?? 312.89);
        $reductionFactorBase = (float) ($context['irrf_reduction_factor_base'] ?? 978.62);
        $reductionFactorMultiplier = (float) ($context['irrf_reduction_factor_multiplier'] ?? 0.133145);

        if ($grossTaxableValue <= 5000.00) {
            return $this->money(min($grossTax, $limitReductionUpTo5000));
        }

        if ($grossTaxableValue <= 7350.00) {
            $reduction = $reductionFactorBase - ($reductionFactorMultiplier * $grossTaxableValue);

            return $this->money(max(0, min($grossTax, $reduction)));
        }

        return 0.0;
    }

    protected function emptyIrrfData(): array
    {
        return [
            'irrf_amount' => 0.0,
            'irrf_base' => 0.0,
            'gross_taxable' => 0.0,
            'legal_deductions' => 0.0,
            'simplified_deduction' => 0.0,
            'deduction_method' => null,
            'reduction_amount' => 0.0,
        ];
    }

    protected function calculateFgts(float $baseValue, float $rate): float
    {
        if ($baseValue <= 0 || $rate <= 0) {
            return 0.0;
        }

        return $this->money($baseValue * ($rate / 100));
    }

    protected function parseDate(mixed $value): ?CarbonInterface
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->copy();
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function money(float $value): float
    {
        return round($value, 2);
    }
}