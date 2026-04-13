<?php

namespace App\Filament\Resources\TimeEntryImports\Pages;

use App\Filament\Resources\TimeEntryImports\TimeEntryImportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTimeEntryImport extends EditRecord
{
    protected static string $resource = TimeEntryImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}