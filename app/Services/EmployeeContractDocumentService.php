<?php

namespace App\Services;

use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;

class EmployeeContractDocumentService
{
    public function generate(Employee $employee)
    {
        $employee->loadMissing([
            'company',
            'branch',
            'work',
            'jobRole',
            'department',
            'costCenter',
            'workShift',
            'contractType',
        ]);

        if (! $employee->company) {
            throw new RuntimeException('O colaborador não possui empresa vinculada.');
        }

        if (! $employee->admission_date) {
            throw new RuntimeException('O colaborador não possui data de admissão informada.');
        }

        $contractKind = $this->resolveContractKind($employee);

        $template = match ($contractKind) {
            'clt_experience' => 'pdf.contracts.contract-clt-experience',
            'clt' => 'pdf.contracts.contract-clt',
            'pf' => 'pdf.contracts.contract-pf',
            'pj' => 'pdf.contracts.contract-pj',
            'estagio' => 'pdf.contracts.contract-estagio',
            default => throw new RuntimeException('Tipo de contrato não suportado para geração automática.'),
        };

        $data = [
            'employee' => $employee,
            'company' => $employee->company,
            'branch' => $employee->branch,
            'work' => $employee->work,
            'jobRole' => $employee->jobRole,
            'department' => $employee->department,
            'costCenter' => $employee->costCenter,
            'workShift' => $employee->workShift,
            'contractType' => $employee->contractType,
            'contractKind' => $contractKind,
            'today' => now(),
            'cityDate' => $this->resolveCityDate($employee),
        ];

        return Pdf::loadView($template, $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
    }

    public function output(Employee $employee): string
    {
        return $this->generate($employee)->output();
    }

    public function download(Employee $employee)
    {
        return $this->generate($employee)
            ->download($this->suggestFileName($employee));
    }

    public function suggestFileName(Employee $employee): string
    {
        $name = str($employee->name ?: 'colaborador')->slug();
        $kind = $this->resolveContractKind($employee);

        return "contrato-{$kind}-{$name}-{$employee->id}.pdf";
    }

    protected function resolveContractKind(Employee $employee): string
    {
        $name = mb_strtolower(trim((string) ($employee->contractType?->name ?? '')));

        if (str_contains($name, 'clt')) {
            return (bool) $employee->has_experience_period ? 'clt_experience' : 'clt';
        }

        if (
            str_contains($name, 'pessoa física')
            || str_contains($name, 'pessoa fisica')
            || str_contains($name, 'pf')
            || str_contains($name, 'rpa')
            || str_contains($name, 'autônomo')
            || str_contains($name, 'autonomo')
        ) {
            return 'pf';
        }

        if (
            str_contains($name, 'pessoa jurídica')
            || str_contains($name, 'pessoa juridica')
            || str_contains($name, 'pj')
        ) {
            return 'pj';
        }

        if (
            str_contains($name, 'estágio')
            || str_contains($name, 'estagio')
        ) {
            return 'estagio';
        }

        throw new RuntimeException('Não foi possível identificar o modelo contratual pelo tipo de contrato.');
    }

    protected function resolveCityDate(Employee $employee): string
    {
        $city = $employee->company?->city
            ?: $employee->branch?->city
            ?: $employee->city
            ?: 'Aripuanã';

        return "{$city}, " . now()->translatedFormat('d \\d\\e F \\d\\e Y');
    }
}