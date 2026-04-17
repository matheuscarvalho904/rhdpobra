<?php

namespace App\Filament\Resources\EmployeeContracts\Tables;

use App\Models\EmployeeContract;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable(),

                TextColumn::make('registration_number')
                    ->label('Matrícula')
                    ->searchable(),

                TextColumn::make('contract_sequence')
                    ->label('Seq.'),

                TextColumn::make('company.name')
                    ->label('Empresa'),

                TextColumn::make('work.name')
                    ->label('Obra'),

                TextColumn::make('jobRole.name')
                    ->label('Cargo'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'ativo',
                        'warning' => 'em_aviso',
                        'danger' => 'desligado',
                        'secondary' => 'suspenso',
                        'info' => 'afastado',
                    ]),

                BadgeColumn::make('is_current')
                    ->label('Atual')
                    ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não')
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ]),
            ])

            ->recordActions([
                EditAction::make(),

                // 🔥 MARCAR COMO ATUAL
                Action::make('set_current')
                    ->label('Tornar Atual')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->is_current)
                    ->requiresConfirmation()
                    ->action(function (EmployeeContract $record) {

                        EmployeeContract::query()
                            ->where('employee_id', $record->employee_id)
                            ->update(['is_current' => false]);

                        $record->update([
                            'is_current' => true,
                            'status' => 'ativo',
                        ]);

                        Notification::make()
                            ->title('Contrato definido como atual.')
                            ->success()
                            ->send();
                    }),

                // ⚠️ COLOCAR EM AVISO
                Action::make('set_notice')
                    ->label('Colocar em Aviso')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'ativo')
                    ->requiresConfirmation()
                    ->action(function (EmployeeContract $record) {

                        $record->update([
                            'status' => 'em_aviso',
                        ]);

                        Notification::make()
                            ->title('Contrato em aviso prévio.')
                            ->success()
                            ->send();
                    }),

                // ❌ DESLIGAR
                Action::make('terminate')
                    ->label('Desligar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (EmployeeContract $record) {

                        $record->update([
                            'status' => 'desligado',
                            'is_current' => false,
                            'termination_date' => now(),
                        ]);

                        Notification::make()
                            ->title('Contrato desligado.')
                            ->success()
                            ->send();
                    }),

                // 🔁 RECONTRATAR
                Action::make('rehire')
                    ->label('Recontratar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'desligado')
                    ->requiresConfirmation()
                    ->action(function (EmployeeContract $record) {

                        $nextSequence = EmployeeContract::query()
                            ->where('employee_id', $record->employee_id)
                            ->max('contract_sequence');

                        $nextSequence = ((int) $nextSequence) + 1;

                        EmployeeContract::create([
                            'employee_id' => $record->employee_id,
                            'company_id' => $record->company_id,
                            'branch_id' => $record->branch_id,
                            'work_id' => $record->work_id,
                            'department_id' => $record->department_id,
                            'job_role_id' => $record->job_role_id,
                            'cost_center_id' => $record->cost_center_id,
                            'contract_type_id' => $record->contract_type_id,
                            'work_shift_id' => $record->work_shift_id,
                            'registration_number' => $record->registration_number,
                            'contract_sequence' => $nextSequence,
                            'status' => 'ativo',
                            'is_current' => true,
                            'admission_date' => now(),
                            'salary' => $record->salary,
                        ]);

                        Notification::make()
                            ->title('Colaborador recontratado.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}