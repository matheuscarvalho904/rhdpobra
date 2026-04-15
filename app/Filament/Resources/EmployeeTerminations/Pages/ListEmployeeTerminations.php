<?php

namespace App\Filament\Resources\EmployeeTerminations\Pages;

use App\Filament\Resources\EmployeeTerminations\EmployeeTerminationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeTerminations extends ListRecords
{
    protected static string $resource = EmployeeTerminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
