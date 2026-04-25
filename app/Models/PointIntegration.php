<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointIntegration extends Model
{
    protected $fillable = [
        'company_id',
        'provider',
        'name',
        'base_url',
        'token',
        'active',
        'last_sync_at',
        'settings',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_sync_at' => 'datetime',
        'settings' => 'array',
    ];

    protected $hidden = [
        'token',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isSolides(): bool
    {
        return $this->provider === 'solides';
    }
}