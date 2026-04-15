<?php

namespace App\Filament\Resources\EmployeeContracts\Tables;

use App\Services\EmployeeNoticeService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')->label('Colaborador')->searchable(),
                TextColumn::make('registration_number')->label('Matrícula')->searchable(),
                TextColumn::make('company.name')->label('Empresa'),
                TextColumn::make('work.name')->label('Obra'),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('admission_date')->label('Admissão')->date('d/m/Y'),
                TextColumn::make('termination_date')->label('Desligamento')->date('d/m/Y'),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('start_notice')
                    ->label('Iniciar Aviso')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->form([
                        DatePicker::make('termination_date')
                            ->label('Data do Desligamento')
                            ->required(),

                        Select::make('notice_type')
                            ->label('Tipo de Aviso')
                            ->options([
                                'worked' => 'Aviso Trabalhado',
                                'indemnified' => 'Aviso Indenizado',
                                'home' => 'Aviso em Casa',
                            ])
                            ->required(),

                        DatePicker::make('notice_start_date')
                            ->label('Início do Aviso'),

                        TextInput::make('notice_days')
                            ->label('Dias de Aviso')
                            ->numeric()
                            ->default(30)
                            ->required(),

                        DatePicker::make('last_worked_date')
                            ->label('Último Dia Trabalhado'),

                        Select::make('reduction_type')
                            ->label('Redução')
                            ->options([
                                'none' => 'Sem redução',
                                '2_hours_daily' => '2 horas diárias',
                                '7_days_final' => '7 dias finais',
                            ])
                            ->default('none'),

                        TextInput::make('dismissal_type')
                            ->label('Tipo de Desligamento'),

                        TextInput::make('termination_reason')
                            ->label('Motivo'),
                    ])
                    ->action(function (array $data, $record, EmployeeNoticeService $service) {
                        try {
                            $service->startNotice($record, $data);

                            Notification::make()
                                ->title('Aviso prévio iniciado com sucesso.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao iniciar aviso.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->status === 'ativo'),
            ]);
    }
}