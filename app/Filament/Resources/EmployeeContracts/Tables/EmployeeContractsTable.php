<?php

namespace App\Filament\Resources\EmployeeContracts\Tables;

use App\Models\EmployeeContract;
use App\Models\EmployeeTermination;
use App\Services\EmployeeContractLifecycleService;
use App\Services\EmployeeRehireService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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
                TextColumn::make('contract_sequence')->label('Seq.'),
                TextColumn::make('company.name')->label('Empresa'),
                TextColumn::make('work.name')->label('Obra'),
                TextColumn::make('jobRole.name')->label('Cargo'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'ativo' => 'success',
                        'em_aviso' => 'warning',
                        'desligado' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('is_current')
                    ->label('Atual')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])

            ->recordActions([
                EditAction::make(),

                // 🔥 ATIVAR CONTRATO
                Action::make('set_current')
                    ->label('Tornar Atual')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->is_current)
                    ->requiresConfirmation()
                    ->action(function (EmployeeContract $record, EmployeeContractLifecycleService $service) {
                        $service->activateContract($record);

                        Notification::make()
                            ->title('Contrato definido como atual.')
                            ->success()
                            ->send();
                    }),

                // ⚠ AVISO
                Action::make('set_notice')
                    ->label('Colocar em Aviso')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'ativo')
                    ->requiresConfirmation()
                    ->action(function (EmployeeContract $record, EmployeeContractLifecycleService $service) {
                        $service->putInNotice($record);

                        Notification::make()
                            ->title('Contrato em aviso.')
                            ->success()
                            ->send();
                    }),

                // 🚨 NOVO FLUXO CORRETO
                Action::make('start_termination')
                    ->label('Iniciar Desligamento')
                    ->icon('heroicon-o-document-minus')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== 'desligado')
                    ->requiresConfirmation()
                    ->action(function (EmployeeContract $record) {

                        $termination = EmployeeTermination::create([
                            'employee_id' => $record->employee_id,
                            'employee_contract_id' => $record->id,
                            'status' => 'draft',
                            'termination_date' => now()->toDateString(),
                        ]);

                        return redirect()->to(
                            route('filament.app.resources.employee-terminations.edit', [
                                'record' => $termination->id,
                            ])
                        );
                    }),

                // 🔁 RECONTRATAR
                Action::make('rehire')
                    ->label('Recontratar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'desligado')
                    ->form([
                        DatePicker::make('admission_date')->label('Admissão')->required(),
                        TextInput::make('salary')->label('Salário')->numeric()->required(),
                    ])
                    ->action(function (EmployeeContract $record, array $data, EmployeeRehireService $service) {
                        $service->rehire($record->employee, $data);

                        Notification::make()
                            ->title('Recontratado com sucesso.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}