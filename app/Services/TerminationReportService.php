<?php

namespace App\Services;

use App\Models\EmployeeTermination;
use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;

class TerminationReportService
{
    public function __construct(
        protected TerminationProcessingService $terminationProcessingService,
    ) {}

    public function generate(EmployeeTermination $termination)
    {
        $termination->loadMissing([
            'employee.company',
            'employee.branch',
            'employee.work',
            'employee.jobRole',
            'contract',
        ]);

        $result = $this->terminationProcessingService->calculate($termination);

        if (empty($result)) {
            throw new RuntimeException('Não foi possível calcular a rescisão para gerar o relatório.');
        }

        $data = [
            'termination' => $termination,
            'employee' => $termination->employee,
            'contract' => $termination->contract,
            'company' => $termination->employee?->company,
            'branch' => $termination->employee?->branch,
            'work' => $termination->employee?->work,
            'jobRole' => $termination->employee?->jobRole,
            'result' => $result,
            'items' => $result['items'] ?? [],
        ];

        return Pdf::loadView('pdf.termination.termination-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
    }

    public function stream(EmployeeTermination $termination)
    {
        return $this->generate($termination)
            ->stream($this->fileName($termination));
    }

    public function download(EmployeeTermination $termination)
    {
        return $this->generate($termination)
            ->download($this->fileName($termination));
    }

    protected function fileName(EmployeeTermination $termination): string
    {
        $employeeName = str($termination->employee?->name ?? 'colaborador')->slug();

        return "rescisao-{$employeeName}-{$termination->id}.pdf";
    }
}