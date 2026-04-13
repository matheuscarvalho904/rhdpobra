<?php

namespace App\Filament\Resources\EmployeeDependents\Pages;

use App\Filament\Resources\EmployeeDependents\EmployeeDependentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeDependent extends EditRecord
{
    protected static string $resource = EmployeeDependentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}