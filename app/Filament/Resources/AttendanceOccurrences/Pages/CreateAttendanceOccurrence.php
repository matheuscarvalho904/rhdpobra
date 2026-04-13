<?php

namespace App\Filament\Resources\AttendanceOccurrences\Pages;

use App\Filament\Resources\AttendanceOccurrences\AttendanceOccurrenceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendanceOccurrence extends CreateRecord
{
    protected static string $resource = AttendanceOccurrenceResource::class;
}