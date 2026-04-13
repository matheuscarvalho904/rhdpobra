<?php

namespace App\Filament\Resources\PayrollEvents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayrollEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Evento')
                    
                    ->schema([
                        TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(30)
                            ->unique(ignoreRecord: true),

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'earning' => 'Provento',
                                'deduction' => 'Desconto',
                                'base' => 'Base',
                                'informational' => 'Informativo',
                            ])
                            ->required(),

                        Select::make('incidence_type')
                            ->label('Tipo de Incidência')
                            ->options([
                                'salary' => 'Salário',
                                'overtime' => 'Hora Extra',
                                'night' => 'Noturno',
                                'rest' => 'DSR',
                                'allowance' => 'Adicional',
                                'bonus' => 'Bônus/Gratificação',
                                'commission' => 'Comissão',
                                'absence' => 'Falta',
                                'lateness' => 'Atraso',
                                'social_security' => 'Previdência',
                                'fund' => 'Fundo',
                                'income_tax' => 'Imposto de Renda',
                                'benefit' => 'Benefício',
                                'advance' => 'Adiantamento',
                                'loan' => 'Empréstimo',
                                'legal' => 'Legal/Judicial',
                                'deduction' => 'Desconto Geral',
                            ])
                            ->searchable(),

                        Select::make('calculation_type')
                            ->label('Tipo de Cálculo')
                            ->options([
                                'fixed' => 'Valor Fixo',
                                'percentage' => 'Percentual',
                                'quantity_x_value' => 'Quantidade x Valor',
                                'manual' => 'Manual',
                                'automatic' => 'Automático',
                            ])
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),

                        Toggle::make('affects_inss')
                            ->label('Incide INSS')
                            ->default(false),

                        Toggle::make('affects_fgts')
                            ->label('Incide FGTS')
                            ->default(false),

                        Toggle::make('affects_irrf')
                            ->label('Incide IRRF')
                            ->default(false),

                        Toggle::make('affects_net')
                            ->label('Afeta Líquido')
                            ->default(true),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}