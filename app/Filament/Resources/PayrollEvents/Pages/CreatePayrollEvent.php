<?php

namespace App\Filament\Resources\PayrollEvents\Pages;

use App\Filament\Resources\PayrollEvents\PayrollEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayrollEvent extends CreateRecord
{
    protected static string $resource = PayrollEventResource::class;
}