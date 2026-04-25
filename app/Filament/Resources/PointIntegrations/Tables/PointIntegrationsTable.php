<?php

namespace App\Filament\Resources\PointIntegrations\Tables;

use App\Models\PointIntegration;
use App\Services\Integrations\Solides\SolidesPointService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->sortable()
                    ->placeholder('Todas'),

                TextColumn::make('name')
                    ->label('Integração')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('provider')
                    ->label('Sistema')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'solides' => 'Sólides',
                        default => $state ?: '-',
                    }),

                IconColumn::make('active')
                    ->label('Ativa')
                    ->boolean(),

                TextColumn::make('last_sync_at')
                    ->label('Última Sincronização')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Nunca sincronizado'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('testConnection')
                    ->label('Testar Conexão')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Testar conexão com Sólides')
                    ->modalDescription('O sistema tentará conectar na API usando a URL base e o token informado.')
                    ->action(function (PointIntegration $record): void {
                        $service = new SolidesPointService($record);

                        $result = $service->testConnection();

                        if ($result['success'] ?? false) {
                            $record->update([
                                'last_sync_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Conexão realizada com sucesso')
                                ->body($result['message'] ?? 'A API respondeu corretamente.')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Falha na conexão')
                            ->body($result['message'] ?? 'Não foi possível conectar com a API.')
                            ->danger()
                            ->send();
                    }),

                ViewAction::make()
                    ->label('Visualizar'),

                EditAction::make()
                    ->label('Editar'),

                DeleteAction::make()
                    ->label('Excluir'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Excluir selecionados'),
                ]),
            ]);
    }
}