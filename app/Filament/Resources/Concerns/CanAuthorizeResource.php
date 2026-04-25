<?php

namespace App\Filament\Resources\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait CanAuthorizeResource
{
    protected static function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    protected static function getPermissionPrefix(): string
    {
        if (! property_exists(static::class, 'permissionPrefix')) {
            return '';
        }

        /** @var string|null $prefix */
        $prefix = static::$permissionPrefix ?? '';

        return (string) $prefix;
    }

    protected static function canPermission(string $action): bool
    {
        $prefix = static::getPermissionPrefix();

        if ($prefix === '') {
            return false;
        }

        return static::currentUser()?->can($prefix . '.' . $action) ?? false;
    }

    public static function canViewAny(): bool
    {
        return static::canPermission('view');
    }

    public static function canView(Model $record): bool
    {
        return static::canPermission('view');
    }

    public static function canCreate(): bool
    {
        return static::canPermission('create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::canPermission('update');
    }

    public static function canDelete(Model $record): bool
    {
        return static::canPermission('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::canPermission('delete');
    }
}