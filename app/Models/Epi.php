<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Epi extends Model
{
    protected $table = 'epis';

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'ca_number',
        'validity_days',
        'requires_return',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'validity_days' => 'integer',
        'requires_return' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(EmployeeEpiDeliveryItem::class, 'epi_id');
    }
}