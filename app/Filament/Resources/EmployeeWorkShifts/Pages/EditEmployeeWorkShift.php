<?php

namespace App\Filament\Resources\EmployeeWorkShifts\Pages;

use App\Filament\Resources\EmployeeWorkShifts\EmployeeWorkShiftResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeWorkShift extends EditRecord
{
    protected static string $resource = EmployeeWorkShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}