<?php

namespace App\Filament\Concerns;

use App\Models\User;
use App\Support\CurrentCompany;
use App\Support\PermissionManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasCompanyPermission
{
    protected static function permissionModule(): string
{
    return defined(static::class . '::PERMISSION_MODULE')
        ? constant(static::class . '::PERMISSION_MODULE')
        : '';
}

    public static function shouldRegisterNavigation(): bool
    {
        return static::canCompany('view');
    }

    public static function canViewAny(): bool
    {
        return static::canCompany('view');
    }

    public static function canView(Model $record): bool
    {
        return static::canCompany('view');
    }

    public static function canCreate(): bool
    {
        return static::canCompany('create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::canCompany('edit');
    }

    public static function canDelete(Model $record): bool
    {
        return static::canCompany('delete');
    }

    protected static function canCompany(string $action): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        $companyId = CurrentCompany::id();
        $module = static::permissionModule();

        if (! $companyId || blank($module)) {
            return false;
        }

        return PermissionManager::allows(
            user: $user,
            companyId: $companyId,
            module: $module,
            action: $action
        );
    }
}