<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeFixedEvent;
use App\Models\EmployeeVariableEvent;
use App\Models\SalaryAdvance;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PayrollCalculationService
{
    public function calculateEmployee(Employee $employee, array $context = []): array
{
    $competencyId = $context['payroll_competency_id'] ?? null;
    $contract = $employee->currentContract;

    if (! $contract) {
        return [];
    }

    if (! in_array($contract->status, ['ativo', 'em_aviso'], true)) {
        return [];
    }

    $processingType = $context['processing_type']
        ?? $context['run_type']
        ?? $employee->processing_type
        ?? 'payroll_clt';

    $context['employee_contract_id'] = $context['employee_contract_id'] ?? $contract->id;
    $context['salary'] = $context['salary'] ?? (float) ($contract->salary ?? $employee->salary ?? 0);
    $context['admission_date'] = $context['admission_date'] ?? optional($contract->admission_date)?->format('Y-m-d');
    $context['termination_date'] = $context['termination_date'] ?? optional($contract->termination_date)?->format('Y-m-d');

    $fixedEvents = $this->getEmployeeFixedEvents($employee);
    $variableEvents = $this->getEmployeeVariableEvents($employee, $competencyId);
    $salaryAdvanceDiscount = $this->getSalaryAdvanceDiscount($employee, $competencyId);

    return match ($processingType) {
        'payroll_clt' => $this->processClt(
            employee: $employee,
            context: $context,
            fixedEvents: $fixedEvents,
            variableEvents: $variableEvents,
            salaryAdvanceDiscount: $salaryAdvanceDiscount,
        ),

        'payroll_rpa' => $this->processRpa(
            employee: $employee,
            context: $context,
            fixedEvents: $fixedEvents,
            variableEvents: $variableEvents,
            salaryAdvanceDiscount: $salaryAdvanceDiscount,
        ),

        'internship_payment' => $this->processInternship(
            employee: $employee,
            context: $context,
            fixedEvents: $fixedEvents,
            variableEvents: $variableEvents,
            salaryAdvanceDiscount: $salaryAdvanceDiscount,
        ),

        'accounts_payable' => $this->processAccountsPayable(
            employee: $employee,
            context: $context,
            fixedEvents: $fixedEvents,
            variableEvents: $variableEvents,
            salaryAdvanceDiscount: $salaryAdvanceDiscount,
        ),

        default => throw new InvalidArgumentException("Tipo de processamento inválido: {$processingType}"),
    };
}

public function calculate(Employee $employee, array $context = []): array
{
    return $this->calculateEmployee($employee, $context);
}

    protected function processClt(
        Employee $employee,
        array $context,
        Collection $fixedEvents,
        Collection $variableEvents,
        float $salaryAdvanceDiscount = 0
    ): array {
        $salaryData = $this->resolveBaseSalaryData($employee, $context);
        $baseSalary = $salaryData['base_salary'];

        $eventSummary = $this->summarizeEvents($employee, $fixedEvents, $variableEvents);

        $grossAmount = $this->money(
            $baseSalary + $eventSummary['provents_total']
        );

        $inssBase = $grossAmount;
        $inssAmount = $employee->has_inss ? $this->calculateInss($inssBase, $context) : 0.0;

        $irrfData = $employee->has_irrf
            ? $this->calculateIrrf(
                grossTaxableValue: $grossAmount,
                inssAmount: $inssAmount,
                context: $context
            )
            : $this->emptyIrrfData();

        $fgtsBase = $grossAmount;
        $fgtsRate = $employee->fgts_rate !== null ? (float) $employee->fgts_rate : 8.00;
        $fgtsAmount = $employee->has_fgts ? $this->calculateFgts($fgtsBase, $fgtsRate) : 0.0;

        $manualDiscounts = $eventSummary['discounts_total'];

        $totalDiscounts = $this->money(
            $manualDiscounts
            + $salaryAdvanceDiscount
            + $inssAmount
            + $irrfData['irrf_amount']
        );

        $netAmount = $this->money($grossAmount - $totalDiscounts);

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'processing_type' => $employee->processing_type,
            'document_type' => 'holerite',
            'gross_amount' => $grossAmount,
            'inss_amount' => $inssAmount,
            'irrf_amount' => $irrfData['irrf_amount'],
            'fgts_amount' => $fgtsAmount,
            'salary_advance_discount' => $salaryAdvanceDiscount,
            'event_provents_total' => $eventSummary['provents_total'],
            'event_discounts_total' => $manualDiscounts,
            'total_discounts' => $totalDiscounts,
            'net_amount' => $netAmount,
            'base_salary' => $baseSalary,
            'full_salary' => $salaryData['full_salary'],
            'worked_days' => $salaryData['worked_days'],
            'salary_reference_days' => $salaryData['reference_days'],
            'salary_is_proportional' => $salaryData['is_proportional'],
            'base_inss' => $inssBase,
            'base_fgts' => $fgtsBase,
            'base_irrf' => $irrfData['irrf_base'],
            'irrf_gross_taxable' => $irrfData['gross_taxable'],
            'irrf_legal_deductions' => $irrfData['legal_deductions'],
            'irrf_simplified_deduction' => $irrfData['simplified_deduction'],
            'irrf_deduction_method' => $irrfData['deduction_method'],
            'irrf_reduction_amount' => $irrfData['reduction_amount'],
            'fgts_rate' => $fgtsRate,
            'items' => $this->buildPayrollItems(
                employee: $employee,
                baseSalary: $baseSalary,
                salaryData: $salaryData,
                fixedEvents: $fixedEvents,
                variableEvents: $variableEvents,
                inssAmount: $inssAmount,
                irrfAmount: $irrfData['irrf_amount'],
                fgtsAmount: $fgtsAmount,
                salaryAdvanceDiscount: $salaryAdvanceDiscount,
            ),
            'calculation_memory' => [
                'base_salary' => $baseSalary,
                'provents_total' => $eventSummary['provents_total'],
                'gross_amount' => $grossAmount,
                'manual_discounts' => $manualDiscounts,
                'salary_advance_discount' => $salaryAdvanceDiscount,
                'inss_amount' => $inssAmount,
                'irrf_amount' => $irrfData['irrf_amount'],
                'total_discounts' => $totalDiscounts,
                'net_amount' => $netAmount,
                'ignored_events' => $eventSummary['ignored_events'] ?? [],
            ],
        ];
    }

    protected function processRpa(
        Employee $employee,
        array $context,
        Collection $fixedEvents,
        Collection $variableEvents,
        float $salaryAdvanceDiscount = 0
    ): array {
        $salaryData = $this->resolveBaseSalaryData($employee, $context, prorate: false);
        $baseAmount = $salaryData['base_salary'];

        $eventSummary = $this->summarizeEvents($employee, $fixedEvents, $variableEvents);

        $grossAmount = $this->money($baseAmount + $eventSummary['provents_total']);

        $withInss = (bool) ($context['with_inss'] ?? $employee->with_inss ?? true);

        $inssAmount = ($employee->has_inss && $withInss)
            ? $this->calculateInss($grossAmount, $context)
            : 0.0;

        $irrfData = $employee->has_irrf
            ? $this->calculateIrrf(
                grossTaxableValue: $grossAmount,
                inssAmount: $inssAmount,
                context: $context
            )
            : $this->emptyIrrfData();

        $eventDiscounts = $eventSummary['discounts_total'];

        $totalDiscounts = $this->money(
            $eventDiscounts
            + $salaryAdvanceDiscount
            + $inssAmount
            + $irrfData['irrf_amount']
        );

        $netAmount = $this->money($grossAmount - $totalDiscounts);

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'processing_type' => $employee->processing_type,
            'document_type' => 'holerite',
            'gross_amount' => $grossAmount,
            'inss_amount' => $inssAmount,
            'irrf_amount' => $irrfData['irrf_amount'],
            'fgts_amount' => 0.0,
            'salary_advance_discount' => $salaryAdvanceDiscount,
            'event_provents_total' => $eventSummary['provents_total'],
            'event_discounts_total' => $eventDiscounts,
            'total_discounts' => $totalDiscounts,
            'net_amount' => $netAmount,
            'base_salary' => $baseAmount,
            'full_salary' => $salaryData['full_salary'],
            'worked_days' => $salaryData['worked_days'],
            'salary_reference_days' => $salaryData['reference_days'],
            'salary_is_proportional' => $salaryData['is_proportional'],
            'base_inss' => $grossAmount,
            'base_fgts' => 0.0,
            'base_irrf' => $irrfData['irrf_base'],
            'irrf_gross_taxable' => $irrfData['gross_taxable'],
            'irrf_legal_deductions' => $irrfData['legal_deductions'],
            'irrf_simplified_deduction' => $irrfData['simplified_deduction'],
            'irrf_deduction_method' => $irrfData['deduction_method'],
            'irrf_reduction_amount' => $irrfData['reduction_amount'],
            'fgts_rate' => 0.0,
            'items' => $this->buildPayrollItems(
                employee: $employee,
                baseSalary: $baseAmount,
                salaryData: $salaryData,
                fixedEvents: $fixedEvents,
                variableEvents: $variableEvents,
                inssAmount: $inssAmount,
                irrfAmount: $irrfData['irrf_amount'],
                fgtsAmount: 0.0,
                salaryAdvanceDiscount: $salaryAdvanceDiscount,
            ),
            'calculation_memory' => [
                'base_salary' => $baseAmount,
                'provents_total' => $eventSummary['provents_total'],
                'gross_amount' => $grossAmount,
                'manual_discounts' => $eventDiscounts,
                'salary_advance_discount' => $salaryAdvanceDiscount,
                'inss_amount' => $inssAmount,
                'irrf_amount' => $irrfData['irrf_amount'],
                'total_discounts' => $totalDiscounts,
                'net_amount' => $netAmount,
                'ignored_events' => $eventSummary['ignored_events'] ?? [],
            ],
        ];
    }

    protected function processInternship(
        Employee $employee,
        array $context,
        Collection $fixedEvents,
        Collection $variableEvents,
        float $salaryAdvanceDiscount = 0
    ): array {
        $salaryData = $this->resolveBaseSalaryData($employee, $context, prorate: false);
        $baseAmount = $salaryData['base_salary'];

        $eventSummary = $this->summarizeEvents($employee, $fixedEvents, $variableEvents);

        $grossAmount = $this->money($baseAmount + $eventSummary['provents_total']);
        $eventDiscounts = $eventSummary['discounts_total'];

        $totalDiscounts = $this->money($eventDiscounts + $salaryAdvanceDiscount);
        $netAmount = $this->money($grossAmount - $totalDiscounts);

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'processing_type' => $employee->processing_type,
            'document_type' => 'holerite',
            'gross_amount' => $grossAmount,
            'inss_amount' => 0.0,
            'irrf_amount' => 0.0,
            'fgts_amount' => 0.0,
            'salary_advance_discount' => $salaryAdvanceDiscount,
            'event_provents_total' => $eventSummary['provents_total'],
            'event_discounts_total' => $eventDiscounts,
            'total_discounts' => $totalDiscounts,
            'net_amount' => $netAmount,
            'base_salary' => $baseAmount,
            'full_salary' => $salaryData['full_salary'],
            'worked_days' => $salaryData['worked_days'],
            'salary_reference_days' => $salaryData['reference_days'],
            'salary_is_proportional' => $salaryData['is_proportional'],
            'base_inss' => 0.0,
            'base_fgts' => 0.0,
            'base_irrf' => 0.0,
            'fgts_rate' => 0.0,
            'items' => $this->buildPayrollItems(
                employee: $employee,
                baseSalary: $baseAmount,
                salaryData: $salaryData,
                fixedEvents: $fixedEvents,
                variableEvents: $variableEvents,
                inssAmount: 0.0,
                irrfAmount: 0.0,
                fgtsAmount: 0.0,
                salaryAdvanceDiscount: $salaryAdvanceDiscount,
            ),
            'calculation_memory' => [
                'base_salary' => $baseAmount,
                'provents_total' => $eventSummary['provents_total'],
                'gross_amount' => $grossAmount,
                'manual_discounts' => $eventDiscounts,
                'salary_advance_discount' => $salaryAdvanceDiscount,
                'total_discounts' => $totalDiscounts,
                'net_amount' => $netAmount,
                'ignored_events' => $eventSummary['ignored_events'] ?? [],
            ],
        ];
    }

    protected function processAccountsPayable(
        Employee $employee,
        array $context,
        Collection $fixedEvents,
        Collection $variableEvents,
        float $salaryAdvanceDiscount = 0
    ): array {
        $salaryData = $this->resolveBaseSalaryData($employee, $context, prorate: false);
        $baseAmount = $salaryData['base_salary'];

        $eventSummary = $this->summarizeEvents($employee, $fixedEvents, $variableEvents);

        $grossAmount = $this->money($baseAmount + $eventSummary['provents_total']);
        $eventDiscounts = $eventSummary['discounts_total'];

        $totalDiscounts = $this->money($eventDiscounts + $salaryAdvanceDiscount);
        $netAmount = $this->money($grossAmount - $totalDiscounts);

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'processing_type' => $employee->processing_type,
            'document_type' => 'holerite',
            'gross_amount' => $grossAmount,
            'inss_amount' => 0.0,
            'irrf_amount' => 0.0,
            'fgts_amount' => 0.0,
            'salary_advance_discount' => $salaryAdvanceDiscount,
            'event_provents_total' => $eventSummary['provents_total'],
            'event_discounts_total' => $eventDiscounts,
            'total_discounts' => $totalDiscounts,
            'net_amount' => $netAmount,
            'base_salary' => $baseAmount,
            'full_salary' => $salaryData['full_salary'],
            'worked_days' => $salaryData['worked_days'],
            'salary_reference_days' => $salaryData['reference_days'],
            'salary_is_proportional' => $salaryData['is_proportional'],
            'base_inss' => 0.0,
            'base_fgts' => 0.0,
            'base_irrf' => 0.0,
            'fgts_rate' => 0.0,
            'items' => $this->buildPayrollItems(
                employee: $employee,
                baseSalary: $baseAmount,
                salaryData: $salaryData,
                fixedEvents: $fixedEvents,
                variableEvents: $variableEvents,
                inssAmount: 0.0,
                irrfAmount: 0.0,
                fgtsAmount: 0.0,
                salaryAdvanceDiscount: $salaryAdvanceDiscount,
            ),
            'calculation_memory' => [
                'base_salary' => $baseAmount,
                'provents_total' => $eventSummary['provents_total'],
                'gross_amount' => $grossAmount,
                'manual_discounts' => $eventDiscounts,
                'salary_advance_discount' => $salaryAdvanceDiscount,
                'total_discounts' => $totalDiscounts,
                'net_amount' => $netAmount,
                'ignored_events' => $eventSummary['ignored_events'] ?? [],
            ],
        ];
    }

    protected function getEmployeeFixedEvents(Employee $employee): Collection
    {
        return EmployeeFixedEvent::query()
            ->with('payrollEvent')
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->get();
    }

    protected function getEmployeeVariableEvents(Employee $employee, ?int $competencyId): Collection
    {
        if (! $competencyId) {
            return collect();
        }

        return EmployeeVariableEvent::query()
            ->with('payrollEvent')
            ->where('employee_id', $employee->id)
            ->where('payroll_competency_id', $competencyId)
            ->get();
    }

    protected function getSalaryAdvanceDiscount(Employee $employee, ?int $competencyId): float
    {
        if (! $competencyId) {
            return 0.0;
        }

        return $this->money(
            SalaryAdvance::query()
                ->where('employee_id', $employee->id)
                ->where('payroll_competency_id', $competencyId)
                ->where('status', 'paid')
                ->sum('amount')
        );
    }

    protected function summarizeEvents(
        Employee $employee,
        Collection $fixedEvents,
        Collection $variableEvents
    ): array {
        $allEvents = $this->mergeUniqueEvents($fixedEvents, $variableEvents);

        $proventsTotal = 0.0;
        $discountsTotal = 0.0;
        $ignored = [];

        foreach ($allEvents as $eventItem) {
            $type = $this->normalizeEventType(
                $eventItem->payrollEvent?->type ?? $eventItem->type ?? null
            );

            $rawAmount = (float) ($eventItem->amount ?? 0);

            if ($rawAmount == 0.0) {
                $ignored[] = [
                    'reason' => 'amount_zero',
                    'event_id' => $eventItem->id ?? null,
                    'payroll_event_id' => $eventItem->payroll_event_id ?? null,
                    'description' => $eventItem->payrollEvent?->name ?? 'Evento sem nome',
                ];
                continue;
            }

            $amount = $this->money(abs($rawAmount));

            if ($type === null) {
                $ignored[] = [
                    'reason' => 'type_invalid',
                    'event_id' => $eventItem->id ?? null,
                    'payroll_event_id' => $eventItem->payroll_event_id ?? null,
                    'description' => $eventItem->payrollEvent?->name ?? 'Evento sem nome',
                    'original_type' => $eventItem->payrollEvent?->type ?? $eventItem->type ?? null,
                ];
                continue;
            }

            if ($this->isSalaryDuplicateEvent($eventItem)) {
                $ignored[] = [
                    'reason' => 'salary_duplicate',
                    'event_id' => $eventItem->id ?? null,
                    'payroll_event_id' => $eventItem->payroll_event_id ?? null,
                    'description' => $eventItem->payrollEvent?->name ?? 'Evento salário duplicado',
                ];
                continue;
            }

            if ($type === 'provento') {
                $proventsTotal += $amount;
                continue;
            }

            if ($type === 'desconto') {
                $discountsTotal += $amount;
            }
        }

        return [
            'employee_id' => $employee->id,
            'provents_total' => $this->money($proventsTotal),
            'discounts_total' => $this->money($discountsTotal),
            'ignored_events' => $ignored,
        ];
    }

    protected function mergeUniqueEvents(Collection $fixedEvents, Collection $variableEvents): Collection
    {
        $events = $fixedEvents->concat($variableEvents);
        $seen = [];

        return $events->filter(function ($event) use (&$seen) {
            $key = implode('|', [
                $event instanceof EmployeeFixedEvent ? 'fixed' : ($event instanceof EmployeeVariableEvent ? 'variable' : class_basename($event)),
                (string) ($event->id ?? 'no-id'),
                (string) ($event->payroll_event_id ?? 'no-payroll-event'),
                number_format((float) ($event->amount ?? 0), 2, '.', ''),
                (string) ($event->reference ?? ''),
                (string) ($event->quantity ?? ''),
                (string) ($event->days ?? ''),
                (string) ($event->hours ?? ''),
            ]);

            if (isset($seen[$key])) {
                return false;
            }

            $seen[$key] = true;
            return true;
        })->values();
    }

    protected function normalizeEventType(?string $type): ?string
    {
        if (blank($type)) {
            return null;
        }

        $type = mb_strtolower(trim((string) $type));

        return match ($type) {
            'provento',
            'earning',
            'vencimento',
            'credito',
            'crédito',
            'credito_manual',
            'crédito_manual'
                => 'provento',

            'desconto',
            'deduction',
            'debito',
            'débito',
            'desconto_manual'
                => 'desconto',

            'informativo',
            'info'
                => 'informativo',

            default => null,
        };
    }

    protected function isSalaryDuplicateEvent(object $event): bool
    {
        $code = mb_strtolower(trim((string) ($event->payrollEvent?->code ?? '')));

        return in_array($code, ['salario', 'salario_base', 'sal'], true);
    }

    protected function buildPayrollItems(
    Employee $employee,
    float $baseSalary,
    array $salaryData,
    Collection $fixedEvents,
    Collection $variableEvents,
    float $inssAmount,
    float $irrfAmount,
    float $fgtsAmount,
    float $salaryAdvanceDiscount
): array {
    $items = [];

    // 🔥 SALÁRIO BASE
    if ($baseSalary > 0) {
        $salaryDescription = $salaryData['is_proportional']
            ? sprintf(
                'Salário Base Proporcional (%d/%d)',
                (int) $salaryData['worked_days'],
                (int) $salaryData['reference_days']
            )
            : 'Salário Base';

        $items[] = [
            'code' => 'SALARIO',
            'description' => $salaryDescription,
            'type' => 'provento',
            'amount' => $this->money($baseSalary),
            'reference' => $salaryData['is_proportional']
                ? 0 // 🔥 NÃO SALVAR STRING
                : (float) ($salaryData['reference_days'] ?: 30),
            'source' => 'base_salary',
        ];
    }

    // 🔥 EVENTOS
    foreach ($this->mergeUniqueEvents($fixedEvents, $variableEvents) as $event) {
        $type = $this->normalizeEventType($event->payrollEvent?->type ?? $event->type ?? null);
        $rawAmount = (float) ($event->amount ?? 0);

        if ($type === null || $rawAmount == 0.0 || $this->isSalaryDuplicateEvent($event)) {
            continue;
        }

        $items[] = [
            'code' => $event->payrollEvent?->code ?? ($event instanceof EmployeeFixedEvent ? 'FIXO' : 'VAR'),
            'description' => $event->payrollEvent?->name ?? 'Evento',
            'type' => $type === 'informativo' ? 'informativo' : $type,
            'reference' => $this->normalizeReferenceNumber($this->resolveEventReference($event)),
            'amount' => $this->money(abs($rawAmount)),
            'source' => $event instanceof EmployeeFixedEvent ? 'fixed_event' : 'variable_event',
            'payroll_event_id' => $event->payroll_event_id,
        ];
    }

    // 🔥 ADIANTAMENTO
    if ($salaryAdvanceDiscount > 0) {
        $items[] = [
            'code' => 'ADIANT',
            'description' => 'Desconto de Adiantamento',
            'type' => 'desconto',
            'amount' => $this->money($salaryAdvanceDiscount),
            'reference' => 0, // 🔥 CORREÇÃO
            'source' => 'salary_advance',
        ];
    }

    // 🔥 INSS
    if ($inssAmount > 0) {
        $items[] = [
            'code' => 'INSS',
            'description' => 'Desconto INSS',
            'type' => 'desconto',
            'amount' => $this->money($inssAmount),
            'reference' => 0, // 🔥 CORREÇÃO
            'source' => 'calculation',
        ];
    }

    // 🔥 IRRF
    if ($irrfAmount > 0) {
        $items[] = [
            'code' => 'IRRF',
            'description' => 'Desconto IRRF',
            'type' => 'desconto',
            'amount' => $this->money($irrfAmount),
            'reference' => 0, // 🔥 CORREÇÃO
            'source' => 'calculation',
        ];
    }

    // 🔥 FGTS
    if ($fgtsAmount > 0) {
        $items[] = [
            'code' => 'FGTS',
            'description' => 'FGTS (Depósito)',
            'type' => 'informativo',
            'amount' => $this->money($fgtsAmount),
            'reference' => 0, // 🔥 CORREÇÃO
            'source' => 'calculation',
        ];
    }

    return array_values($items);
}
    protected function resolveEventReference(object $event): ?string
{
    $eventName = mb_strtolower((string) ($event->payrollEvent?->name ?? ''));
    $eventCode = mb_strtolower((string) ($event->payrollEvent?->code ?? ''));

    $isFalta = str_contains($eventName, 'falta') || str_contains($eventCode, 'falta');
    $isHora = str_contains($eventName, 'hora') || str_contains($eventCode, 'hora');

    if ($isFalta) {
        $faltasFields = [
            'quantity',
            'qty',
            'days',
            'days_quantity',
            'reference_quantity',
            'reference',
        ];

        foreach ($faltasFields as $field) {
            if (isset($event->{$field}) && $event->{$field} !== null && $event->{$field} !== '') {
                $value = $event->{$field};

                if (is_numeric($value)) {
                    return number_format((float) $value, 2, ',', '.');
                }

                return mb_substr(trim((string) $value), 0, 30);
            }
        }

        return null;
    }

    if ($isHora) {
        $hoursFields = [
            'hours',
            'hour_quantity',
            'hours_quantity',
            'worked_hours',
            'overtime_hours',
            'extra_hours',
            'quantity',
            'qty',
            'reference_quantity',
            'reference',
        ];

        foreach ($hoursFields as $field) {
            if (isset($event->{$field}) && $event->{$field} !== null && $event->{$field} !== '') {
                $value = $event->{$field};

                if (is_numeric($value)) {
                    return number_format((float) $value, 2, ',', '.');
                }

                return mb_substr(trim((string) $value), 0, 30);
            }
        }

        return null;
    }

    $possibleFields = [
        'reference',
        'quantity',
        'qty',
        'hours',
        'hour_quantity',
        'hours_quantity',
        'worked_hours',
        'overtime_hours',
        'extra_hours',
        'days',
        'days_quantity',
        'percent',
        'percentage',
    ];

    foreach ($possibleFields as $field) {
        if (isset($event->{$field}) && $event->{$field} !== null && $event->{$field} !== '') {
            $value = $event->{$field};

            if (is_numeric($value)) {
                return number_format((float) $value, 2, ',', '.');
            }

            return mb_substr(trim((string) $value), 0, 30);
        }
    }

    return null;
}

    protected function resolveBaseSalaryData(Employee $employee, array $context, ?bool $prorate = null): array
    {
        $fullSalary = isset($context['salary'])
        ? $this->money((float) $context['salary'])
        : $this->money((float) (
            $employee->currentContract?->salary
            ?? $employee->salary
            ?? 0
        ));

        $shouldProrate = $prorate ?? (bool) ($context['prorate_salary_on_admission'] ?? true);

        if (! $shouldProrate || $fullSalary <= 0) {
            return [
                'base_salary' => $fullSalary,
                'full_salary' => $fullSalary,
                'worked_days' => $context['worked_days'] ?? null,
                'reference_days' => $context['salary_divisor_days'] ?? 30,
                'is_proportional' => false,
            ];
        }

        $period = $this->resolveCompetencyPeriod($context);

        if (! $period) {
            return [
                'base_salary' => $fullSalary,
                'full_salary' => $fullSalary,
                'worked_days' => null,
                'reference_days' => $context['salary_divisor_days'] ?? 30,
                'is_proportional' => false,
            ];
        }

        $admissionDate = $this->resolveEmployeeAdmissionDate($employee);
        $terminationDate = $this->resolveEmployeeTerminationDate($employee);

        if (! $admissionDate && ! $terminationDate) {
            return [
                'base_salary' => $fullSalary,
                'full_salary' => $fullSalary,
                'worked_days' => $this->resolveFullWorkedDaysForDisplay($period, $context),
                'reference_days' => $this->resolveSalaryDivisorDays($period, $context),
                'is_proportional' => false,
            ];
        }

        $workedDays = $this->resolveWorkedDaysInPeriod(
            periodStart: $period['start'],
            periodEnd: $period['end'],
            admissionDate: $admissionDate,
            terminationDate: $terminationDate,
            context: $context,
        );

        $referenceDays = $this->resolveSalaryDivisorDays($period, $context);

        if ($workedDays === null || $referenceDays <= 0) {
            return [
                'base_salary' => $fullSalary,
                'full_salary' => $fullSalary,
                'worked_days' => null,
                'reference_days' => $referenceDays ?: 30,
                'is_proportional' => false,
            ];
        }

        $isProportional = $workedDays < $referenceDays;

        $baseSalary = $isProportional
            ? $this->money(($fullSalary / $referenceDays) * $workedDays)
            : $fullSalary;

        return [
            'base_salary' => $baseSalary,
            'full_salary' => $fullSalary,
            'worked_days' => $workedDays,
            'reference_days' => $referenceDays,
            'is_proportional' => $isProportional,
        ];
    }

    protected function resolveCompetencyPeriod(array $context): ?array
    {
        $start = $this->parseDate(
            $context['period_start']
                ?? $context['competency_start']
                ?? $context['payroll_period_start']
                ?? data_get($context, 'payroll_competency.start_date')
                ?? data_get($context, 'payroll_competency.period_start')
                ?? data_get($context, 'payroll_competency.start_at')
        );

        $end = $this->parseDate(
            $context['period_end']
                ?? $context['competency_end']
                ?? $context['payroll_period_end']
                ?? data_get($context, 'payroll_competency.end_date')
                ?? data_get($context, 'payroll_competency.period_end')
                ?? data_get($context, 'payroll_competency.end_at')
        );

        if ($start && $end) {
            return [
                'start' => $start->copy()->startOfDay(),
                'end' => $end->copy()->endOfDay(),
            ];
        }

        $month = $context['competency_month']
            ?? $context['month']
            ?? data_get($context, 'payroll_competency.month');

        $year = $context['competency_year']
            ?? $context['year']
            ?? data_get($context, 'payroll_competency.year');

        if ($month && $year) {
            $date = Carbon::createFromDate((int) $year, (int) $month, 1);

            return [
                'start' => $date->copy()->startOfMonth()->startOfDay(),
                'end' => $date->copy()->endOfMonth()->endOfDay(),
            ];
        }

        $referenceDate = $this->parseDate(
            $context['payment_date']
                ?? $context['reference_date']
                ?? $context['competency_date']
                ?? data_get($context, 'payroll_competency.reference_date')
        );

        if ($referenceDate) {
            return [
                'start' => $referenceDate->copy()->startOfMonth()->startOfDay(),
                'end' => $referenceDate->copy()->endOfMonth()->endOfDay(),
            ];
        }

        return null;
    }

    protected function resolveEmployeeAdmissionDate(Employee $employee): ?CarbonInterface
    {
        return $this->parseDate(
            $employee->admission_date
                ?? $employee->hire_date
                ?? $employee->admitted_at
                ?? $employee->start_date
                ?? null
        );
    }

    protected function resolveEmployeeTerminationDate(Employee $employee): ?CarbonInterface
    {
        return $this->parseDate(
            $employee->termination_date
                ?? $employee->dismissal_date
                ?? $employee->demission_date
                ?? $employee->end_date
                ?? null
        );
    }

    protected function resolveWorkedDaysInPeriod(
        CarbonInterface $periodStart,
        CarbonInterface $periodEnd,
        ?CarbonInterface $admissionDate,
        ?CarbonInterface $terminationDate,
        array $context = []
    ): ?int {
        $workStart = $periodStart->copy()->startOfDay();
        $workEnd = $periodEnd->copy()->endOfDay();

        if ($admissionDate) {
            $admissionDate = $admissionDate->copy()->startOfDay();

            if ($admissionDate->greaterThan($workStart)) {
                $workStart = $admissionDate;
            }
        }

        if ($terminationDate) {
            $terminationDate = $terminationDate->copy()->endOfDay();

            if ($terminationDate->lessThan($workEnd)) {
                $workEnd = $terminationDate;
            }
        }

        if ($workStart->greaterThan($workEnd)) {
            return 0;
        }

        if (($context['salary_divisor_mode'] ?? 'fixed_30') === 'calendar') {
            return $workStart->diffInDays($workEnd) + 1;
        }

        return $this->normalizeWorkedDaysToThirtyDayMonth($workStart, $workEnd);
    }

    protected function normalizeWorkedDaysToThirtyDayMonth(
        CarbonInterface $workStart,
        CarbonInterface $workEnd
    ): int {
        $startDay = min((int) $workStart->day, 30);
        $endDay = min((int) $workEnd->day, 30);

        if ($workStart->isSameMonth($workEnd)) {
            return max(0, ($endDay - $startDay) + 1);
        }

        return max(0, $workStart->diffInDays($workEnd) + 1);
    }

    protected function resolveFullWorkedDaysForDisplay(array $period, array $context): int
    {
        if (($context['salary_divisor_mode'] ?? 'fixed_30') === 'calendar') {
            return $period['start']->diffInDays($period['end']) + 1;
        }

        return $this->resolveSalaryDivisorDays($period, $context);
    }

    protected function resolveSalaryDivisorDays(array $period, array $context): int
    {
        if (! empty($context['salary_divisor_days'])) {
            return max(1, (int) $context['salary_divisor_days']);
        }

        $mode = $context['salary_divisor_mode'] ?? 'fixed_30';

        if ($mode === 'calendar') {
            return $period['start']->daysInMonth;
        }

        return 30;
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

    protected function normalizeReferenceNumber(mixed $reference): float
{
    if ($reference === null || $reference === '') {
        return 0;
    }

    if (is_numeric($reference)) {
        return round((float) $reference, 2);
    }

    $value = trim((string) $reference);

    // 🔥 evita erro com "30/30"
    if (str_contains($value, '/')) {
        return 0;
    }

    $value = str_replace('.', '', $value);
    $value = str_replace(',', '.', $value);

    return is_numeric($value) ? round((float) $value, 2) : 0;
}
}