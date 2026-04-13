<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HourBank extends Model
{
    protected $fillable = [
        'employee_id',
        'balance_minutes',
        'last_calculated_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'balance_minutes' => 'integer',
            'last_calculated_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(HourBankItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getFormattedBalanceAttribute(): string
    {
        $minutes = abs($this->balance_minutes);
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;
        $signal = $this->balance_minutes < 0 ? '-' : '';

        return sprintf('%s%02d:%02d', $signal, $hours, $remainingMinutes);
    }
}