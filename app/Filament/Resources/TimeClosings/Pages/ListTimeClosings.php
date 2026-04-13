<?php

namespace App\Filament\Resources\TimeClosings\Pages;

use App\Filament\Resources\TimeClosings\TimeClosingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTimeClosings extends ListRecords
{
    protected static string $resource = TimeClosingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}