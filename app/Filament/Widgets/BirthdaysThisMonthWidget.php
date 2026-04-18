<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BirthdaysThisMonthWidget extends BaseWidget
{
    protected static ?string $heading = 'Aniversariantes do Mês';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('birth_date')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Colaborador')
                    ->searchable(),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Nascimento')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-'),

                Tables\Columns\TextColumn::make('birthday_day')
                    ->label('Dia')
                    ->state(fn (Employee $record) => $record->birth_date ? Carbon::parse($record->birth_date)->format('d') : '-')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('birthday_today')
                ->label('Hoje?')
                ->state(function (Employee $record) {
                    if (! $record->birth_date) {
                        return '-';
                    }

                    $birthDate = Carbon::parse($record->birth_date);

                    return $birthDate->day === now()->day && $birthDate->month === now()->month
                        ? '🎉 Hoje'
                        : '-';
                }),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Filial')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('work.name')
                    ->label('Obra')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('jobRole.name')
                    ->label('Cargo')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('Celular')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Nenhum aniversariante neste mês.')
            ->emptyStateDescription('Quando houver colaboradores com aniversário no mês atual, eles aparecerão aqui.');
    }

    protected function getTableQuery(): Builder
    {
        $month = now()->month;

        return Employee::query()
            ->with(['company', 'branch', 'work', 'jobRole'])
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $month)
            ->orderByRaw('DAY(birth_date) asc');
    }
}