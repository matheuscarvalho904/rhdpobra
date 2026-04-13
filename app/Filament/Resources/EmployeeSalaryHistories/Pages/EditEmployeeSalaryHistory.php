<?php

namespace App\Filament\Resources\EmployeeSalaryHistories\Pages;

use App\Filament\Resources\EmployeeSalaryHistories\EmployeeSalaryHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeSalaryHistory extends EditRecord
{
    protected static string $resource = EmployeeSalaryHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}