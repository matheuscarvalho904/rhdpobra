<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkShift extends Model
{
    protected $fillable = [
        'name',
        'code',
        'weekly_workload',
        'monthly_workload',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weekly_workload' => 'integer',
            'monthly_workload' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function days(): HasMany
    {
        return $this->hasMany(WorkShiftDay::class);
    }
}