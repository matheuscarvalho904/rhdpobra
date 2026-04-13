<?php

namespace App\Filament\Resources\EmployeeDependents\Pages;

use App\Filament\Resources\EmployeeDependents\EmployeeDependentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeDependent extends CreateRecord
{
    protected static string $resource = EmployeeDependentResource::class;
}