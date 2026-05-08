<?php

namespace App\Filament\Resources\TimeBanks\Pages;

use App\Filament\Resources\TimeBanks\TimeBankResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListTimeBanks extends ListRecords
{
    protected static string $resource = TimeBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('info')
                ->label('Banco de Horas')
                ->disabled()
                ->color('gray'),
        ];
    }
}