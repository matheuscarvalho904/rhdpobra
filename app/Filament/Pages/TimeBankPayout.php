<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\PayrollCompetency;
use App\Models\TimeBank;
use App\Services\TimeBankPayoutService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Throwable;
use UnitEnum;

class TimeBankPayout extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Ponto e Jornada';

    protected static ?string $navigationLabel = 'Pagamento Banco';

    protected static ?string $title = 'Pagamento de Banco de Horas';

    protected static ?int $navigationSort = 43;

    protected string $view = 'filament.pages.time-bank-payout';

    public ?int $employee_id = null;

    public ?int $payroll_competency_id = null;

    public ?string $hours = null;

    public ?string $description = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pay')
                ->label('Pagar Banco')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Confirmar pagamento do banco de horas')
                ->modalDescription('Essa ação vai baixar o saldo do banco de horas e gerar um evento variável na folha.')
                ->action(function (): void {

                    $data = $this->form->getState();

                    try {

                        $employee = Employee::findOrFail($data['employee_id']);

                        $competency = PayrollCompetency::findOrFail(
                            $data['payroll_competency_id']
                        );

                        app(TimeBankPayoutService::class)->payout(
                            employee: $employee,
                            competency: $competency,
                            hours: (float) $data['hours'],
                            description: $data['description'] ?: null,
                        );

                        Notification::make()
                            ->title('Pagamento lançado com sucesso.')
                            ->body('O saldo foi baixado e o evento foi enviado para a folha.')
                            ->success()
                            ->send();

                        $this->form->fill();

                    } catch (Throwable $e) {

                        Notification::make()
                            ->title('Erro ao pagar banco de horas')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
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
                    ->required()
                    ->live(),

                Select::make('payroll_competency_id')
                    ->label('Competência da Folha')
                    ->options(fn (): array => PayrollCompetency::query()
                        ->orderByDesc('year')
                        ->orderByDesc('month')
                        ->get()
                        ->mapWithKeys(fn ($competency) => [
                            $competency->id => (
                                $competency->display_name
                                ?? "{$competency->month}/{$competency->year}"
                            ),
                        ])
                        ->toArray())
                    ->searchable()
                    ->required(),

                TextInput::make('current_balance')
                    ->label('Saldo Atual')
                    ->disabled()
                    ->dehydrated(false)
                    ->suffix(' h')
                    ->formatStateUsing(function ($state, $get) {

                        $employeeId = $get('employee_id');

                        if (! $employeeId) {
                            return '0,00';
                        }

                        $balance = TimeBank::query()
                            ->where('employee_id', $employeeId)
                            ->value('net_balance_hours');

                        return number_format(
                            (float) $balance,
                            2,
                            ',',
                            '.'
                        );
                    }),

                TextInput::make('hours')
                    ->label('Horas a pagar')
                    ->numeric()
                    ->required()
                    ->suffix(' h')
                    ->minValue(0.01),

                Textarea::make('description')
                    ->label('Observação')
                    ->rows(4)
                    ->placeholder('Ex: Pagamento parcial do saldo de banco de horas.'),
            ]);
    }
}