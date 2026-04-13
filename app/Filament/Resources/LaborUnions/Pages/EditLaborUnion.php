<?php

namespace App\Filament\Resources\LaborUnions\Pages;

use App\Filament\Resources\LaborUnions\LaborUnionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLaborUnion extends EditRecord
{
    protected static string $resource = LaborUnionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}