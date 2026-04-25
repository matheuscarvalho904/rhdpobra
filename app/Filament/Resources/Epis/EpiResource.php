<?php

namespace App\Filament\Resources\Epis;

use App\Filament\Resources\Epis\Pages\CreateEpi;
use App\Filament\Resources\Epis\Pages\EditEpi;
use App\Filament\Resources\Epis\Pages\ListEpis;
use App\Models\Company;
use App\Models\Epi;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class EpiResource extends Resource
{
    protected static ?string $model = Epi::class;

    protected static ?string $navigationLabel = 'EPIs';
    protected static ?string $modelLabel = 'EPI';
    protected static ?string $pluralModelLabel = 'EPIs';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|UnitEnum|null $navigationGroup = 'Segurança do Trabalho';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')
                ->label('Empresa')
                ->options(fn () => Company::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->required()
                ->native(false),

            TextInput::make('name')
                ->label('Nome do EPI')
                ->required()
                ->maxLength(255),

            TextInput::make('code')
                ->label('Código')
                ->maxLength(50),

            TextInput::make('ca_number')
                ->label('Número do CA')
                ->maxLength(100),

            TextInput::make('validity_days')
                ->label('Validade (dias)')
                ->numeric()
                ->minValue(0),

            Toggle::make('requires_return')
                ->label('Exige devolução')
                ->default(false),

            Toggle::make('is_active')
                ->label('Ativo')
                ->default(true),

            Textarea::make('notes')
                ->label('Observações')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('ca_number')
                    ->label('CA')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('validity_days')
                    ->label('Validade (dias)')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('requires_return')
                    ->label('Devolução')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEpis::route('/'),
            'create' => CreateEpi::route('/create'),
            'edit' => EditEpi::route('/{record}/edit'),
        ];
    }
}