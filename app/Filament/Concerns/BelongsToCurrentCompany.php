<?php

namespace App\Filament\Concerns;

use App\Support\CurrentCompany;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToCurrentCompany
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $companyId = CurrentCompany::id();

        if (! $companyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('company_id', $companyId);
    }
}