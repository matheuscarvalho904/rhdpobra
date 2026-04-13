<?php

namespace App\Filament\Resources\AttendanceOccurrences\Pages;

use App\Filament\Resources\AttendanceOccurrences\AttendanceOccurrenceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceOccurrence extends EditRecord
{
    protected static string $resource = AttendanceOccurrenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}