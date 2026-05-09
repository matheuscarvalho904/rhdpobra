<?php

namespace App\Filament\Resources\CompanyTimeBankSettings\Pages;

use App\Filament\Resources\CompanyTimeBankSettings\CompanyTimeBankSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompanyTimeBankSetting extends EditRecord
{
    protected static string $resource = CompanyTimeBankSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}