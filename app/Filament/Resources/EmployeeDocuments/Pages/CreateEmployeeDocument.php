<?php

namespace App\Filament\Resources\EmployeeDocuments\Pages;

use App\Filament\Resources\EmployeeDocuments\EmployeeDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeDocument extends CreateRecord
{
    protected static string $resource = EmployeeDocumentResource::class;
}