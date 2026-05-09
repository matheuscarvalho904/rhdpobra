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
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class TimeBankAdjustment extends Page implements HasForms
{
    use InteractsWithForms;

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

        $this->form->fill([
            'type' => 'credit',
            'movement_date' => now()->toDateString(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar Ajuste')
                ->color('success')
                ->action(function (): void {

                    $data = $this->form->getState();

                    $employee = Employee::findOrFail($data['employee_id']);

                    $service = app(TimeBankService::class);

                    if ($data['type'] === 'credit') {

                        $service->credit(
                            employee: $employee,
                            hours: (float) $data['hours'],
                            description: $data['description'] ?: 'Crédito manual de banco de horas.',
                            metadata: [
                                'source' => 'manual_adjustment',
                            ]
                        );
                    } else {

                        $service->debit(
                            employee: $employee,
                            hours: (float) $data['hours'],
                            description: $data['description'] ?: 'Débito manual de banco de horas.',
                            metadata: [
                                'source' => 'manual_adjustment',
                            ]
                        );
                    }

                    Notification::make()
                        ->title('Ajuste realizado com sucesso.')
                        ->success()
                        ->send();

                    $this->form->fill([
                        'type' => 'credit',
                        'movement_date' => now()->toDateString(),
                    ]);
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
                    ->label('Data')
                    ->required(),

                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(4),
            ]);
    }
}