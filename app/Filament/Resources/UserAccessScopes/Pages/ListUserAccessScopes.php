<?php

namespace App\Filament\Resources\UserAccessScopes\Pages;

use App\Filament\Resources\UserAccessScopes\UserAccessScopeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserAccessScopes extends ListRecords
{
    protected static string $resource = UserAccessScopeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}