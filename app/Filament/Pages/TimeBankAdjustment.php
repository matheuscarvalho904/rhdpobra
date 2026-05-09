<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Services\TimeBankService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class TimeBankAdjustment extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string|UnitEnum|null $navigationGroup = 'Ponto e Jornada';

    protected static ?string $navigationLabel = 'Ajuste Banco de Horas';

    protected static ?string $title = 'Ajuste Manual de Banco de Horas';

    protected string $view = 'filament.pages.time-bank-adjustment';

    public ?int $employee_id = null;

    public ?string $type = 'credit';

    public ?string $hours = null;

    public ?string $movement_date = null;

    public ?string $description = null;

    public function mount(): void
    {
        $this->movement_date = now()->toDateString();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar Ajuste')
                ->color('success')
                ->action(function (): void {

                    $this->validate([
                        'employee_id' => ['required'],
                        'type' => ['required'],
                        'hours' => ['required', 'numeric', 'min:0.01'],
                    ]);

                    $employee = Employee::findOrFail($this->employee_id);

                    $service = app(TimeBankService::class);

                    if ($this->type === 'credit') {

                        $service->credit(
                            employee: $employee,
                            hours: (float) $this->hours,
                            description: $this->description ?: 'Crédito manual de banco de horas.',
                            metadata: [
                                'source' => 'manual_adjustment',
                            ]
                        );
                    } else {

                        $service->debit(
                            employee: $employee,
                            hours: (float) $this->hours,
                            description: $this->description ?: 'Débito manual de banco de horas.',
                            metadata: [
                                'source' => 'manual_adjustment',
                            ]
                        );
                    }

                    Notification::make()
                        ->title('Ajuste realizado com sucesso.')
                        ->success()
                        ->send();

                    $this->reset([
                        'employee_id',
                        'hours',
                        'description',
                    ]);

                    $this->type = 'credit';

                    $this->movement_date = now()->toDateString();
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Colaborador')
                    ->options(fn (): array => Employee::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->searchable()
                    ->required(),

                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'credit' => 'Crédito',
                        'debit' => 'Débito',
                    ])
                    ->required(),

                TextInput::make('hours')
                    ->label('Horas')
                    ->numeric()
                    ->required()
                    ->suffix(' h'),

                DatePicker::make('movement_date')
                    ->label('Data'),

                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(4),
            ]);
    }
}