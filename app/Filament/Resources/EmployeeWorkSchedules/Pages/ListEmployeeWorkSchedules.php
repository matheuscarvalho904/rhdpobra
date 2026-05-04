<?php

namespace App\Filament\Resources\EmployeeWorkSchedules\Pages;

use App\Filament\Resources\EmployeeWorkSchedules\EmployeeWorkScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeWorkSchedules extends ListRecords
{
    protected static string $resource = EmployeeWorkScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova Jornada do Colaborador'),
        ];
    }
}