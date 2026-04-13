<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentEmployeesTable extends BaseWidget
{
    protected static ?string $heading = 'Últimos Colaboradores Admitidos';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->latest('admission_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Matrícula'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Colaborador')
                    ->searchable(),

                Tables\Columns\TextColumn::make('work.name')
                    ->label('Obra')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('jobRole.name')
                    ->label('Cargo')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('admission_date')
                    ->label('Admissão')
                    ->date('d/m/Y'),
            ])
            ->defaultPaginationPageOption(5);
    }
}