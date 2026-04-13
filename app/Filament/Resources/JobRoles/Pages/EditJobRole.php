<?php

namespace App\Filament\Resources\JobRoles\Pages;

use App\Filament\Resources\JobRoles\JobRoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJobRole extends EditRecord
{
    protected static string $resource = JobRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}