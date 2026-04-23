<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Branch;
use App\Models\Company;
use App\Models\ContractType;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\EmployeeFile;
use App\Models\JobRole;
use App\Models\Work;
use App\Models\WorkShift;
use App\Services\EmployeeContractDocumentService;
use App\Services\EmployeeEpiReportService;
use App\Services\EmployeeRehireService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable()
                    ->weight('semiBold')
                    ->description(fn ($record): ?string => $record->cpf ?: null)
                    ->wrap(),

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('branch.name')
                    ->label('Filial')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('work.name')
                    ->label('Obra')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('jobRole.name')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('contractType.name')
                    ->label('Contrato')
                    ->badge()
                    ->color(fn (?string $state): string => match (mb_strtoupper((string) $state)) {
                        'CLT' => 'success',
                        'APRENDIZ' => 'info',
                        'ESTÁGIO', 'ESTAGIO' => 'warning',
                        'PESSOA FÍSICA', 'PESSOA FISICA', 'PF', 'AUTÔNOMO', 'AUTONOMO', 'RPA' => 'primary',
                        'PESSOA JURÍDICA', 'PESSOA JURIDICA', 'PJ' => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('processing_type')
                    ->label('Processamento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'payroll_clt' => 'Folha CLT',
                        'payroll_rpa' => 'Folha RPA',
                        'internship_payment' => 'Estágio',
                        'accounts_payable' => 'Contas a Pagar',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'payroll_clt' => 'success',
                        'payroll_rpa' => 'primary',
                        'internship_payment' => 'warning',
                        'accounts_payable' => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('salary')
                    ->label('Salário Base')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state): string => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->toggleable(),

                TextColumn::make('salary_advance_amount')
                    ->label('Adiantamento')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state): string => 'R$ ' . number_format((float) ($state ?? 0), 2, ',', '.'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pix' => 'PIX',
                        'transfer' => 'Transferência',
                        'bank_deposit' => 'Depósito',
                        'cash' => 'Dinheiro',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pix' => 'success',
                        'transfer' => 'info',
                        'bank_deposit' => 'warning',
                        'cash' => 'gray',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('admission_date')
                    ->label('Admissão')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('termination_date')
                    ->label('Desligamento')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'terminated' => 'Desligado',
                        'leave' => 'Afastado',
                        'em_aviso' => 'Em Aviso',
                        default => (string) $state,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'terminated' => 'danger',
                        'leave' => 'warning',
                        'em_aviso' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                IconColumn::make('has_inss')
                    ->label('INSS')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('has_fgts')
                    ->label('FGTS')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('with_inss')
                    ->label('Retém INSS')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
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

                SelectFilter::make('department_id')
                    ->label('Departamento')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('job_role_id')
                    ->label('Cargo')
                    ->relationship('jobRole', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('contract_type_id')
                    ->label('Tipo de Contrato')
                    ->relationship('contractType', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('processing_type')
                    ->label('Processamento')
                    ->options([
                        'payroll_clt' => 'Folha CLT',
                        'payroll_rpa' => 'Folha RPA',
                        'internship_payment' => 'Estágio',
                        'accounts_payable' => 'Contas a Pagar',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'terminated' => 'Desligado',
                        'leave' => 'Afastado',
                        'em_aviso' => 'Em Aviso',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('Forma de Pagamento')
                    ->options([
                        'pix' => 'PIX',
                        'transfer' => 'Transferência',
                        'bank_deposit' => 'Depósito',
                        'cash' => 'Dinheiro',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Somente Ativos')
                    ->placeholder('Todos')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos'),
            ])
            ->filtersFormColumns(3)
            ->striped()
            ->paginated([5, 10, 25, 50, 100])
            ->recordActions([
                ViewAction::make()
                    ->label('Visualizar'),

                EditAction::make()
                    ->label('Editar'),

                Action::make('generate_contract')
                    ->label('Gerar Contrato')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function ($record) {
                        try {
                            $service = app(EmployeeContractDocumentService::class);
                            $pdfContent = $service->output($record);
                            $fileName = $service->suggestFileName($record);
                            $filePath = 'employees/contracts/' . $fileName;

                            Storage::disk('public')->put($filePath, $pdfContent);

                            EmployeeFile::create([
                                'employee_id' => $record->id,
                                'type' => 'contrato',
                                'file_name' => $fileName,
                                'file_path' => $filePath,
                                'generated_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Contrato gerado com sucesso.')
                                ->success()
                                ->send();

                            return response()->streamDownload(
                                fn () => print($pdfContent),
                                $fileName
                            );
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao gerar contrato.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return null;
                        }
                    }),

                Action::make('employee_epi_report')
                    ->label('Relatório EPI')
                    ->icon('heroicon-o-shield-check')
                    ->color('primary')
                    ->action(function ($record) {
                        try {
                            $service = app(EmployeeEpiReportService::class);
                            $pdfContent = $service->generate($record)->output();

                            Notification::make()
                                ->title('Relatório de EPI gerado com sucesso.')
                                ->success()
                                ->send();

                            return response()->streamDownload(
                                fn () => print($pdfContent),
                                'relatorio-epi-colaborador-' . $record->id . '.pdf'
                            );
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao gerar relatório de EPI.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return null;
                        }
                    }),

                Action::make('rehire')
                    ->label('Recontratar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->form([
                        DatePicker::make('admission_date')
                            ->label('Data de Admissão')
                            ->required(),

                        TextInput::make('salary')
                            ->label('Salário')
                            ->numeric()
                            ->required()
                            ->prefix('R$'),

                        Select::make('company_id')
                            ->label('Empresa')
                            ->options(fn () => Company::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->required(),

                        Select::make('branch_id')
                            ->label('Filial')
                            ->options(fn () => Branch::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable(),

                        Select::make('work_id')
                            ->label('Obra')
                            ->options(fn () => Work::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable(),

                        Select::make('department_id')
                            ->label('Departamento')
                            ->options(fn () => Department::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable(),

                        Select::make('job_role_id')
                            ->label('Cargo')
                            ->options(fn () => JobRole::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable(),

                        Select::make('cost_center_id')
                            ->label('Centro de Custo')
                            ->options(fn () => CostCenter::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable(),

                        Select::make('contract_type_id')
                            ->label('Tipo de Contrato')
                            ->options(fn () => ContractType::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable(),

                        Select::make('work_shift_id')
                            ->label('Jornada')
                            ->options(fn () => WorkShift::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable(),
                    ])
                    ->action(function (array $data, $record, EmployeeRehireService $service) {
                        try {
                            $contract = $service->rehire($record, $data);

                            Notification::make()
                                ->title('Colaborador recontratado com sucesso.')
                                ->body('Nova matrícula: ' . $contract->registration_number)
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao recontratar.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => in_array($record->status, ['terminated', 'inactive'], true)),

                DeleteAction::make()
                    ->label('Excluir'),
            ])
            ->emptyStateHeading('Nenhum colaborador encontrado')
            ->emptyStateDescription('Cadastre colaboradores para começar o controle de folha, vínculos e pagamentos.');
    }
}