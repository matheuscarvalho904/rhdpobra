<?php

namespace App\Filament\Resources\EmployeeTimeBankSettings\Pages;

use App\Filament\Resources\EmployeeTimeBankSettings\EmployeeTimeBankSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeTimeBankSettings extends ListRecords
{
    protected static string $resource = EmployeeTimeBankSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova Configuração'),
        ];
    }
}