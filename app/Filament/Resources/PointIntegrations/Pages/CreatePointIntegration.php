<?php

namespace App\Filament\Resources\PointIntegrations\Pages;

use App\Filament\Resources\PointIntegrations\PointIntegrationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePointIntegration extends CreateRecord
{
    protected static string $resource = PointIntegrationResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}