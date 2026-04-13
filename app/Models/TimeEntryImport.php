<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeEntryImport extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'work_id',
        'imported_by',
        'file_name',
        'status',
        'imported_rows',
        'valid_rows',
        'invalid_rows',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'branch_id' => 'integer',
            'work_id' => 'integer',
            'imported_by' => 'integer',
            'imported_rows' => 'integer',
            'valid_rows' => 'integer',
            'invalid_rows' => 'integer',
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

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TimeEntryImportItem::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', ['completed', 'completed_with_errors']);
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'completed' => 'Concluído',
            'completed_with_errors' => 'Concluído com Erros',
            'failed' => 'Falhou',
            default => $this->status ?? '-',
        };
    }
}