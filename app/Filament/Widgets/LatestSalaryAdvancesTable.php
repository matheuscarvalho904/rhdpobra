<?php

namespace App\Filament\Widgets;

use App\Models\SalaryAdvance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestSalaryAdvancesTable extends BaseWidget
{
    protected static ?string $heading = 'Últimos Adiantamentos';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable(),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('work.name')
                    ->label('Obra')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('advance_date')
                    ->label('Data')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'draft' => 'Rascunho',
                        'paid' => 'Pago',
                        'canceled' => 'Cancelado',
                        'integrated_payroll' => 'Integrado na Folha',
                        default => $state ?: '-',
                    }),
            ])
            ->defaultPaginationPageOption(10);
    }

    protected function getTableQuery(): Builder
    {
        return SalaryAdvance::query()
            ->with(['employee', 'company', 'work'])
            ->latest('advance_date')
            ->limit(10);
    }
}