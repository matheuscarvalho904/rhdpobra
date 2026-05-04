<?php

namespace App\Filament\Resources\TimePayrollEventMappings\Pages;

use App\Filament\Resources\TimePayrollEventMappings\TimePayrollEventMappingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTimePayrollEventMapping extends EditRecord
{
    protected static string $resource = TimePayrollEventMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}