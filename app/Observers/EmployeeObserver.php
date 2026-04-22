<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmployeeFile;
use App\Services\EmployeeContractDocumentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeObserver
{
    public function created(Employee $employee): void
    {
        $this->generateContractAutomatically($employee);
    }

    protected function generateContractAutomatically(Employee $employee): void
    {
        try {
            $employee->loadMissing('contractType');

            if (! $employee->contractType) {
                return;
            }

            $service = app(EmployeeContractDocumentService::class);

            $pdfContent = $service->output($employee);

            $fileName = $service->suggestFileName($employee);
            $filePath = 'employees/contracts/' . $fileName;

            Storage::disk('public')->put($filePath, $pdfContent);

            EmployeeFile::create([
                'employee_id' => $employee->id,
                'type' => 'contrato',
                'file_name' => $fileName,
                'file_path' => $filePath,
                'generated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar contrato automático do colaborador.', [
                'employee_id' => $employee->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}