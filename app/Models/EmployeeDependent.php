<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDependent extends Model
{
    protected $fillable = [
        'employee_id',
        'name',
        'relationship',
        'cpf',
        'birth_date',
        'is_ir_dependent',
        'is_family_allowance_dependent',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'birth_date' => 'date',
            'is_ir_dependent' => 'boolean',
            'is_family_allowance_dependent' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}