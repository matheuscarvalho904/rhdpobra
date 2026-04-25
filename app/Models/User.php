<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
/**
 * @method bool can(string $ability, mixed $arguments = [])
 * @method bool hasRole(string|array $roles)
 * @method bool hasAnyRole(string|array $roles)
 */


/**
 * @method bool hasRole(string|array $roles, ?string $guard = null)
 * @method bool hasAnyRole(string|array $roles, ?string $guard = null)
 * @method bool hasAllRoles(string|array $roles, ?string $guard = null)
 */
class User extends Authenticatable implements FilamentUser
{
    use Notifiable;
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->hasAnyRole([
            'Administrador',
            'RH',
            'Encarregado',
            'Financeiro',
            'Operador',
            'Consulta',
        ]);
    }

    public function accessScopes(): HasMany
    {
        return $this->hasMany(UserAccessScope::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Administrador');
    }

    public function isRh(): bool
    {
        return $this->hasRole('RH');
    }

    public function isConsulta(): bool
    {
        return $this->hasRole('Consulta');
    }
}