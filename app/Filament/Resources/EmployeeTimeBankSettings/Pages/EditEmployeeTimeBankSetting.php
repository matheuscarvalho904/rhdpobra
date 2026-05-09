<?php

namespace App\Filament\Resources\EmployeeTimeBankSettings\Pages;

use App\Filament\Resources\EmployeeTimeBankSettings\EmployeeTimeBankSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeTimeBankSetting extends EditRecord
{
    protected static string $resource = EmployeeTimeBankSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}