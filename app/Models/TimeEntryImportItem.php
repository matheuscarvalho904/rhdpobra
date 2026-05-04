<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntryImportItem extends Model
{
    protected $fillable = [
        'time_entry_import_id',
        'employee_id',
        'provider',
        'external_id',
        'external_employee_id',
        'external_employee_name',
        'entry_date',
        'entry_datetime',
        'type',
        'status',
        'raw_payload',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'entry_datetime' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(TimeEntryImport::class, 'time_entry_import_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}