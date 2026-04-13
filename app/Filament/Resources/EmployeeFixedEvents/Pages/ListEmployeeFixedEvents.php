<?php

namespace App\Filament\Resources\EmployeeFixedEvents\Pages;

use App\Filament\Resources\EmployeeFixedEvents\EmployeeFixedEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeFixedEvents extends ListRecords
{
    protected static string $resource = EmployeeFixedEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}