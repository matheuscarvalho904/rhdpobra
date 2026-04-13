<?php

namespace App\Filament\Resources\EmployeeDocuments\Schemas;

use App\Models\DocumentType;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Documento do Colaborador')
            
                    ->schema([
                        Select::make('employee_id')
                            ->label('Colaborador')
                            ->options(
                                Employee::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('document_type_id')
                            ->label('Tipo de Documento')
                            ->options(
                                DocumentType::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('document_number')
                            ->label('Número do Documento')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('issuing_agency')
                            ->label('Órgão Emissor')
                            ->maxLength(255),

                        TextInput::make('issuing_state')
                            ->label('UF Emissora')
                            ->maxLength(2),

                        DatePicker::make('issue_date')
                            ->label('Data de Emissão')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('expiration_date')
                            ->label('Data de Validade')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}