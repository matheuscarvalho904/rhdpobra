<?php

namespace App\Filament\Resources\PointIntegrations\Pages;

use App\Filament\Resources\PointIntegrations\PointIntegrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPointIntegrations extends ListRecords
{
    protected static string $resource = PointIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova Integração'),
        ];
    }
}