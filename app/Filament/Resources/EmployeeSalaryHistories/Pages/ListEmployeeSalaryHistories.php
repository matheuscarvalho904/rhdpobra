<?php

namespace App\Filament\Resources\EmployeeSalaryHistories\Pages;

use App\Filament\Resources\EmployeeSalaryHistories\EmployeeSalaryHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeSalaryHistories extends ListRecords
{
    protected static string $resource = EmployeeSalaryHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}