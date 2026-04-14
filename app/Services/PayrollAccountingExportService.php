<?php

namespace App\Services;

use App\Models\PayrollAccountMapping;
use App\Models\PayrollRun;
use App\Models\PayrollRunItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PayrollAccountingExportService
{
    public function exportDetailedCsv(PayrollRun $payrollRun): string
    {
        $items = $this->getExportableItems($payrollRun);

        if ($items->isEmpty()) {
            throw new RuntimeException('Nenhum item encontrado para exportação contábil.');
        }

        $rows = $this->buildDetailedRows($payrollRun, $items);

        if (empty($rows)) {
            throw new RuntimeException('Nenhuma linha contábil foi gerada para a folha selecionada.');
        }

        $directory = 'exports/payroll-accounting';
        Storage::disk('local')->makeDirectory($directory);

        $fileName = sprintf(
            'exportacao-contabil-folha-%d-%s.csv',
            $payrollRun->id,
            now()->format('YmdHis')
        );

        $path = $directory . '/' . $fileName;

        $handle = fopen(Storage::disk('local')->path($path), 'w');

        if ($handle === false) {
            throw new RuntimeException('Não foi possível criar o arquivo CSV da exportação contábil.');
        }

        fputcsv($handle, [
            'competencia',
            'data_pagamento',
            'empresa',
            'filial',
            'obra',
            'centro_custo',
            'matricula',
            'colaborador',
            'cpf',
            'evento_codigo',
            'evento_descricao',
            'tipo',
            'conta_debito',
            'conta_credito',
            'valor',
            'historico',
        ], ';');

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['competencia'],
                $row['data_pagamento'],
                $row['empresa'],
                $row['filial'],
                $row['obra'],
                $row['centro_custo'],
                $row['matricula'],
                $row['colaborador'],
                $row['cpf'],
                $row['evento_codigo'],
                $row['evento_descricao'],
                $row['tipo'],
                $row['conta_debito'],
                $row['conta_credito'],
                number_format((float) $row['valor'], 2, '.', ''),
                $row['historico'],
            ], ';');
        }

        fclose($handle);

        return Storage::disk('local')->path($path);
    }

    protected function getExportableItems(PayrollRun $payrollRun): Collection
    {
        return PayrollRunItem::query()
            ->with([
                'employee.jobRole',
                'employee.costCenter',
                'payrollEvent',
                'payrollRun.company',
                'payrollRun.branch',
                'payrollRun.work',
                'payrollRun.payrollCompetency',
            ])
            ->where('payroll_run_id', $payrollRun->id)
            ->whereIn('type', ['provento', 'desconto', 'informativo'])
            ->where('amount', '>', 0)
            ->orderBy('employee_id')
            ->orderBy('id')
            ->get();
    }

    protected function buildDetailedRows(PayrollRun $payrollRun, Collection $items): array
    {
        $rows = [];

        foreach ($items as $item) {
            $employee = $item->employee;
            $run = $item->payrollRun;

            if (! $employee || ! $run) {
                continue;
            }

            $mapping = $this->resolveMapping($run, $item);

            $rows[] = [
                'competencia' => $this->formatCompetency($run),
                'data_pagamento' => optional($run->payrollCompetency?->payment_date)->format('d/m/Y') ?? '',
                'empresa' => $run->company->name ?? '',
                'filial' => $run->branch->name ?? '',
                'obra' => $run->work->name ?? '',
                'centro_custo' => $employee->costCenter->name ?? '',
                'matricula' => $employee->registration_number ?? $employee->code ?? '',
                'colaborador' => $employee->name ?? '',
                'cpf' => $this->onlyDigits($employee->cpf ?? ''),
                'evento_codigo' => $item->code ?? '',
                'evento_descricao' => $item->description ?? '',
                'tipo' => $item->type ?? '',
                'conta_debito' => $mapping?->debit_account ?? '',
                'conta_credito' => $mapping?->credit_account ?? '',
                'valor' => round((float) $item->amount, 2),
                'historico' => $this->buildHistory($mapping?->history_template, $run, $employee, $item),
            ];
        }

        return $rows;
    }

    protected function resolveMapping(PayrollRun $run, PayrollRunItem $item): ?PayrollAccountMapping
    {
        $query = PayrollAccountMapping::query()
            ->where('is_active', true)
            ->where(function ($q) use ($run) {
                $q->whereNull('company_id')
                    ->orWhere('company_id', $run->company_id);
            })
            ->where(function ($q) use ($run) {
                $q->whereNull('branch_id')
                    ->orWhere('branch_id', $run->branch_id);
            })
            ->where(function ($q) use ($run) {
                $q->whereNull('work_id')
                    ->orWhere('work_id', $run->work_id);
            });

        if ($item->payroll_event_id) {
            $mapping = (clone $query)
                ->where('payroll_event_id', $item->payroll_event_id)
                ->orderByDesc('work_id')
                ->orderByDesc('branch_id')
                ->orderByDesc('company_id')
                ->first();

            if ($mapping) {
                return $mapping;
            }
        }

        if ($item->code) {
            $mapping = (clone $query)
                ->where('event_code', $item->code)
                ->orderByDesc('work_id')
                ->orderByDesc('branch_id')
                ->orderByDesc('company_id')
                ->first();

            if ($mapping) {
                return $mapping;
            }
        }

        if ($item->type) {
            return (clone $query)
                ->where('event_type', $item->type)
                ->orderByDesc('work_id')
                ->orderByDesc('branch_id')
                ->orderByDesc('company_id')
                ->first();
        }

        return null;
    }

    protected function buildHistory(?string $template, PayrollRun $run, mixed $employee, PayrollRunItem $item): string
    {
        $competency = $this->formatCompetency($run);
        $company = $run->company->name ?? '';
        $work = $run->work->name ?? '';
        $employeeName = $employee->name ?? '';
        $eventDescription = $item->description ?? '';

        $template = $template ?: 'Folha {competencia} - {evento} - {colaborador} - {obra}';

        return strtr($template, [
            '{competencia}' => $competency,
            '{empresa}' => $company,
            '{obra}' => $work,
            '{colaborador}' => $employeeName,
            '{evento}' => $eventDescription,
        ]);
    }

    protected function formatCompetency(PayrollRun $run): string
    {
        $month = $run->payrollCompetency?->month;
        $year = $run->payrollCompetency?->year;

        if ($month && $year) {
            return str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '/' . $year;
        }

        return '';
    }

    protected function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}