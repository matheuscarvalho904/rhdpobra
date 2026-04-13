<?php

namespace App\Filament\Resources\EmployeeFixedEvents\Pages;

use App\Filament\Resources\EmployeeFixedEvents\EmployeeFixedEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeFixedEvent extends EditRecord
{
    protected static string $resource = EmployeeFixedEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}