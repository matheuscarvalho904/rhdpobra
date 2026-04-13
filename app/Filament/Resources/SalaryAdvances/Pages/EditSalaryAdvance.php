<?php

namespace App\Filament\Resources\SalaryAdvances\Pages;

use App\Filament\Resources\SalaryAdvances\SalaryAdvanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalaryAdvance extends EditRecord
{
    protected static string $resource = SalaryAdvanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}