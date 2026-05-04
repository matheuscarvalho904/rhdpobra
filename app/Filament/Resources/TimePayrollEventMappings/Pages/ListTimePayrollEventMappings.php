<?php

namespace App\Filament\Resources\TimePayrollEventMappings\Pages;

use App\Filament\Resources\TimePayrollEventMappings\TimePayrollEventMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTimePayrollEventMappings extends ListRecords
{
    protected static string $resource = TimePayrollEventMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Novo Mapeamento'),
        ];
    }
}