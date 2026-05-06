<?php

namespace Database\Seeders;

use App\Models\PayrollEvent;
use App\Models\TimePayrollEventMapping;
use Illuminate\Database\Seeder;

class TimePayrollEventMappingSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        $mappings = [

            // HORAS EXTRAS
            'overtime_50' => 'HORA_EXTRA_50',
            'overtime_100' => 'HORA_EXTRA_100',

            // DSR SOBRE HE
            'dsr_overtime' => 'DSR_HE',

            // DESCONTOS
            'absence' => 'FALTA',
            'delay' => 'ATRASO',

            // 🔥 NOVO
            'dsr_absence' => 'DSR_FALTA',
        ];

        foreach ($mappings as $type => $eventCode) {

            $event = PayrollEvent::where('code', $eventCode)->first();

            if (! $event) {
                continue;
            }

            TimePayrollEventMapping::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'type' => $type,
                ],
                [
                    'payroll_event_id' => $event->id,
                    'is_active' => true,
                    'settings' => [],
                ]
            );
        }
    }
}