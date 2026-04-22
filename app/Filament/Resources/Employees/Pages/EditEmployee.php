<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Services\EmployeeContractDocumentService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
{
    return [
        Action::make('generate_contract')
            ->label('Gerar Contrato')
            ->icon('heroicon-o-document-text')
            ->color('success')
            ->action(function () {
                $service = app(EmployeeContractDocumentService::class);

                return response()->streamDownload(
                    fn () => print($service->output($this->record)),
                    'contrato-' . $this->record->id . '.pdf'
                );
            }),
    ];
}
}