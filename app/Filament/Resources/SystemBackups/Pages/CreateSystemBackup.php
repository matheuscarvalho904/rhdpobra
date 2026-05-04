<?php

namespace App\Filament\Resources\SystemBackups\Pages;

use App\Filament\Resources\SystemBackups\SystemBackupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSystemBackup extends CreateRecord
{
    protected static string $resource = SystemBackupResource::class;
}