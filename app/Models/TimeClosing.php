<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeClosing extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'work_id',
        'period_start',
        'period_end',
        'status',
        'processed_at',
        'approved_at',
        'closed_at',
        'processed_by',
        'approved_by',
        'closed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'branch_id' => 'integer',
            'work_id' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'processed_at' => 'datetime',
            'approved_at' => 'datetime',
            'closed_at' => 'datetime',
            'processed_by' => 'integer',
            'approved_by' => 'integer',
            'closed_by' => 'integer',
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

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TimeClosingItem::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Aberto',
            'processing' => 'Processando',
            'reviewed' => 'Conferido',
            'approved' => 'Aprovado',
            'closed' => 'Fechado',
            'integrated_to_payroll' => 'Integrado à Folha',
            default => $this->status ?? '-',
        };
    }
}