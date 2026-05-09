<?php

namespace App\Filament\Resources\CompanyTimeBankSettings\Pages;

use App\Filament\Resources\CompanyTimeBankSettings\CompanyTimeBankSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyTimeBankSettings extends ListRecords
{
    protected static string $resource = CompanyTimeBankSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova Configuração'),
        ];
    }
}