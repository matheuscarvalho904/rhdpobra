<?php

namespace App\Filament\Resources\TimeEntryImports\Schemas;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Work;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TimeEntryImportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Importação')
                
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

                        TextInput::make('file_name')
                            ->label('Nome do Arquivo')
                            ->required()
                            ->maxLength(255),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pendente',
                                'processing' => 'Processando',
                                'completed' => 'Concluído',
                                'completed_with_errors' => 'Concluído com Erros',
                                'failed' => 'Falhou',
                            ])
                            ->default('pending')
                            ->required(),

                        Select::make('imported_by')
                            ->label('Importado por')
                            ->options(
                                User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('imported_rows')
                            ->label('Linhas Importadas')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('valid_rows')
                            ->label('Linhas Válidas')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('invalid_rows')
                            ->label('Linhas Inválidas')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Placeholder::make('import_hint')
                            ->label('Observação')
                            ->content('A importação ideal deve passar primeiro por validação antes de gravar os lançamentos definitivos.')
                            ->columnSpanFull(),
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