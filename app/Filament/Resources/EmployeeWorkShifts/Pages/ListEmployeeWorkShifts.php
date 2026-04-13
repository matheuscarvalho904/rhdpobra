<?php

namespace App\Filament\Resources\EmployeeWorkShifts\Pages;

use App\Filament\Resources\EmployeeWorkShifts\EmployeeWorkShiftResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeWorkShifts extends ListRecords
{
    protected static string $resource = EmployeeWorkShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}