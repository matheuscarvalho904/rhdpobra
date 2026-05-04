<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeBank extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'positive_balance_hours',
        'negative_balance_hours',
        'net_balance_hours',
        'is_active',
        'last_movement_at',
        'settings',
    ];

    protected $casts = [
        'positive_balance_hours' => 'decimal:2',
        'negative_balance_hours' => 'decimal:2',
        'net_balance_hours' => 'decimal:2',
        'is_active' => 'boolean',
        'last_movement_at' => 'datetime',
        'settings' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(TimeBankMovement::class);
    }
}