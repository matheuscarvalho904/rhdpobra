<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollEvent extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'incidence_type',
        'calculation_type',
        'affects_inss',
        'affects_fgts',
        'affects_irrf',
        'affects_net',
        'is_active',
        'description',
    ];

    protected $casts = [
        'affects_inss' => 'boolean',
        'affects_fgts' => 'boolean',
        'affects_irrf' => 'boolean',
        'affects_net' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function fixedEvents()
    {
        return $this->hasMany(EmployeeFixedEvent::class);
    }

    public function variableEvents()
    {
        return $this->hasMany(EmployeeVariableEvent::class);
    }

    public function runItems()
    {
        return $this->hasMany(PayrollRunItem::class);
    }
}