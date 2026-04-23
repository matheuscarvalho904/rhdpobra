<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeEpiDelivery extends Model
{
    protected $fillable = [
        'employee_id',
        'company_id',
        'delivery_date',
        'status',
        'term_file_path',
        'term_file_name',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EmployeeEpiDeliveryItem::class, 'employee_epi_delivery_id');
    }
}