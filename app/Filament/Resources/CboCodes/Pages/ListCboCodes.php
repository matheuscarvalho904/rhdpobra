<?php

namespace App\Filament\Resources\CboCodes\Pages;

use App\Filament\Resources\CboCodes\CboCodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCboCodes extends ListRecords
{
    protected static string $resource = CboCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}