<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEpiDeliveryItem extends Model
{
    protected $fillable = [
        'employee_epi_delivery_id',
        'epi_id',
        'quantity',
        'expected_return_date',
        'returned_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expected_return_date' => 'date',
        'returned_at' => 'date',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(EmployeeEpiDelivery::class, 'employee_epi_delivery_id');
    }

    public function epi(): BelongsTo
    {
        return $this->belongsTo(Epi::class, 'epi_id');
    }
}