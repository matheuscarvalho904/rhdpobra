<?php

namespace App\Services;

use App\Models\EmployeeEpiDelivery;
use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;

class EmployeeEpiTermService
{
    public function generate(EmployeeEpiDelivery $delivery)
    {
        $delivery->loadMissing([
            'employee.company',
            'employee.branch',
            'employee.work',
            'employee.jobRole',
            'company',
            'items.epi',
        ]);

        if (! $delivery->employee) {
            throw new RuntimeException('Entrega de EPI sem colaborador vinculado.');
        }

        if ($delivery->items->isEmpty()) {
            throw new RuntimeException('Entrega de EPI sem itens vinculados.');
        }

        $employee = $delivery->employee;
        $company = $delivery->company ?: $employee->company;
        $work = $employee->work;
        $jobRole = $employee->jobRole;

        return Pdf::loadView('pdf.epi.term', [
            'delivery' => $delivery,
            'employee' => $employee,
            'company' => $company,
            'work' => $work,
            'jobRole' => $jobRole,
            'items' => $delivery->items,
            'today' => now(),
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
    }

    public function output(EmployeeEpiDelivery $delivery): string
    {
        return $this->generate($delivery)->output();
    }

    public function download(EmployeeEpiDelivery $delivery)
    {
        return $this->generate($delivery)
            ->download($this->suggestFileName($delivery));
    }

    public function suggestFileName(EmployeeEpiDelivery $delivery): string
    {
        $employeeName = str($delivery->employee?->name ?? 'colaborador')->slug();
        $deliveryId = $delivery->id ?? 'novo';

        return "termo-epi-{$employeeName}-entrega-{$deliveryId}.pdf";
    }
}