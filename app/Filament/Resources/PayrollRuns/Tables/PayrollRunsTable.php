<?php

namespace App\Filament\Resources\PayrollRuns\Tables;

use App\Models\PayrollCompetency;
use App\Services\PayrollRunProcessingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PayrollRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable()
                    ->weight('semiBold')
                    ->wrap(),

                TextColumn::make('payrollCompetency.display_name')
                    ->label('Competência')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('run_type')
                    ->label('Tipo da Folha')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::runTypeLabels()[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'payroll_clt' => 'success',
                        'payroll_apprentice' => 'info',
                        'internship_payment' => 'warning',
                        'payroll_rpa' => 'danger',
                        'accounts_payable' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('branch.name')
                    ->label('Filial')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('work.name')
                    ->label('Obra')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'open' => 'Aberta',
                        'processing' => 'Processando',
                        'processed' => 'Processada',
                        'closed' => 'Fechada',
                        'error' => 'Erro',
                        default => (string) $state,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'open' => 'gray',
                        'processing' => 'warning',
                        'processed' => 'success',
                        'closed' => 'primary',
                        'error' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('processed_employees')
                    ->label('Colaboradores')
                    ->alignCenter()
                    ->sortable()
                    ->placeholder('0'),

                TextColumn::make('total_gross')
                    ->label('Bruto')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'R$ ' . number_format((float) ($state ?? 0), 2, ',', '.')),

                TextColumn::make('total_discounts')
                    ->label('Descontos')
                    ->alignEnd()
                    ->sortable()
                    ->color('danger')
                    ->formatStateUsing(fn ($state): string => 'R$ ' . number_format((float) ($state ?? 0), 2, ',', '.')),

                TextColumn::make('total_net')
                    ->label('Líquido')
                    ->alignEnd()
                    ->sortable()
                    ->weight('semiBold')
                    ->color('success')
                    ->formatStateUsing(fn ($state): string => 'R$ ' . number_format((float) ($state ?? 0), 2, ',', '.')),

                TextColumn::make('total_fgts')
                    ->label('FGTS')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'R$ ' . number_format((float) ($state ?? 0), 2, ',', '.'))
                    ->toggleable(),

                TextColumn::make('processed_at')
                    ->label('Processada em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('closed_at')
                    ->label('Fechada em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('error_message')
                    ->label('Erro')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->placeholder('-')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('branch_id')
                    ->label('Filial')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('work_id')
                    ->label('Obra')
                    ->relationship('work', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payroll_competency_id')
                    ->label('Competência')
                    ->options(fn () => PayrollCompetency::query()
                        ->orderByDesc('year')
                        ->orderByDesc('month')
                        ->get()
                        ->mapWithKeys(fn (PayrollCompetency $competency) => [
                            $competency->id => $competency->display_name,
                        ])
                        ->toArray())
                    ->searchable()
                    ->preload(),

                SelectFilter::make('run_type')
                    ->label('Tipo da Folha')
                    ->options(self::runTypeLabels()),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Aberta',
                        'processing' => 'Processando',
                        'processed' => 'Processada',
                        'closed' => 'Fechada',
                        'error' => 'Erro',
                    ]),
            ])
            ->filtersFormColumns(3)
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->recordActions([
                Action::make('process')
                    ->label('Processar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => in_array($record->status, ['open', 'error'], true))
                    ->action(function ($record): void {
                        app(PayrollRunProcessingService::class)->reprocess($record);

                        Notification::make()
                            ->title('Folha processada com sucesso.')
                            ->success()
                            ->send();
                    }),

                Action::make('reprocess')
                    ->label('Reprocessar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => in_array($record->status, ['processed', 'error'], true))
                    ->action(function ($record): void {
                        app(PayrollRunProcessingService::class)->reprocess($record);

                        Notification::make()
                            ->title('Folha reprocessada com sucesso.')
                            ->success()
                            ->send();
                    }),

                Action::make('download_all_payslips')
                    ->label('Holerites ZIP')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->visible(fn ($record): bool => in_array($record->status, ['processed', 'closed'], true))
                    ->url(fn ($record): string => route('payroll.payslip.download-all', [
                        'payrollRun' => $record->id,
                    ]))
                    ->openUrlInNewTab(),

                Action::make('close')
                    ->label('Fechar')
                    ->icon('heroicon-o-lock-closed')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status === 'processed')
                    ->action(function ($record): void {
                        $record->update([
                            'status' => 'closed',
                            'closed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Folha fechada com sucesso.')
                            ->success()
                            ->send();
                    }),

                ViewAction::make()
                    ->label('Visualizar'),

                EditAction::make()
                    ->label('Editar')
                    ->visible(fn ($record): bool => $record->status !== 'closed'),

                DeleteAction::make()
                    ->label('Excluir')
                    ->visible(fn ($record): bool => in_array($record->status, ['open', 'error'], true)),
            ])
            ->emptyStateHeading('Nenhum processamento de folha encontrado')
            ->emptyStateDescription('Crie uma nova folha para começar o processamento da competência.');
    }

    protected static function runTypeLabels(): array
    {
        return [
            'payroll_clt' => 'Folha CLT',
            'payroll_apprentice' => 'Folha Aprendiz',
            'internship_payment' => 'Folha Estágio',
            'payroll_rpa' => 'Folha RPA / PF',
            'accounts_payable' => 'Contas a Pagar / PJ',
        ];
    }
}