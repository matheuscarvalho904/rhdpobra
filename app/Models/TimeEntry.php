<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'work_id',
        'employee_id',
        'attendance_occurrence_id',
        'entry_date',
        'entry_1',
        'exit_1',
        'entry_2',
        'exit_2',
        'expected_minutes',
        'worked_minutes',
        'overtime_minutes',
        'lateness_minutes',
        'absence_minutes',
        'night_minutes',
        'is_manual',
        'source',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'branch_id' => 'integer',
            'work_id' => 'integer',
            'employee_id' => 'integer',
            'attendance_occurrence_id' => 'integer',
            'entry_date' => 'date',
            'entry_1' => 'datetime:H:i:s',
            'exit_1' => 'datetime:H:i:s',
            'entry_2' => 'datetime:H:i:s',
            'exit_2' => 'datetime:H:i:s',
            'expected_minutes' => 'integer',
            'worked_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'lateness_minutes' => 'integer',
            'absence_minutes' => 'integer',
            'night_minutes' => 'integer',
            'is_manual' => 'boolean',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceOccurrence(): BelongsTo
    {
        return $this->belongsTo(AttendanceOccurrence::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeFromCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeFromBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeFromWork(Builder $query, int $workId): Builder
    {
        return $query->where('work_id', $workId);
    }

    public function scopeFromEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('entry_date', $date);
    }

    public function getFormattedSourceAttribute(): string
    {
        return match ($this->source) {
            'manual' => 'Manual',
            'import' => 'Importação',
            'integration' => 'Integração',
            default => $this->source ?? '-',
        };
    }
}