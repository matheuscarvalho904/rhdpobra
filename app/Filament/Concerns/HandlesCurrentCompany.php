<?php

namespace App\Filament\Concerns;

use App\Support\CurrentCompany;
use Filament\Forms\Components\Hidden;

trait HandlesCurrentCompany
{
    protected static function companyHiddenField(): Hidden
    {
        return Hidden::make('company_id')
            ->default(fn () => CurrentCompany::id())
            ->dehydrated()
            ->required();
    }
}