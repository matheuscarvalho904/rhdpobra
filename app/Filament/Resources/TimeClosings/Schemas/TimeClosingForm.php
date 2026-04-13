<?php

namespace App\Filament\Resources\TimeClosings\Schemas;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Work;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TimeClosingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Fechamento')
                
                    ->schema([
                        Select::make('company_id')
                            ->label('Empresa')
                            ->options(
                                Company::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('branch_id', null);
                                $set('work_id', null);
                            }),

                        Select::make('branch_id')
                            ->label('Filial')
                            ->options(fn ($get) => Branch::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('work_id', null);
                            }),

                        Select::make('work_id')
                            ->label('Obra')
                            ->options(fn ($get) => Work::query()
                                ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                ->when($get('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        DatePicker::make('period_start')
                            ->label('Período Inicial')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),

                        DatePicker::make('period_end')
                            ->label('Período Final')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Aberto',
                                'processing' => 'Processando',
                                'reviewed' => 'Conferido',
                                'approved' => 'Aprovado',
                                'closed' => 'Fechado',
                                'integrated_to_payroll' => 'Integrado à Folha',
                            ])
                            ->default('open')
                            ->required(),

                        Placeholder::make('closing_rule_hint')
                            ->label('Regra')
                            ->content('Cada fechamento deve ser único por empresa, filial, obra e período.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Controle de Processamento')
                    
                    ->schema([
                        Select::make('processed_by')
                            ->label('Processado por')
                            ->options(
                                User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload(),

                        Select::make('approved_by')
                            ->label('Aprovado por')
                            ->options(
                                User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload(),

                        Select::make('closed_by')
                            ->label('Fechado por')
                            ->options(
                                User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload(),

                        DatePicker::make('processed_at')
                            ->label('Processado em')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->dehydrated(false),

                        DatePicker::make('approved_at')
                            ->label('Aprovado em')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->dehydrated(false),

                        DatePicker::make('closed_at')
                            ->label('Fechado em')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->dehydrated(false),
                    ]),

                Section::make('Observações')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(4),
                    ]),
            ]);
    }
}