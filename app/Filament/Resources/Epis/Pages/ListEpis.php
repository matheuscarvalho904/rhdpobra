<?php

namespace App\Filament\Resources\Epis\Pages;

use App\Filament\Resources\Epis\EpiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEpis extends ListRecords
{
    protected static string $resource = EpiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo EPI'),
        ];
    }
}