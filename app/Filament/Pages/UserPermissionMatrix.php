<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\User;
use App\Models\UserCompanyPermission;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class UserPermissionMatrix extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'Segurança';

    protected static ?string $navigationLabel = 'Permissões por Usuário';

    protected static ?string $title = 'Matriz de Permissões por Usuário';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.user-permission-matrix';

    public ?int $user_id = null;

    public ?int $company_id = null;

    public array $permissions = [];

    public function mount(): void
    {
        $this->form->fill([
            'permissions' => [],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar Permissões')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(function (): void {
                    $data = $this->form->getState();

                    if (blank($data['user_id'] ?? null) || blank($data['company_id'] ?? null)) {
                        Notification::make()
                            ->title('Selecione usuário e empresa.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $selected = $data['permissions'] ?? [];

                    foreach (self::permissionMap() as $module => $config) {
                        foreach ($config['actions'] as $action => $label) {
                            UserCompanyPermission::updateOrCreate(
                                [
                                    'user_id' => $data['user_id'],
                                    'company_id' => $data['company_id'],
                                    'module' => $module,
                                    'action' => $action,
                                ],
                                [
                                    'allowed' => in_array("{$module}.{$action}", $selected, true),
                                ]
                            );
                        }
                    }

                    Notification::make()
                        ->title('Permissões salvas com sucesso.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Usuário e Empresa')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Usuário')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadPermissions()),

                        Select::make('company_id')
                            ->label('Empresa')
                            ->options(fn (): array => Company::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadPermissions()),
                    ]),

                Section::make('Permissões')
                    ->description('Marque as ações permitidas para este usuário nesta empresa.')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('')
                            ->options(self::flatPermissionOptions())
                            ->columns(4)
                            ->bulkToggleable(),
                    ]),
            ]);
    }

    public function loadPermissions(): void
    {
        $state = $this->form->getState();

        $userId = $state['user_id'] ?? null;
        $companyId = $state['company_id'] ?? null;

        if (! $userId || ! $companyId) {
            return;
        }

        $allowed = UserCompanyPermission::query()
            ->where('user_id', $userId)
            ->where('company_id', $companyId)
            ->where('allowed', true)
            ->get()
            ->map(fn ($permission) => "{$permission->module}.{$permission->action}")
            ->values()
            ->toArray();

        $this->form->fill([
            'user_id' => $userId,
            'company_id' => $companyId,
            'permissions' => $allowed,
        ]);
    }

    protected static function flatPermissionOptions(): array
    {
        $options = [];

        foreach (self::permissionMap() as $module => $config) {
            foreach ($config['actions'] as $action => $label) {
                $options["{$module}.{$action}"] = "{$config['label']} - {$label}";
            }
        }

        return $options;
    }

    protected static function permissionMap(): array
    {
        return [
            'employees' => [
                'label' => 'Colaboradores',
                'actions' => [
                    'view' => 'Visualizar',
                    'create' => 'Cadastrar',
                    'edit' => 'Editar',
                    'delete' => 'Excluir',
                    'export' => 'Exportar',
                    'contract' => 'Gerar Contrato',
                ],
            ],
            'payroll' => [
                'label' => 'Folha',
                'actions' => [
                    'view' => 'Visualizar',
                    'create' => 'Criar',
                    'process' => 'Processar',
                    'reprocess' => 'Reprocessar',
                    'close' => 'Fechar',
                    'delete' => 'Excluir',
                ],
            ],
            'time_bank' => [
                'label' => 'Banco de Horas',
                'actions' => [
                    'view' => 'Visualizar',
                    'adjust' => 'Ajustar',
                    'payout' => 'Pagar Banco',
                    'delete' => 'Excluir',
                ],
            ],
            'works' => [
                'label' => 'Obras',
                'actions' => [
                    'view' => 'Visualizar',
                    'create' => 'Cadastrar',
                    'edit' => 'Editar',
                    'delete' => 'Excluir',
                ],
            ],
            'finance' => [
                'label' => 'Financeiro',
                'actions' => [
                    'view' => 'Visualizar',
                    'create' => 'Cadastrar',
                    'edit' => 'Editar',
                    'delete' => 'Excluir',
                    'approve' => 'Aprovar',
                ],
            ],
            'settings' => [
                'label' => 'Configurações',
                'actions' => [
                    'view' => 'Visualizar',
                    'edit' => 'Editar',
                ],
            ],
        ];
    }
}