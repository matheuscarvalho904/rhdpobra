<?php

namespace App\Filament\Resources\SystemBackups\Pages;

use App\Filament\Resources\SystemBackups\SystemBackupResource;
use App\Services\SystemBackupService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSystemBackups extends ListRecords
{
    protected static string $resource = SystemBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runBackup')
                ->label('Gerar Backup')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Gerar backup agora?')
                ->modalDescription('Será criado um backup da base de dados atual.')
                ->modalSubmitActionLabel('Sim, gerar backup')
                ->action(function (): void {
                    $backup = app(SystemBackupService::class)->run();

                    if ($backup->status === 'success') {
                        Notification::make()
                            ->title('Backup gerado com sucesso')
                            ->body('O arquivo foi salvo e já está disponível para download.')
                            ->success()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Falha ao gerar backup')
                        ->body($backup->message ?: 'Erro desconhecido.')
                        ->danger()
                        ->send();
                }),
        ];
    }
}