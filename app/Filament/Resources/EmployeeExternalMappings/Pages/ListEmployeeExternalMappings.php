<?php

namespace App\Filament\Resources\EmployeeExternalMappings\Pages;

use App\Filament\Resources\EmployeeExternalMappings\EmployeeExternalMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeExternalMappings extends ListRecords
{
    protected static string $resource = EmployeeExternalMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo Vínculo'),
        ];
    }
}