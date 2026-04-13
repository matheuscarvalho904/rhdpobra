<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntryImportItem extends Model
{
    protected $fillable = [
        'time_entry_import_id',
        'employee_id',
        'registration_number',
        'employee_name',
        'entry_date',
        'entry_1',
        'exit_1',
        'entry_2',
        'exit_2',
        'row_data',
        'error_message',
        'is_valid',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'time_entry_import_id' => 'integer',
            'employee_id' => 'integer',
            'entry_date' => 'date',
            'entry_1' => 'datetime:H:i:s',
            'exit_1' => 'datetime:H:i:s',
            'entry_2' => 'datetime:H:i:s',
            'exit_2' => 'datetime:H:i:s',
            'row_data' => 'array',
            'is_valid' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function timeEntryImport(): BelongsTo
    {
        return $this->belongsTo(TimeEntryImport::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_valid', true);
    }

    public function scopeInvalid(Builder $query): Builder
    {
        return $query->where('is_valid', false);
    }
}