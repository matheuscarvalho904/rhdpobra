<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PayrollCompetency extends Model
{
    protected $table = 'payroll_competencies';

    protected $fillable = [
        'company_id',
        'branch_id',
        'type',

        'description',

        'month',
        'year',

        'period_start',
        'period_end',
        'payment_date',

        'status',
        'notes',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',

        'month' => 'integer',
        'year' => 'integer',

        'period_start' => 'date',
        'period_end' => 'date',
        'payment_date' => 'date',
    ];

    protected $appends = [
        'display_name',
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getDisplayNameAttribute(): string
    {
        if (! empty($this->description)) {
            return $this->description;
        }

        if ($this->month && $this->year) {
            return $this->getMonthName() . ' / ' . $this->year;
        }

        return 'Competência #' . $this->id;
    }

    public function getMonthName(): string
    {
        return match ((int) $this->month) {
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
            default => (string) $this->month,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT (REGRAS AUTOMÁTICAS)
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (PayrollCompetency $competency) {

            // 🔥 empresa automática
            if (! $competency->company_id && Auth::user()?->company_id) {
                $competency->company_id = Auth::user()->company_id;
            }

            // 🔥 período automático
            if ($competency->month && $competency->year) {
                $start = Carbon::createFromDate($competency->year, $competency->month, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();

                $competency->period_start ??= $start;
                $competency->period_end ??= $end;
            }

            // 🔥 descrição automática
            if (blank($competency->description)) {
                $typeLabel = self::getTypeLabel($competency->type);
                $competency->description = $typeLabel . ' - ' . $competency->getMonthName() . ' / ' . $competency->year;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public static function getTypeLabel(?string $type): string
    {
        return match ($type) {
            'monthly' => 'Folha Mensal',
            'vacation' => 'Férias',
            'thirteenth' => '13º Salário',
            'termination' => 'Rescisão',
            'advance' => 'Adiantamento',
            default => 'Competência',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function payrollRuns(): HasMany
    {
        return $this->hasMany(PayrollRun::class, 'payroll_competency_id');
    }

    public function employeeVariableEvents(): HasMany
    {
        return $this->hasMany(EmployeeVariableEvent::class, 'payroll_competency_id');
    }

    public function salaryAdvances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class, 'payroll_competency_id');
    }
}