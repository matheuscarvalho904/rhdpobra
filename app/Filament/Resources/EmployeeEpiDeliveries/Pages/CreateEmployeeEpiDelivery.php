<?php

namespace App\Filament\Resources\EmployeeEpiDeliveries\Pages;

use App\Filament\Resources\EmployeeEpiDeliveries\EmployeeEpiDeliveryResource;
use App\Models\Employee;
use Filament\Resources\Pages\CreateRecord;
use RuntimeException;

class CreateEmployeeEpiDelivery extends CreateRecord
{
    protected static string $resource = EmployeeEpiDeliveryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['employee_id'])) {
            throw new RuntimeException('Selecione um colaborador para gerar a entrega de EPI.');
        }

        $employee = Employee::query()
            ->select(['id', 'company_id'])
            ->find($data['employee_id']);

        if (! $employee) {
            throw new RuntimeException('Colaborador não encontrado.');
        }

        if (empty($employee->company_id)) {
            throw new RuntimeException('O colaborador selecionado não possui empresa vinculada.');
        }

        $data['company_id'] = $employee->company_id;

        return $data;
    }
}