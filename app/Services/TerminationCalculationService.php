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
        $contract = $termination->contract ?? $employee->currentContract;

        if (! $contract) {
            throw new InvalidArgumentException('Contrato atual não encontrado para cálculo da rescisão.');
        }

        $salary = $this->money((float) (
            data_get($context, 'salary')
            ?? $contract->salary
            ?? $employee->salary
            ?? 0
        ));

        if ($salary <= 0) {
            throw new InvalidArgumentException('Salário inválido para cálculo da rescisão.');
        }

        $admissionDate = $this->parseDate(
            data_get($context, 'admission_date')
            ?? $contract->admission_date
            ?? $employee->admission_date
        );

        if (! $admissionDate) {
            throw new InvalidArgumentException('Data de admissão não informada para cálculo da rescisão.');
        }

        $terminationDate = $this->parseDate(
            $termination->termination_date
            ?? data_get($context, 'termination_date')
        );

        if (! $terminationDate) {
            throw new InvalidArgumentException('Data de desligamento não informada.');
        }

        $noticeType = (string) ($termination->notice_type ?? 'not_applicable');
        $dismissalType = (string) ($termination->dismissal_type ?? 'without_cause');
        $noticeDays = max(0, (int) ($termination->notice_days ?? 0));

        $projectedEndDate = $this->parseDate(
            $termination->projected_end_date
            ?? $termination->notice_end_date
            ?? data_get($context, 'projected_end_date')
        );

        $useProjectedDate = $this->shouldProjectNotice(
            dismissalType: $dismissalType,
            noticeType: $noticeType,
            projectedEndDate: $projectedEndDate
        );

        $calculationEndDate = $useProjectedDate && $projectedEndDate
            ? $projectedEndDate->copy()
            : $terminationDate->copy();

        $fgtsBalance = $this->money((float) (data_get($context, 'fgts_balance') ?? 0));
        $unusedVacationPeriods = max(0, (int) (data_get($context, 'unused_vacation_periods') ?? 0));
        $alreadyPaidThirteenth = $this->money((float) (data_get($context, 'thirteenth_advance_paid') ?? 0));
        $extraDiscounts = $this->money((float) (data_get($context, 'extra_discounts') ?? 0));
        $otherEarnings = $this->money((float) (data_get($context, 'other_earnings') ?? 0));

        $salaryBalance = $this->calculateSalaryBalance($salary, $terminationDate);

        $noticePay = $this->calculateNoticePay(
            salary: $salary,
            dismissalType: $dismissalType,
            noticeType: $noticeType,
            noticeDays: $noticeDays
        );

        $thirteenthData = $this->calculateThirteenthProportional(
            salary: $salary,
            admissionDate: $admissionDate,
            referenceDate: $calculationEndDate,
            alreadyPaid: $alreadyPaidThirteenth
        );

        $vacationOverdueData = $this->calculateOverdueVacations(
            salary: $salary,
            overduePeriods: $unusedVacationPeriods
        );

        $vacationProportionalData = $this->calculateProportionalVacations(
            salary: $salary,
            admissionDate: $admissionDate,
            referenceDate: $calculationEndDate
        );

        $fgtsFineRate = $this->resolveFgtsFineRate($dismissalType);

        $grossRescission = $this->money(
            ($salaryBalance['amount'] ?? 0)
            + ($noticePay['amount'] ?? 0)
            + ($thirteenthData['amount'] ?? 0)
            + ($vacationOverdueData['vacation_amount'] ?? 0)
            + ($vacationOverdueData['one_third_amount'] ?? 0)
            + ($vacationProportionalData['vacation_amount'] ?? 0)
            + ($vacationProportionalData['one_third_amount'] ?? 0)
            + $otherEarnings
        );

        $inssBase = $this->money(
            ($salaryBalance['inss_base'] ?? 0)
            + ($noticePay['inss_base'] ?? 0)
            + ($thirteenthData['inss_base'] ?? 0)
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
            ($salaryBalance['fgts_base'] ?? 0)
            + ($noticePay['fgts_base'] ?? 0)
            + ($thirteenthData['fgts_base'] ?? 0)
        );

        $fgtsRate = (float) ($employee->fgts_rate ?? 8.00);
        $fgtsMonthAmount = $employee->has_fgts
            ? $this->calculateFgts($fgtsMonthBase, $fgtsRate)
            : 0.0;

        $fgtsFineBase = $this->money($fgtsBalance + $fgtsMonthAmount);
        $fgtsFineAmount = ($employee->has_fgts && $fgtsFineRate > 0)
            ? $this->money($fgtsFineBase * ($fgtsFineRate / 100))
            : 0.0;

        $totalDiscounts = $this->money(
            $inssAmount
            + ($irrfData['irrf_amount'] ?? 0)
            + ($thirteenthData['discount_amount'] ?? 0)
            + $extraDiscounts
        );

        $netRescission = $this->money($grossRescission - $totalDiscounts);

        if ($netRescission < 0) {
            $netRescission = 0.0;
        }

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'employee_contract_id' => $contract->id ?? null,
            'termination_id' => $termination->id,
            'document_type' => 'rescisao',

            'salary' => $salary,
            'admission_date' => $admissionDate->format('Y-m-d'),
            'termination_date' => $terminationDate->format('Y-m-d'),
            'calculation_end_date' => $calculationEndDate->format('Y-m-d'),
            'dismissal_type' => $dismissalType,
            'notice_type' => $noticeType,
            'notice_days' => $noticeDays,
            'projected_end_date' => $projectedEndDate?->format('Y-m-d'),
            'uses_projected_date' => $useProjectedDate,

            'salary_balance' => $salaryBalance['amount'] ?? 0,
            'salary_balance_days' => $salaryBalance['days'] ?? 0,

            'notice_amount' => $noticePay['amount'] ?? 0,
            'notice_reference_days' => $noticePay['reference_days'] ?? 0,

            'thirteenth_amount' => $thirteenthData['amount'] ?? 0,
            'thirteenth_avos' => $thirteenthData['avos'] ?? 0,
            'thirteenth_advance_discount' => $thirteenthData['discount_amount'] ?? 0,

            'vacation_overdue_amount' => $vacationOverdueData['vacation_amount'] ?? 0,
            'vacation_overdue_one_third' => $vacationOverdueData['one_third_amount'] ?? 0,
            'vacation_overdue_periods' => $vacationOverdueData['periods'] ?? 0,

            'vacation_proportional_amount' => $vacationProportionalData['vacation_amount'] ?? 0,
            'vacation_proportional_one_third' => $vacationProportionalData['one_third_amount'] ?? 0,
            'vacation_proportional_avos' => $vacationProportionalData['avos'] ?? 0,

            'other_earnings' => $otherEarnings,
            'extra_discounts' => $extraDiscounts,
            'gross_amount' => $grossRescission,
            'inss_amount' => $inssAmount,
            'irrf_amount' => $irrfData['irrf_amount'] ?? 0,
            'total_discounts' => $totalDiscounts,
            'net_amount' => $netRescission,

            'base_inss' => $inssBase,
            'base_irrf' => $irrfData['irrf_base'] ?? 0,

            'fgts_rate' => $fgtsRate,
            'fgts_month_base' => $fgtsMonthBase,
            'fgts_month_amount' => $fgtsMonthAmount,
            'fgts_balance' => $fgtsBalance,
            'fgts_fine_rate' => $fgtsFineRate,
            'fgts_fine_base' => $fgtsFineBase,
            'fgts_fine_amount' => $fgtsFineAmount,

            'items' => $this->buildTerminationItems(
                salaryBalance: $salaryBalance,
                noticePay: $noticePay,
                thirteenthData: $thirteenthData,
                vacationOverdueData: $vacationOverdueData,
                vacationProportionalData: $vacationProportionalData,
                inssAmount: $inssAmount,
                irrfAmount: $irrfData['irrf_amount'] ?? 0,
                fgtsMonthAmount: $fgtsMonthAmount,
                fgtsFineAmount: $fgtsFineAmount,
                fgtsFineRate: $fgtsFineRate,
                otherEarnings: $otherEarnings,
                extraDiscounts: $extraDiscounts
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
            'inss_base' => $amount,
            'fgts_base' => $amount,
        ];
    }

    protected function calculateNoticePay(
        float $salary,
        string $dismissalType,
        string $noticeType,
        int $noticeDays
    ): array {
        if ($noticeDays <= 0 || ! $this->noticeCanGeneratePay($dismissalType, $noticeType)) {
            return [
                'reference_days' => max(0, $noticeDays),
                'amount' => 0.0,
                'inss_base' => 0.0,
                'fgts_base' => 0.0,
            ];
        }

        if ($noticeType === 'worked') {
            return [
                'reference_days' => $noticeDays,
                'amount' => 0.0,
                'inss_base' => 0.0,
                'fgts_base' => 0.0,
            ];
        }

        $amount = $this->money(($salary / 30) * $noticeDays);

        return [
            'reference_days' => $noticeDays,
            'amount' => $amount,
            'inss_base' => 0.0,
            'fgts_base' => $amount,
        ];
    }

    protected function calculateThirteenthProportional(
        float $salary,
        CarbonInterface $admissionDate,
        CarbonInterface $referenceDate,
        float $alreadyPaid = 0.0
    ): array {
        $avos = $this->calculateAvosByFifteenDays($admissionDate, $referenceDate, $referenceDate->year);
        $amount = $this->money(($salary / 12) * $avos);
        $discountAmount = $this->money(min($amount, max(0, $alreadyPaid)));

        return [
            'avos' => $avos,
            'amount' => $amount,
            'discount_amount' => $discountAmount,
            'inss_base' => $amount,
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
        CarbonInterface $admissionDate,
        CarbonInterface $referenceDate
    ): array {
        $cycleStart = $admissionDate->copy();

        while ($cycleStart->copy()->addYear()->lessThanOrEqualTo($referenceDate)) {
            $cycleStart->addYear();
        }

        $avos = $this->calculateAvosByFifteenDays($cycleStart, $referenceDate, null);
        $avos = min(12, $avos);

        $vacationAmount = $this->money(($salary / 12) * $avos);
        $oneThird = $this->money($vacationAmount / 3);

        return [
            'avos' => $avos,
            'vacation_amount' => $vacationAmount,
            'one_third_amount' => $oneThird,
        ];
    }

    protected function calculateAvosByFifteenDays(
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        ?int $fixedYear = null
    ): int {
        if ($startDate->greaterThan($endDate)) {
            return 0;
        }

        $cursor = $fixedYear
            ? Carbon::create($fixedYear, 1, 1)->startOfMonth()
            : $startDate->copy()->startOfMonth();

        $limit = $fixedYear
            ? Carbon::create($fixedYear, 12, 31)->endOfMonth()
            : $endDate->copy()->endOfMonth();

        if ($limit->greaterThan($endDate->copy()->endOfMonth())) {
            $limit = $endDate->copy()->endOfMonth();
        }

        $avos = 0;

        while ($cursor->lessThanOrEqualTo($limit)) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            if ($startDate->greaterThan($monthEnd) || $endDate->lessThan($monthStart)) {
                $cursor->addMonthNoOverflow();
                continue;
            }

            $workedStart = $startDate->greaterThan($monthStart)
                ? $startDate->copy()->startOfDay()
                : $monthStart->copy()->startOfDay();

            $workedEnd = $endDate->lessThan($monthEnd)
                ? $endDate->copy()->endOfDay()
                : $monthEnd->copy()->endOfDay();

            $workedDays = $workedStart->diffInDays($workedEnd) + 1;

            if ($workedDays >= 15) {
                $avos++;
            }

            $cursor->addMonthNoOverflow();
        }

        return max(0, min(12, $avos));
    }

    protected function shouldProjectNotice(
        string $dismissalType,
        string $noticeType,
        ?CarbonInterface $projectedEndDate
    ): bool {
        if (! $projectedEndDate) {
            return false;
        }

        if (! in_array($dismissalType, ['without_cause', 'mutual_agreement'], true)) {
            return false;
        }

        return in_array($noticeType, ['indemnified', 'dismissed'], true);
    }

    protected function noticeCanGeneratePay(string $dismissalType, string $noticeType): bool
    {
        if (! in_array($noticeType, ['indemnified', 'dismissed'], true)) {
            return false;
        }

        return in_array($dismissalType, ['without_cause', 'mutual_agreement'], true);
    }

    protected function resolveFgtsFineRate(string $dismissalType): float
    {
        return match ($dismissalType) {
            'without_cause' => 40.0,
            'mutual_agreement' => 20.0,
            default => 0.0,
        };
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
        float $fgtsFineAmount,
        float $fgtsFineRate,
        float $otherEarnings = 0.0,
        float $extraDiscounts = 0.0
    ): array {
        $items = [];

        $this->pushItem($items, 'SALDO_SAL', 'Saldo de Salário', 'provento', $salaryBalance['days'] ?? 0, $salaryBalance['amount'] ?? 0);
        $this->pushItem($items, 'AVISO', 'Aviso Prévio Indenizado', 'provento', $noticePay['reference_days'] ?? 0, $noticePay['amount'] ?? 0);
        $this->pushItem($items, '13PROP', '13º Proporcional', 'provento', $thirteenthData['avos'] ?? 0, $thirteenthData['amount'] ?? 0);
        $this->pushItem($items, 'FERIAS_VENC', 'Férias Vencidas', 'provento', $vacationOverdueData['periods'] ?? 0, $vacationOverdueData['vacation_amount'] ?? 0);
        $this->pushItem($items, 'FERIAS_VENC_13', '1/3 sobre Férias Vencidas', 'provento', 0, $vacationOverdueData['one_third_amount'] ?? 0);
        $this->pushItem($items, 'FERIAS_PROP', 'Férias Proporcionais', 'provento', $vacationProportionalData['avos'] ?? 0, $vacationProportionalData['vacation_amount'] ?? 0);
        $this->pushItem($items, 'FERIAS_PROP_13', '1/3 sobre Férias Proporcionais', 'provento', 0, $vacationProportionalData['one_third_amount'] ?? 0);
        $this->pushItem($items, 'OUTROS_PROV', 'Outros Proventos', 'provento', 0, $otherEarnings);

        $this->pushItem($items, 'DESC_13_ADIANT', 'Desconto Adiantamento 13º', 'desconto', 0, $thirteenthData['discount_amount'] ?? 0);
        $this->pushItem($items, 'INSS', 'Desconto INSS', 'desconto', 0, $inssAmount);
        $this->pushItem($items, 'IRRF', 'Desconto IRRF', 'desconto', 0, $irrfAmount);
        $this->pushItem($items, 'OUTROS_DESC', 'Outros Descontos', 'desconto', 0, $extraDiscounts);

        $this->pushItem($items, 'FGTS', 'FGTS da Rescisão', 'informativo', 0, $fgtsMonthAmount);

        if ($fgtsFineAmount > 0) {
            $this->pushItem(
                $items,
                'MULTA_FGTS',
                'Multa de ' . number_format($fgtsFineRate, 0, ',', '.') . '% do FGTS',
                'provento',
                $fgtsFineRate,
                $fgtsFineAmount
            );
        }

        return array_values($items);
    }

    protected function pushItem(
        array &$items,
        string $code,
        string $description,
        string $type,
        float|int $reference,
        float|int $amount,
        string $source = 'termination'
    ): void {
        $amount = $this->money((float) $amount);

        if ($amount <= 0) {
            return;
        }

        $items[] = [
            'code' => $code,
            'description' => $description,
            'type' => $type,
            'reference' => (float) $reference,
            'amount' => $amount,
            'source' => $source,
        ];
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
                return max(0, $this->money(($baseValue * (float) $row['rate']) - (float) $row['deduction']));
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
