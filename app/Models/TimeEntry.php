<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'time_entry_import_id',
        'time_entry_import_item_id',
        'provider',
        'source',
        'external_id',
        'external_employee_id',
        'entry_date',
        'entry_datetime',
        'type',
        'status',
        'raw_payload',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'entry_datetime' => 'datetime',
        'raw_payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (TimeEntry $record): void {
            $record->provider ??= 'manual';
            $record->source ??= 'manual';
            $record->status ??= 'valid';

            if (! $record->entry_date && $record->entry_datetime) {
                $record->entry_date = $record->entry_datetime->toDateString();
            }
        });

        static::updating(function (TimeEntry $record): void {
            if ($record->entry_datetime) {
                $record->entry_date = $record->entry_datetime->toDateString();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(TimeEntryImport::class, 'time_entry_import_id');
    }

    public function importItem(): BelongsTo
    {
        return $this->belongsTo(TimeEntryImportItem::class, 'time_entry_import_item_id');
    }
}
