<?php

namespace App\Filament\Resources\EmployeeFixedEvents\Pages;

use App\Filament\Resources\EmployeeFixedEvents\EmployeeFixedEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeFixedEvent extends CreateRecord
{
    protected static string $resource = EmployeeFixedEventResource::class;
}