<?php

namespace App\Filament\Resources\AttendanceOccurrences\Pages;

use App\Filament\Resources\AttendanceOccurrences\AttendanceOccurrenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceOccurrences extends ListRecords
{
    protected static string $resource = AttendanceOccurrenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}