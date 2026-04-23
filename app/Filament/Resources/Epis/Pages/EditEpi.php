<?php

namespace App\Filament\Resources\Epis\Pages;

use App\Filament\Resources\Epis\EpiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEpi extends EditRecord
{
    protected static string $resource = EpiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Excluir'),
        ];
    }
}