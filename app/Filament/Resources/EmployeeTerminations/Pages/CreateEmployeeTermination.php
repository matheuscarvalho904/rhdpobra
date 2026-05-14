<?php

namespace App\Filament\Resources\EmployeeTerminations\Pages;

use App\Filament\Resources\EmployeeTerminations\EmployeeTerminationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeTermination extends CreateRecord
{
    protected static string $resource = EmployeeTerminationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['notice_amount'] = isset($data['notice_amount'])
            ? (float) $data['notice_amount']
            : 0;

        $data['termination_amount'] = isset($data['termination_amount'])
            ? (float) $data['termination_amount']
            : 0;

        $data['is_notice_projected'] = isset($data['is_notice_projected'])
            ? (bool) $data['is_notice_projected']
            : true;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->applyTerminationStatus();
    }

    protected function applyTerminationStatus(): void
    {
        $termination = $this->record;

        if ($termination->status !== 'closed') {
            return;
        }

        $termination->loadMissing([
            'employee',
            'contract',
        ]);

        $terminationDate = $termination->termination_date;

        $finalDate = $termination->notice_type === 'worked'
            ? ($termination->notice_end_date ?: $termination->projected_end_date ?: $terminationDate)
            : $terminationDate;

        $isWorkedNotice = $termination->notice_type === 'worked';

        if ($termination->employee) {
            $termination->employee->update([
                'status' => $isWorkedNotice ? 'leave' : 'terminated',
                'is_active' => $isWorkedNotice,
                'termination_date' => $finalDate,
            ]);
        }

        if ($termination->contract) {
            $termination->contract->update([
                'status' => $isWorkedNotice ? 'em_aviso' : 'desligado',
                'is_active' => $isWorkedNotice,
                'is_current' => $isWorkedNotice,
                'termination_date' => $finalDate,
                'termination_reason' => $termination->termination_reason,
            ]);
        }

        if (! $termination->closed_at) {
            $termination->update([
                'closed_at' => now(),
            ]);
        }
    }
}