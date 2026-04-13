<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'document_type_id',
        'document_number',
        'issue_date',
        'expiration_date',
        'issuing_agency',
        'issuing_state',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'document_type_id' => 'integer',
            'issue_date' => 'date',
            'expiration_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }
}