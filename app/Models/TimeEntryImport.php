<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeEntryImport extends Model
{
    protected $fillable = [
        'company_id',
        'point_integration_id',
        'provider',
        'start_date',
        'end_date',
        'status',
        'total_records',
        'imported_records',
        'ignored_records',
        'error_message',
        'metadata',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function pointIntegration(): BelongsTo
    {
        return $this->belongsTo(PointIntegration::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TimeEntryImportItem::class);
    }
}