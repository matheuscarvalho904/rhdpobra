<?php

namespace App\Filament\Resources\EmployeeVariableEvents\Pages;

use App\Filament\Resources\EmployeeVariableEvents\EmployeeVariableEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeVariableEvent extends CreateRecord
{
    protected static string $resource = EmployeeVariableEventResource::class;
}