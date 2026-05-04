<?php

namespace App\Filament\Resources\PointIntegrations\Tables;

use App\Models\PointIntegration;
use App\Services\Integrations\Solides\SolidesPunchImportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PointIntegrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Integração')
                    ->searchable(),

                TextColumn::make('provider')
                    ->label('Sistema')
                    ->badge(),

                IconColumn::make('active')
                    ->label('Ativa')
                    ->boolean(),

                TextColumn::make('last_sync_at')
                    ->label('Última Sincronização')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Nunca'),
            ])

            ->recordActions([
                /*
                |--------------------------------------------------------------------------
                | 🔥 IMPORTAR MARCAÇÕES
                |--------------------------------------------------------------------------
                */
                Action::make('importPunches')
                    ->label('Importar Marcações')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')

                    ->form([
                        DatePicker::make('start_date')
                            ->label('Data Inicial')
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('Data Final')
                            ->required(),
                    ])

                    ->action(function (PointIntegration $record, array $data) {

                        $import = app(SolidesPunchImportService::class)
                            ->import(
                                $record,
                                $data['start_date'],
                                $data['end_date']
                            );

                        if ($import->status === 'completed') {

                            Notification::make()
                                ->title('Importação concluída')
                                ->body(
                                    "Total: {$import->total_records}\n" .
                                    "Importados: {$import->imported_records}\n" .
                                    "Ignorados: {$import->ignored_records}"
                                )
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Erro na importação')
                            ->body($import->error_message ?? 'Erro desconhecido')
                            ->danger()
                            ->send();
                    })

                    ->modalHeading('Importar Marcações de Ponto')
                    ->modalDescription('Selecione o período para importar as marcações da Sólides/Tangerino.')
                    ->modalSubmitActionLabel('Importar'),

                /*
                |--------------------------------------------------------------------------
                | OUTRAS ACTIONS
                |--------------------------------------------------------------------------
                */
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}