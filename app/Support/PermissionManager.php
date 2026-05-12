<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserCompanyPermission;

class PermissionManager
{
    public static function allows(
        User $user,
        int $companyId,
        string $module,
        string $action
    ): bool {

        return UserCompanyPermission::query()

            ->where('user_id', $user->id)

            ->where('company_id', $companyId)

            ->where('module', $module)

            ->where('action', $action)

            ->where('allowed', true)

            ->exists();
    }
}