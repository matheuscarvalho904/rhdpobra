<?php

namespace App\Filament\Resources\TimeBanks\Pages;

use App\Filament\Resources\TimeBanks\TimeBankResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTimeBank extends EditRecord
{
    protected static string $resource = TimeBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
