<?php

namespace App\Filament\Resources\EmployeeDependents\Pages;

use App\Filament\Resources\EmployeeDependents\EmployeeDependentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeDependents extends ListRecords
{
    protected static string $resource = EmployeeDependentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}