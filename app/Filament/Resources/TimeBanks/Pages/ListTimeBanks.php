<?php

namespace App\Filament\Resources\TimeBanks\Pages;

use App\Filament\Resources\TimeBanks\TimeBankResource;
use Filament\Resources\Pages\ListRecords;

class ListTimeBanks extends ListRecords
{
    protected static string $resource = TimeBankResource::class;
}