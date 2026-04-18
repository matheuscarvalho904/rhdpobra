<?php

namespace App\Filament\Widgets;

use App\Models\SalaryAdvance;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopWorksSalaryAdvancesTable extends BaseWidget
{
    protected static ?string $heading = 'Adiantamentos por Obra';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SalaryAdvance::query()
                    ->select([
                        'company_id',
                        'work_id',
                        DB::raw('COUNT(*) as total_advances'),
                        DB::raw('SUM(amount) as total_amount'),
                        DB::raw('AVG(amount) as average_amount'),
                    ])
                    ->with(['company', 'work'])
                    ->whereNotNull('work_id')
                    ->groupBy('company_id', 'work_id')
                    ->orderByDesc(DB::raw('SUM(amount)'))
            )
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->placeholder('-'),

                TextColumn::make('work.name')
                    ->label('Obra')
                    ->placeholder('-')
                    ->weight('bold'),

                TextColumn::make('total_advances')
                    ->label('Qtd. Adiantamentos')
                    ->alignCenter(),

                TextColumn::make('total_amount')
                    ->label('Valor Total')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->alignEnd(),

                TextColumn::make('average_amount')
                    ->label('Ticket Médio')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->alignEnd(),
            ])
            ->defaultPaginationPageOption(10);
    }
}