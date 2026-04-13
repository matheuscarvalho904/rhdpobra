<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    protected $fillable = [
        'holiday_type_id',
        'name',
        'holiday_date',
        'state',
        'city',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'holiday_type_id' => 'integer',
            'holiday_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function holidayType(): BelongsTo
    {
        return $this->belongsTo(HolidayType::class);
    }
}