<?php

namespace App\Filament\Resources\PointIntegrations\Pages;

use App\Filament\Resources\PointIntegrations\PointIntegrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPointIntegration extends EditRecord
{
    protected static string $resource = PointIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Excluir'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}