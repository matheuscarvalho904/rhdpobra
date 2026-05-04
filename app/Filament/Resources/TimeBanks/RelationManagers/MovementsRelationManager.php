<?php

namespace App\Filament\Resources\TimeBanks\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Movimentações';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Movimento')
                ->schema([
                    Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'credit' => 'Crédito',
                            'debit' => 'Débito',
                            'adjustment' => 'Ajuste',
                            'payout' => 'Pago em Folha',
                            'expiration' => 'Expiração',
                        ])
                        ->required(),

                    Select::make('origin')
                        ->label('Origem')
                        ->options([
                            'manual' => 'Manual',
                            'time_closing' => 'Fechamento',
                            'payroll' => 'Folha',
                            'expiration' => 'Expiração',
                            'adjustment' => 'Ajuste',
                        ])
                        ->default('manual')
                        ->required(),

                    TextInput::make('hours')
                        ->label('Horas')
                        ->numeric()
                        ->required(),

                    DatePicker::make('movement_date')
                        ->label('Data')
                        ->default(now())
                        ->required(),

                    DatePicker::make('expires_at')
                        ->label('Expira em')
                        ->nullable(),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pendente',
                            'confirmed' => 'Confirmado',
                            'canceled' => 'Cancelado',
                        ])
                        ->default('confirmed')
                        ->required(),
                ])
                ->columns(3),

            Section::make('Detalhes')
                ->schema([
                    Textarea::make('description')
                        ->label('Descrição')
                        ->rows(3)
                        ->columnSpanFull(),

                    KeyValue::make('metadata')
                        ->label('Metadados')
                        ->keyLabel('Chave')
                        ->valueLabel('Valor')
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('movement_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'credit' => 'Crédito',
                        'debit' => 'Débito',
                        'adjustment' => 'Ajuste',
                        'payout' => 'Pago em Folha',
                        'expiration' => 'Expiração',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'credit' => 'success',
                        'debit' => 'danger',
                        'adjustment' => 'warning',
                        'payout' => 'info',
                        'expiration' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('origin')
                    ->label('Origem')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'manual' => 'Manual',
                        'time_closing' => 'Fechamento',
                        'payroll' => 'Folha',
                        'expiration' => 'Expiração',
                        'adjustment' => 'Ajuste',
                        default => $state ?: '-',
                    }),

                TextColumn::make('hours')
                    ->label('Horas')
                    ->suffix('h')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('balance_after')
                    ->label('Saldo Após')
                    ->suffix('h')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'canceled' => 'Cancelado',
                        default => $state ?: '-',
                    }),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50)
                    ->placeholder('-'),
            ])
            ->defaultSort('movement_date', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Novo Movimento')
                    ->mutateDataUsing(function (array $data): array {
                        $bank = $this->getOwnerRecord();

                        $data['company_id'] = $bank->company_id;
                        $data['employee_id'] = $bank->employee_id;
                        $data['time_bank_id'] = $bank->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ]);
    }
}