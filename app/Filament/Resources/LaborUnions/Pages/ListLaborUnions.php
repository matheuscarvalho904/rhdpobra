<?php

namespace App\Filament\Resources\LaborUnions\Pages;

use App\Filament\Resources\LaborUnions\LaborUnionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLaborUnions extends ListRecords
{
    protected static string $resource = LaborUnionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}