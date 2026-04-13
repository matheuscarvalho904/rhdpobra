<?php

namespace App\Filament\Resources\EmployeeVariableEvents\Pages;

use App\Filament\Resources\EmployeeVariableEvents\EmployeeVariableEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeVariableEvents extends ListRecords
{
    protected static string $resource = EmployeeVariableEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}