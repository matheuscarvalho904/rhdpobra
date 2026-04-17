<?php

namespace App\Filament\Resources\EmployeeTerminations\Tables;

use App\Services\TerminationProcessingService;
use App\Services\TerminationReportService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeTerminationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contract.registration_number')
                    ->label('Matrícula')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('notice_type')
                    ->label('Aviso')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'worked' => 'Trabalhado',
                        'indemnified' => 'Indenizado',
                        'home' => 'Em Casa',
                        default => '-',
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draft' => 'Rascunho',
                        'in_progress' => 'Em Andamento',
                        'closed' => 'Fechado',
                        'cancelled' => 'Cancelado',
                        default => (string) $state,
                    })
                    ->sortable(),

                TextColumn::make('termination_date')
                    ->label('Desligamento')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('projected_end_date')
                    ->label('Projeção')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('notice_amount')
                    ->label('Aviso')
                    ->money('BRL')
                    ->placeholder('-')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('termination_amount')
                    ->label('Líquido Rescisão')
                    ->money('BRL')
                    ->placeholder('-')
                    ->alignEnd()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),

                Action::make('calculate_termination')
                    ->label('Calcular Rescisão')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->action(function ($record, TerminationProcessingService $service) {
                        try {
                            $result = $service->process($record);

                            Notification::make()
                                ->title('Rescisão calculada com sucesso.')
                                ->body('Líquido: R$ ' . number_format((float) ($result['net_amount'] ?? 0), 2, ',', '.'))
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao calcular rescisão.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'in_progress'], true)),

                Action::make('print_termination')
                    ->label('Gerar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function ($record) {
                        $service = app(TerminationReportService::class);
                        $pdfContent = $service->output($record);

                        return response()->streamDownload(
                            fn () => print($pdfContent),
                            'rescisao-' . ($record->id ?? 'arquivo') . '.pdf'
                        );
                    }),

                Action::make('close_termination')
                    ->label('Fechar Desligamento')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record, TerminationProcessingService $service) {
                        try {
                            $service->processAndClose($record);

                            Notification::make()
                                ->title('Desligamento fechado com sucesso.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao fechar desligamento.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'in_progress'], true)),
            ]);
    }
}