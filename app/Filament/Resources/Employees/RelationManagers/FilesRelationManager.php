<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Services\EmployeeContractDocumentService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static ?string $title = 'Arquivos do Colaborador';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'contrato' => 'Contrato',
                        'aditivo' => 'Aditivo',
                        'epi' => 'Termo de EPI',
                        'ficha' => 'Ficha',
                        'documento' => 'Documento',
                        default => ucfirst((string) $state),
                    })
                    ->colors([
                        'success' => fn ($state) => $state === 'contrato',
                        'warning' => fn ($state) => $state === 'epi',
                        'info' => fn ($state) => $state === 'aditivo',
                        'gray' => fn ($state) => ! in_array($state, ['contrato', 'epi', 'aditivo'], true),
                    ]),

                TextColumn::make('file_name')
                    ->label('Arquivo')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('generated_at')
                    ->label('Gerado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label('Observações')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->headerActions([
                Action::make('generate_contract')
                    ->label('Gerar Contrato')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->form([
                        Select::make('type')
                            ->label('Tipo de geração')
                            ->options([
                                'contrato' => 'Contrato',
                            ])
                            ->default('contrato')
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (array $data): void {
                        try {
                            $employee = $this->getOwnerRecord();

                            $service = app(EmployeeContractDocumentService::class);
                            $pdfContent = $service->output($employee);
                            $fileName = $service->suggestFileName($employee);
                            $filePath = 'employees/contracts/' . $fileName;

                            Storage::disk('public')->put($filePath, $pdfContent);

                            $this->getRelationship()->create([
                                'type' => $data['type'],
                                'file_name' => $fileName,
                                'file_path' => $filePath,
                                'generated_at' => now(),
                                'notes' => 'Gerado manualmente pela tela de arquivos.',
                            ]);

                            Notification::make()
                                ->title('Contrato gerado com sucesso.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao gerar contrato.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Baixar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function ($record) {
                        $fullPath = storage_path('app/public/' . $record->file_path);

                        if (! file_exists($fullPath)) {
                            Notification::make()
                                ->title('Arquivo não encontrado.')
                                ->danger()
                                ->send();

                            return null;
                        }

                        return response()->download($fullPath, $record->file_name);
                    }),

                DeleteAction::make()
                    ->label('Excluir')
                    ->before(function ($record): void {
                        if ($record->file_path && Storage::disk('public')->exists($record->file_path)) {
                            Storage::disk('public')->delete($record->file_path);
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Nenhum arquivo encontrado')
            ->emptyStateDescription('Os contratos e demais arquivos gerados do colaborador aparecerão aqui.');
    }
}