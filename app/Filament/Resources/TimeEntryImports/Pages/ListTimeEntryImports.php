<?php

namespace App\Filament\Resources\TimeEntryImports\Pages;

use App\Filament\Resources\TimeEntryImports\TimeEntryImportResource;
use Filament\Resources\Pages\ListRecords;

class ListTimeEntryImports extends ListRecords
{
    protected static string $resource = TimeEntryImportResource::class;
}