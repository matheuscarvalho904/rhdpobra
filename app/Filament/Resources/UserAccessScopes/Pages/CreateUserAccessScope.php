<?php

namespace App\Filament\Resources\UserAccessScopes\Pages;

use App\Filament\Resources\UserAccessScopes\UserAccessScopeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserAccessScope extends CreateRecord
{
    protected static string $resource = UserAccessScopeResource::class;
}