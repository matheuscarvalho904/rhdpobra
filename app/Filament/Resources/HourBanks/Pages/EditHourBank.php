<?php

namespace App\Filament\Resources\HourBanks\Pages;

use App\Filament\Resources\HourBanks\HourBankResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHourBank extends EditRecord
{
    protected static string $resource = HourBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}