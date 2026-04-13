<?php

namespace App\Filament\Resources\TimeClosings\Pages;

use App\Filament\Resources\TimeClosings\TimeClosingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTimeClosing extends EditRecord
{
    protected static string $resource = TimeClosingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}