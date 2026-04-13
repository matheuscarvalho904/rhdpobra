<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountsPayable extends Model
{
    protected $table = 'accounts_payable';

    protected $fillable = [
        'company_id',
        'branch_id',
        'work_id',
        'financial_category_id',
        'description',
        'amount',
        'due_date',
        'status',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function category()
    {
        return $this->belongsTo(FinancialCategory::class, 'financial_category_id');
    }
}