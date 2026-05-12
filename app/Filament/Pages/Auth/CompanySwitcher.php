<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Support\CurrentCompany;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CompanySwitcher extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.auth.company-switcher';

    public ?int $company_id = null;

    public function mount(): void
    {
        $this->company_id = CurrentCompany::id();

        $this->form->fill([
            'company_id' => $this->company_id,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Empresa ativa')
                    ->options(function (): array {
                        $user = Auth::user();

                        if (! $user instanceof User) {
                            return [];
                        }

                        return $user->activeCompanies()
                            ->orderBy('companies.name')
                            ->get()
                            ->mapWithKeys(fn ($company) => [
                                $company->id => $company->name,
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state): void {
                        if (! $state) {
                            return;
                        }

                        CurrentCompany::set((int) $state);

                        Notification::make()
                            ->title('Empresa alterada com sucesso.')
                            ->success()
                            ->send();

                        $this->redirect('/app');
                    }),
            ]);
    }
}