<?php

namespace App\Filament\Resources\UserAccessScopes\Pages;

use App\Filament\Resources\UserAccessScopes\UserAccessScopeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserAccessScope extends EditRecord
{
    protected static string $resource = UserAccessScopeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
