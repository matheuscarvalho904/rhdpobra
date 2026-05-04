<?php

namespace App\Filament\Resources\SystemBackups\Tables;

use App\Models\SystemBackup;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;

class SystemBackupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'warning',
                        'pending' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'success' => 'Concluído',
                        'failed' => 'Falhou',
                        'running' => 'Executando',
                        'pending' => 'Pendente',
                        default => $state ?? '-',
                    }),

                Tables\Columns\TextColumn::make('disk')
                    ->label('Disco')
                    ->badge(),

                Tables\Columns\TextColumn::make('size_for_humans')
                    ->label('Tamanho'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('finished_at')
                    ->label('Finalizado em')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (SystemBackup $record): bool => $record->status === 'success' && filled($record->path))
                    ->url(fn (SystemBackup $record): string => route('system-backups.download', $record))
                    ->openUrlInNewTab(),

                Action::make('details')
                    ->label('Detalhes')
                    ->icon('heroicon-o-document-text')
                    ->modalHeading('Detalhes do Backup')
                    ->modalContent(fn (SystemBackup $record) => view('filament.backups.details', [
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),
            ])
            ->bulkActions([]);
    }
}