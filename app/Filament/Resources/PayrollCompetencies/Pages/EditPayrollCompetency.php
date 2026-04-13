<?php

namespace App\Filament\Resources\PayrollCompetencies\Pages;

use App\Filament\Resources\PayrollCompetencies\PayrollCompetencyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPayrollCompetency extends EditRecord
{
    protected static string $resource = PayrollCompetencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}