<?php

namespace App\Services;

use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeEpiReportService
{
    public function generate(Employee $employee)
    {
        $employee->load([
            'company',
            'work',
            'jobRole',
            'epiDeliveries.items.epi',
        ]);

        return Pdf::loadView('pdf.epi.employee-report', [
            'employee' => $employee,
            'company' => $employee->company,
            'deliveries' => $employee->epiDeliveries,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);
    }

    public function download(Employee $employee)
    {
        return $this->generate($employee)
            ->download("relatorio-epi-{$employee->id}.pdf");
    }
}