<?php

namespace App\Filament\Resources\EmployeeEpiDeliveries\Pages;

use App\Filament\Resources\EmployeeEpiDeliveries\EmployeeEpiDeliveryResource;
use App\Services\EmployeeEpiTermService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditEmployeeEpiDelivery extends EditRecord
{
    protected static string $resource = EmployeeEpiDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gerar_termo')
                ->label('Gerar Termo EPI')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->action(function () {
                    try {
                        $delivery = $this->record->loadMissing([
                            'employee.company',
                            'employee.work',
                            'employee.jobRole',
                            'items.epi',
                        ]);

                        $service = app(EmployeeEpiTermService::class);

                        $pdf = $service->output($delivery);
                        $fileName = $service->suggestFileName($delivery);
                        $filePath = "employees/epi/{$fileName}";

                        Storage::disk('public')->put($filePath, $pdf);

                        $delivery->update([
                            'term_file_path' => $filePath,
                            'term_file_name' => $fileName,
                        ]);

                        $delivery->employee->files()->create([
                            'type' => 'epi',
                            'file_name' => $fileName,
                            'file_path' => $filePath,
                            'generated_at' => now(),
                            'is_active' => true,
                            'notes' => 'Termo de entrega de EPI em lote',
                        ]);

                        Notification::make()
                            ->title('Termo gerado com sucesso.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Erro ao gerar termo.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make()
                ->label('Excluir'),
        ];
    }
}