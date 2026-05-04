<?php

namespace App\Filament\Resources\EmployeeWorkSchedules\Pages;

use App\Filament\Resources\EmployeeWorkSchedules\EmployeeWorkScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeWorkSchedule extends EditRecord
{
    protected static string $resource = EmployeeWorkScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Excluir'),
        ];
    }
}