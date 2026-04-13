<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HourBankItem extends Model
{
    protected $fillable = [
        'hour_bank_id',
        'employee_id',
        'reference_type',
        'reference_id',
        'movement_date',
        'movement_type',
        'minutes',
        'balance_after',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'hour_bank_id' => 'integer',
            'employee_id' => 'integer',
            'reference_id' => 'integer',
            'movement_date' => 'date',
            'minutes' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function hourBank(): BelongsTo
    {
        return $this->belongsTo(HourBank::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('movement_type', 'credit');
    }

    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('movement_type', 'debit');
    }

    public function getFormattedMovementTypeAttribute(): string
    {
        return match ($this->movement_type) {
            'credit' => 'Crédito',
            'debit' => 'Débito',
            'adjustment' => 'Ajuste',
            'expiration' => 'Expiração',
            default => $this->movement_type ?? '-',
        };
    }

    public function getFormattedMinutesAttribute(): string
    {
        $minutes = abs($this->minutes);
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;
        $signal = $this->minutes < 0 ? '-' : '';

        return sprintf('%s%02d:%02d', $signal, $hours, $remainingMinutes);
    }

    public function getFormattedBalanceAfterAttribute(): string
    {
        $minutes = abs($this->balance_after);
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;
        $signal = $this->balance_after < 0 ? '-' : '';

        return sprintf('%s%02d:%02d', $signal, $hours, $remainingMinutes);
    }
}