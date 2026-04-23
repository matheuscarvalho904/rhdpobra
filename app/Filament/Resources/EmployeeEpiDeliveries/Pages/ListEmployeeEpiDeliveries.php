<?php

namespace App\Filament\Resources\EmployeeEpiDeliveries\Pages;

use App\Filament\Resources\EmployeeEpiDeliveries\EmployeeEpiDeliveryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeEpiDeliveries extends ListRecords
{
    protected static string $resource = EmployeeEpiDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova Entrega'),
        ];
    }
}