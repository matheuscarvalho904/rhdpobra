<?php

namespace App\Filament\Resources\PayrollEvents\Pages;

use App\Filament\Resources\PayrollEvents\PayrollEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPayrollEvent extends EditRecord
{
    protected static string $resource = PayrollEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}