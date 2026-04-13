<?php

namespace App\Filament\Resources\CboCodes\Pages;

use App\Filament\Resources\CboCodes\CboCodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCboCode extends EditRecord
{
    protected static string $resource = CboCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}