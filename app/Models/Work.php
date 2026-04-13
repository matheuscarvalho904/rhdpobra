<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Work extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'code',
        'client_name',
        'city',
        'state',
        'start_date',
        'expected_end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'branch_id' => 'integer',
            'start_date' => 'date',
            'expected_end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function userAccessScopes(): HasMany
    {
        return $this->hasMany(UserAccessScope::class);
    }
}