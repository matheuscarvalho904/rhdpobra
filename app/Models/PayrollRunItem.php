<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRunItem extends Model
{
    protected $table = 'payroll_run_items';

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'payroll_event_id',
        'code',
        'description',
        'type',
        'reference',
        'amount',
        'source',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollEvent(): BelongsTo
    {
        return $this->belongsTo(PayrollEvent::class);
    }

    public function isProvento(): bool
    {
        return $this->type === 'provento';
    }

    public function isDesconto(): bool
    {
        return $this->type === 'desconto';
    }

    public function isBase(): bool
    {
        return $this->type === 'base';
    }
}