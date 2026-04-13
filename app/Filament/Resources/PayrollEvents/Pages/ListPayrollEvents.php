<?php

namespace App\Filament\Resources\PayrollEvents\Pages;

use App\Filament\Resources\PayrollEvents\PayrollEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayrollEvents extends ListRecords
{
    protected static string $resource = PayrollEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}