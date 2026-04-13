<?php

namespace App\Filament\Resources\TimeEntryImports\Pages;

use App\Filament\Resources\TimeEntryImports\TimeEntryImportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTimeEntryImport extends CreateRecord
{
    protected static string $resource = TimeEntryImportResource::class;
}