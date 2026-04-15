<?php

namespace App\Filament\Resources\EmployeeTerminations\Pages;

use App\Filament\Resources\EmployeeTerminations\EmployeeTerminationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeTermination extends EditRecord
{
    protected static string $resource = EmployeeTerminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
