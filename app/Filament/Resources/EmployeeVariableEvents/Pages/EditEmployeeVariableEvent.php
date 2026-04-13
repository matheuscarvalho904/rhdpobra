<?php

namespace App\Filament\Resources\EmployeeVariableEvents\Pages;

use App\Filament\Resources\EmployeeVariableEvents\EmployeeVariableEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeVariableEvent extends EditRecord
{
    protected static string $resource = EmployeeVariableEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}