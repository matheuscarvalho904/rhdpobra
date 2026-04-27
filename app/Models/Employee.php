<?php

namespace App\Models;

use App\Services\ContractProcessingRuleService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeExternalMapping;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        /*
        |--------------------------------------------------------------------------
        | RELAÇÕES
        |--------------------------------------------------------------------------
        */
        'company_id',
        'branch_id',
        'work_id',
        'department_id',
        'cost_center_id',
        'job_role_id',
        'cbo_code_id',
        'labor_union_id',
        'contract_type_id',
        'bank_id',
        'work_shift_id',

        /*
        |--------------------------------------------------------------------------
        | DADOS CADASTRAIS
        |--------------------------------------------------------------------------
        */
        'code',
        'name',
        'social_name',
        'cpf',
        'rg',
        'rg_issuer',
        'pis',
        'ctps',
        'ctps_series',
        'birth_date',
        'gender',
        'marital_status',
        'nationality',
        'birthplace',
        'mother_name',
        'father_name',

        /*
        |--------------------------------------------------------------------------
        | CONTATO
        |--------------------------------------------------------------------------
        */
        'email',
        'phone',
        'mobile',

        /*
        |--------------------------------------------------------------------------
        | ENDEREÇO
        |--------------------------------------------------------------------------
        */
        'zip_code',
        'address',
        'number',
        'complement',
        'district',
        'city',
        'state',

        /*
        |--------------------------------------------------------------------------
        | DADOS FUNCIONAIS / CONTRATUAIS
        |--------------------------------------------------------------------------
        */
        'admission_date',
        'termination_date',
        'status',
        'is_active',
        'salary',
        'salary_advance_amount',
        'payment_method',

        /*
        |--------------------------------------------------------------------------
        | DADOS BANCÁRIOS
        |--------------------------------------------------------------------------
        */
        'bank_agency',
        'bank_account',
        'bank_account_digit',
        'bank_account_type',

        /*
        |--------------------------------------------------------------------------
        | PIX
        |--------------------------------------------------------------------------
        */
        'pix_key_type',
        'pix_key',
        'pix_holder_name',
        'pix_holder_document',

        /*
        |--------------------------------------------------------------------------
        | REGRAS DE PROCESSAMENTO
        |--------------------------------------------------------------------------
        */
        'processing_type',
        'generates_payroll',
        'generates_accounts_payable',
        'allows_payslip',
        'has_fgts',
        'has_inss',
        'has_irrf',
        'fgts_rate',
        'inss_optional',
        'with_inss',

        'has_experience_period',
        'experience_model',
        'experience_days_first',
        'experience_days_second',
        'experience_total_days',
        'experience_start_date',
        'experience_end_date',

        /*
        |--------------------------------------------------------------------------
        | CONTROLE
        |--------------------------------------------------------------------------
        */
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        /*
        |--------------------------------------------------------------------------
        | DATAS
        |--------------------------------------------------------------------------
        */
        'birth_date' => 'date',
        'admission_date' => 'date',
        'termination_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',

        /*
        |--------------------------------------------------------------------------
        | NUMÉRICOS
        |--------------------------------------------------------------------------
        */
        'salary' => 'decimal:2',
        'salary_advance_amount' => 'decimal:2',
        'fgts_rate' => 'decimal:2',

        /*
        |--------------------------------------------------------------------------
        | BOOLEANOS
        |--------------------------------------------------------------------------
        */
        'is_active' => 'boolean',
        'generates_payroll' => 'boolean',
        'generates_accounts_payable' => 'boolean',
        'allows_payslip' => 'boolean',
        'has_fgts' => 'boolean',
        'has_inss' => 'boolean',
        'has_irrf' => 'boolean',
        'inss_optional' => 'boolean',
        'with_inss' => 'boolean',

        'has_experience_period' => 'boolean',
        'experience_days_first' => 'integer',
        'experience_days_second' => 'integer',
        'experience_total_days' => 'integer',
        'experience_start_date' => 'date',
        'experience_end_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'active',
        'is_active' => true,
        'salary' => 0,
        'salary_advance_amount' => 0,
        'processing_type' => 'payroll_clt',
        'generates_payroll' => true,
        'generates_accounts_payable' => false,
        'allows_payslip' => true,
        'has_fgts' => true,
        'has_inss' => true,
        'has_irrf' => true,
        'fgts_rate' => 8.00,
        'inss_optional' => false,
        'with_inss' => true,
    ];

    protected $appends = [
        'display_document',
        'display_name',
        'formatted_pix_key',
        'formatted_pix_holder_document',
        'bank_account_full',
    ];

    protected static function booted(): void
    {
        static::creating(function (Employee $employee) {
            if (! $employee->company_id && Auth::user()?->company_id) {
                $employee->company_id = Auth::user()->company_id;
            }

            if (blank($employee->code)) {
                $employee->code = static::generateNextCode($employee->company_id);
            }

            $employee->applyContractRules();

            if (! $employee->created_by && Auth::id()) {
                $employee->created_by = Auth::id();
            }

            if (! $employee->updated_by && Auth::id()) {
                $employee->updated_by = Auth::id();
            }
        });

        static::updating(function (Employee $employee) {
            if ($employee->isDirty('contract_type_id')) {
                $employee->applyContractRules();
            }

            if (Auth::id()) {
                $employee->updated_by = Auth::id();
            }
        });
    }

    public static function generateNextCode(?int $companyId = null): string
    {
        $query = static::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $lastCode = $query
            ->whereNotNull('code')
            ->orderByDesc('id')
            ->value('code');

        $nextNumber = 1;

        if ($lastCode && preg_match('/(\d+)$/', (string) $lastCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return 'MAT-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function applyContractRules(): void
    {
        $rules = ContractProcessingRuleService::getByContractTypeId($this->contract_type_id);

        $this->processing_type = $rules['processing_type'] ?? 'payroll_clt';
        $this->generates_payroll = (bool) ($rules['generates_payroll'] ?? true);
        $this->generates_accounts_payable = (bool) ($rules['generates_accounts_payable'] ?? false);
        $this->allows_payslip = (bool) ($rules['allows_payslip'] ?? true);

        $this->has_fgts = (bool) ($rules['has_fgts'] ?? false);
        $this->has_inss = (bool) ($rules['has_inss'] ?? true);
        $this->has_irrf = (bool) ($rules['has_irrf'] ?? true);

        $this->fgts_rate = $this->has_fgts
            ? (isset($rules['fgts_rate']) && $rules['fgts_rate'] !== null
                ? (float) $rules['fgts_rate']
                : 8.00)
            : 0.00;

        $this->inss_optional = (bool) ($rules['inss_optional'] ?? false);

        if (! $this->inss_optional) {
            $this->with_inss = (bool) ($rules['with_inss'] ?? true);
        } elseif ($this->with_inss === null) {
            $this->with_inss = true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isClt(): bool
    {
        return $this->processing_type === 'payroll_clt';
    }

    public function isRpa(): bool
    {
        return $this->processing_type === 'payroll_rpa';
    }

    public function isInternship(): bool
    {
        return $this->processing_type === 'internship_payment';
    }

    public function isAccountsPayable(): bool
    {
        return $this->processing_type === 'accounts_payable';
    }

    public function usesInss(): bool
    {
        return (bool) $this->has_inss && (bool) $this->with_inss;
    }

    public function hasPix(): bool
    {
        return ! empty($this->pix_key);
    }

    public function hasPayrollEligibleContract(): bool
    {
        $contract = $this->currentContract;

        if (! $contract) {
            return false;
        }

        return in_array($contract->status, ['ativo', 'em_aviso'], true);
    }

    public function currentContractStatus(): ?string
    {
        return $this->currentContract?->status;
    }

    public function isInNotice(): bool
    {
        return $this->currentContractStatus() === 'em_aviso';
    }

    public function isTerminatedByContract(): bool
    {
        return $this->currentContractStatus() === 'desligado';
    }

    public function getDisplayDocumentAttribute(): ?string
    {
        return $this->cpf ?: $this->pix_holder_document;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->social_name ?: (string) $this->name;
    }

    public function getFormattedPixKeyAttribute(): ?string
    {
        if (blank($this->pix_key)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $this->pix_key);

        return match ($this->pix_key_type) {
            'cpf' => strlen($digits) === 11
                ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits)
                : $this->pix_key,

            'cnpj' => strlen($digits) === 14
                ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits)
                : $this->pix_key,

            'phone' => strlen($digits) === 11
                ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $digits)
                : (strlen($digits) === 10
                    ? preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $digits)
                    : $this->pix_key),

            default => $this->pix_key,
        };
    }

    public function getFormattedPixHolderDocumentAttribute(): ?string
    {
        if (blank($this->pix_holder_document)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $this->pix_holder_document);

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        }

        if (strlen($digits) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
        }

        return $this->pix_holder_document;
    }

    public function getBankAccountFullAttribute(): ?string
    {
        if (blank($this->bank_account)) {
            return null;
        }

        return $this->bank_account . ($this->bank_account_digit ? '-' . $this->bank_account_digit : '');
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

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(JobRole::class);
    }

    public function cboCode(): BelongsTo
    {
        return $this->belongsTo(CboCode::class);
    }

    public function laborUnion(): BelongsTo
    {
        return $this->belongsTo(LaborUnion::class);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class);
    }

    public function currentContract(): HasOne
    {
        return $this->hasOne(EmployeeContract::class)
            ->where('is_current', true);
    }

    public function terminations(): HasMany
    {
        return $this->hasMany(EmployeeTermination::class);
    }

    public function files(): HasMany
{
    return $this->hasMany(EmployeeFile::class);
}

public function epiDeliveries(): HasMany
{
    return $this->hasMany(\App\Models\EmployeeEpiDelivery::class);
}
public function externalMappings(): HasMany
{
    return $this->hasMany(EmployeeExternalMapping::class);
}

public function solidesMapping(): HasOne
{
    return $this->hasOne(EmployeeExternalMapping::class)
        ->where('provider', 'solides');
}

}