<?php

namespace App\Filament\Resources\TimeEntries\Pages;

use App\Filament\Resources\TimeEntries\TimeEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTimeEntries extends ListRecords
{
    protected static string $resource = TimeEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova Marcação'),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $tableFilters = [];

        if (request()->filled('start_date') || request()->filled('end_date')) {
            $tableFilters['periodo'] = [
                'start_date' => request()->get('start_date'),
                'end_date' => request()->get('end_date'),
            ];
        }

        if (! empty($tableFilters)) {
            $this->tableFilters = $tableFilters;
        }
    }
}