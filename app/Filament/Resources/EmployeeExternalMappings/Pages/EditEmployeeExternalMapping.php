<?php

namespace App\Filament\Resources\EmployeeExternalMappings\Pages;

use App\Filament\Resources\EmployeeExternalMappings\EmployeeExternalMappingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeExternalMapping extends EditRecord
{
    protected static string $resource = EmployeeExternalMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Excluir'),
        ];
    }
}