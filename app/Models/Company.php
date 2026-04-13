<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'trade_name',
        'code',
        'document',
        'state_registration',
        'phone',
        'email',
        'zip_code',
        'address',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function costCenters(): HasMany
    {
        return $this->hasMany(CostCenter::class);
    }

    public function userAccessScopes(): HasMany
    {
        return $this->hasMany(UserAccessScope::class);
    }
}