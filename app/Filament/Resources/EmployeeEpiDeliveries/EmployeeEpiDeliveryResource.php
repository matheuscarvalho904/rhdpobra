<?php

namespace App\Filament\Resources\EmployeeEpiDeliveries;

use App\Filament\Resources\EmployeeEpiDeliveries\Pages\CreateEmployeeEpiDelivery;
use App\Filament\Resources\EmployeeEpiDeliveries\Pages\EditEmployeeEpiDelivery;
use App\Filament\Resources\EmployeeEpiDeliveries\Pages\ListEmployeeEpiDeliveries;
use App\Models\Employee;
use App\Models\EmployeeEpiDelivery;
use App\Models\Epi;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeEpiDeliveryResource extends Resource
{
    protected static ?string $model = EmployeeEpiDelivery::class;

    protected static ?string $navigationLabel = 'Entregas de EPI';
    protected static ?string $modelLabel = 'Entrega de EPI';
    protected static ?string $pluralModelLabel = 'Entregas de EPI';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|UnitEnum|null $navigationGroup = 'Segurança do Trabalho';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Colaborador')
                ->options(fn () => Employee::query()
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray())
                ->searchable()
                ->preload()
                ->required()
                ->native(false),

            DatePicker::make('delivery_date')
                ->label('Data da Entrega')
                ->required()
                ->default(now()),

            Select::make('status')
                ->label('Status')
                ->options([
                    'open' => 'Aberta',
                    'closed' => 'Fechada',
                ])
                ->default('open')
                ->required()
                ->native(false),

            Textarea::make('notes')
                ->label('Observações da Entrega')
                ->rows(3)
                ->columnSpanFull(),

            Repeater::make('items')
                ->label('Itens da Entrega')
                ->relationship()
                ->defaultItems(1)
                ->reorderable(false)
                ->schema([
                    Select::make('epi_id')
                        ->label('EPI')
                        ->options(fn () => Epi::query()
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),

                    TextInput::make('quantity')
                        ->label('Quantidade')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->minValue(1),

                    DatePicker::make('expected_return_date')
                        ->label('Prev. Devolução'),

                    Select::make('status')
                        ->label('Status do Item')
                        ->options([
                            'delivered' => 'Entregue',
                            'returned' => 'Devolvido',
                            'lost' => 'Perdido',
                            'replaced' => 'Substituído',
                        ])
                        ->default('delivered')
                        ->required()
                        ->native(false),

                    DatePicker::make('returned_at')
                        ->label('Data de Devolução')
                        ->visible(fn ($get) => $get('status') === 'returned'),

                    Textarea::make('notes')
                        ->label('Observações do Item')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('delivery_date')
                    ->label('Entrega')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Itens')
                    ->counts('items')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'open' => 'Aberta',
                        'closed' => 'Fechada',
                        default => $state,
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'open' => 'warning',
                        'closed' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('term_file_name')
                    ->label('Termo')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('delivery_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeEpiDeliveries::route('/'),
            'create' => CreateEmployeeEpiDelivery::route('/create'),
            'edit' => EditEmployeeEpiDelivery::route('/{record}/edit'),
        ];
    }
}