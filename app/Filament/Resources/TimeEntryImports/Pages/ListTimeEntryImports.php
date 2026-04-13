<?php

namespace App\Filament\Resources\TimeEntryImports\Pages;

use App\Filament\Resources\TimeEntryImports\TimeEntryImportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTimeEntryImports extends ListRecords
{
    protected static string $resource = TimeEntryImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}