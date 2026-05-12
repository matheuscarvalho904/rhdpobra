<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CurrentCompany
{
    protected static function user(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public static function get(): ?Company
    {
        $user = self::user();

        if (! $user) {
            return null;
        }

        $companyId = Session::get('current_company_id');

        if ($companyId) {
            $company = $user->companies()
                ->where('companies.id', $companyId)
                ->wherePivot('is_active', true)
                ->first();

            if ($company) {
                return $company;
            }

            Session::forget('current_company_id');
        }

        $defaultCompany = $user->companies()
            ->wherePivot('is_default', true)
            ->wherePivot('is_active', true)
            ->first();

        if ($defaultCompany) {
            Session::put('current_company_id', $defaultCompany->id);

            return $defaultCompany;
        }

        $firstCompany = $user->companies()
            ->wherePivot('is_active', true)
            ->first();

        if ($firstCompany) {
            Session::put('current_company_id', $firstCompany->id);

            return $firstCompany;
        }

        return null;
    }

    public static function id(): ?int
    {
        return self::get()?->id;
    }

    public static function set(int $companyId): void
    {
        $user = self::user();

        if (! $user) {
            return;
        }

        $exists = $user->companies()
            ->where('companies.id', $companyId)
            ->wherePivot('is_active', true)
            ->exists();

        if (! $exists) {
            return;
        }

        Session::put('current_company_id', $companyId);
    }

    public static function clear(): void
    {
        Session::forget('current_company_id');
    }
}