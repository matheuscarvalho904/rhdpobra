<?php

namespace App\Filament\Resources\EmployeeEpiDeliveries;

use App\Filament\Resources\EmployeeEpiDeliveries\Pages\CreateEmployeeEpiDelivery;
use App\Filament\Resources\EmployeeEpiDeliveries\Pages\EditEmployeeEpiDelivery;
use App\Filament\Resources\EmployeeEpiDeliveries\Pages\ListEmployeeEpiDeliveries;
use App\Models\Employee;
use App\Models\EmployeeEpiDelivery;
use App\Models\Epi;
use App\Models\User;
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
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class EmployeeEpiDeliveryResource extends Resource
{
    protected static string $permissionPrefix = 'epis';
    protected static ?string $model = EmployeeEpiDelivery::class;

    protected static ?string $navigationLabel = 'Entregas de EPI';
    protected static ?string $modelLabel = 'Entrega de EPI';
    protected static ?string $pluralModelLabel = 'Entregas de EPI';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|UnitEnum|null $navigationGroup = 'Segurança do Trabalho';
    protected static ?int $navigationSort = 2;

    protected static function user(): ?User
    {
        $user = Auth::user();
        return $user instanceof User ? $user : null;
    }

    public static function canViewAny(): bool
    {
        return static::user()?->can('epis.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::user()?->can('epis.deliver') ?? false;
    }

    public static function canEdit($record): bool
    {
        return static::user()?->can('epis.deliver') ?? false;
    }

    public static function canDelete($record): bool
    {
        return static::user()?->can('epis.delete') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Colaborador')
                ->options(fn () => Employee::query()->orderBy('name')->pluck('name', 'id')->toArray())
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
                        ->numeric()
                        ->default(1)
                        ->required(),

                    DatePicker::make('expected_return_date'),

                    Select::make('status')
                        ->options([
                            'delivered' => 'Entregue',
                            'returned' => 'Devolvido',
                            'lost' => 'Perdido',
                            'replaced' => 'Substituído',
                        ])
                        ->default('delivered'),

                    DatePicker::make('returned_at')
                        ->visible(fn ($get) => $get('status') === 'returned'),

                    Textarea::make('notes')->rows(2),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')->label('Colaborador'),
                TextColumn::make('company.name')->label('Empresa'),
                TextColumn::make('delivery_date')->date('d/m/Y'),
                TextColumn::make('items_count')->counts('items'),
                TextColumn::make('status')->badge(),
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