<?php

namespace App\Filament\Resources\EmployeeTerminations\Pages;

use App\Filament\Resources\EmployeeTerminations\EmployeeTerminationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeTermination extends CreateRecord
{
    protected static string $resource = EmployeeTerminationResource::class;
}
