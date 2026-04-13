<?php

namespace App\Filament\Resources\SalaryAdvances\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalaryAdvancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('work.name')
                    ->label('Obra')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('advance_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format((float) $state, 2, ',', '.'))
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pix' => 'PIX',
                        'bank_transfer' => 'Transferência',
                        'cash' => 'Dinheiro',
                        default => $state,
                    }),

                TextColumn::make('pix_key_type')
                    ->label('Tipo PIX')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'cpf' => 'CPF',
                        'cnpj' => 'CNPJ',
                        'email' => 'E-mail',
                        'phone' => 'Telefone',
                        'random' => 'Aleatória',
                        default => $state ?? '-',
                    })
                    ->toggleable(),

                TextColumn::make('pix_key')
                    ->label('Chave PIX')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'draft' => 'Rascunho',
                        'paid' => 'Pago',
                        'canceled' => 'Cancelado',
                        'integrated_payroll' => 'Integrado na Folha',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}