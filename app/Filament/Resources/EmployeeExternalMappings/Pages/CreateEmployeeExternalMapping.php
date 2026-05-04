<?php

namespace App\Filament\Resources\EmployeeExternalMappings\Pages;

use App\Filament\Resources\EmployeeExternalMappings\EmployeeExternalMappingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeExternalMapping extends CreateRecord
{
    protected static string $resource = EmployeeExternalMappingResource::class;
}