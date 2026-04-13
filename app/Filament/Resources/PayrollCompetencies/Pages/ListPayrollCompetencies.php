<?php

namespace App\Filament\Resources\PayrollCompetencies\Pages;

use App\Filament\Resources\PayrollCompetencies\PayrollCompetencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayrollCompetencies extends ListRecords
{
    protected static string $resource = PayrollCompetencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}