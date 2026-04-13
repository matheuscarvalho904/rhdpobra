<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAccessScope extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'work_id',
        'department_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'company_id' => 'integer',
            'branch_id' => 'integer',
            'work_id' => 'integer',
            'department_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}