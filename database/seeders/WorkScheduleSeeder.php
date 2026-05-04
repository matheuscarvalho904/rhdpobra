<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleDay;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $companyIds = Company::query()->pluck('id');

        if ($companyIds->isEmpty()) {
            $companyIds = collect([null]);
        }

        foreach ($companyIds as $companyId) {
            $schedule = WorkSchedule::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'name' => 'Jornada Sólides 44h',
                ],
                [
                    'code' => 'SOLIDES-44H',
                    'schedule_type' => 'fixed',
                    'is_active' => true,
                    'works_on_holidays' => false,
                    'uses_time_bank' => true,
                    'daily_tolerance_minutes' => 10,
                    'monthly_tolerance_minutes' => 0,
                    'weekly_hours' => 44,
                    'monthly_hours' => 220,
                    'notes' => 'Jornada padrão: segunda a sexta 8h, sábado trabalhado como saldo/extra conforme regra Sólides, domingo sem jornada.',
                    'settings' => [
                        'standard' => 'solides',
                        'overtime_calculation' => 'period_balance',
                        'auto_discount_absence' => false,
                        'auto_discount_delay' => false,
                    ],
                ]
            );

            $days = [
                0 => [
                    'is_working_day' => false,
                    'first_start' => null,
                    'first_end' => null,
                    'second_start' => null,
                    'second_end' => null,
                    'expected_hours' => 0,
                    'holiday_keeps_schedule' => false,
                    'holiday_generates_overtime_100' => true,
                ],
                1 => [
                    'is_working_day' => true,
                    'first_start' => '07:00',
                    'first_end' => '11:00',
                    'second_start' => '13:00',
                    'second_end' => '17:00',
                    'expected_hours' => 8,
                    'holiday_keeps_schedule' => false,
                    'holiday_generates_overtime_100' => true,
                ],
                2 => [
                    'is_working_day' => true,
                    'first_start' => '07:00',
                    'first_end' => '11:00',
                    'second_start' => '13:00',
                    'second_end' => '17:00',
                    'expected_hours' => 8,
                    'holiday_keeps_schedule' => false,
                    'holiday_generates_overtime_100' => true,
                ],
                3 => [
                    'is_working_day' => true,
                    'first_start' => '07:00',
                    'first_end' => '11:00',
                    'second_start' => '13:00',
                    'second_end' => '17:00',
                    'expected_hours' => 8,
                    'holiday_keeps_schedule' => false,
                    'holiday_generates_overtime_100' => true,
                ],
                4 => [
                    'is_working_day' => true,
                    'first_start' => '07:00',
                    'first_end' => '11:00',
                    'second_start' => '13:00',
                    'second_end' => '17:00',
                    'expected_hours' => 8,
                    'holiday_keeps_schedule' => false,
                    'holiday_generates_overtime_100' => true,
                ],
                5 => [
                    'is_working_day' => true,
                    'first_start' => '07:00',
                    'first_end' => '11:00',
                    'second_start' => '13:00',
                    'second_end' => '17:00',
                    'expected_hours' => 8,
                    'holiday_keeps_schedule' => false,
                    'holiday_generates_overtime_100' => true,
                ],
                6 => [
                    'is_working_day' => false,
                    'first_start' => '07:00',
                    'first_end' => '11:00',
                    'second_start' => null,
                    'second_end' => null,
                    'expected_hours' => 0,
                    'holiday_keeps_schedule' => false,
                    'holiday_generates_overtime_100' => false,
                ],
            ];

            foreach ($days as $weekday => $data) {
                WorkScheduleDay::query()->updateOrCreate(
                    [
                        'work_schedule_id' => $schedule->id,
                        'weekday' => $weekday,
                    ],
                    [
                        'is_working_day' => $data['is_working_day'],
                        'first_start' => $data['first_start'],
                        'first_end' => $data['first_end'],
                        'second_start' => $data['second_start'],
                        'second_end' => $data['second_end'],
                        'expected_hours' => $data['expected_hours'],
                        'overtime_50_after_hours' => $weekday === 6 ? 0 : 8,
                        'overtime_100_after_hours' => null,
                        'holiday_keeps_schedule' => $data['holiday_keeps_schedule'],
                        'holiday_generates_overtime_100' => $data['holiday_generates_overtime_100'],
                        'entry_tolerance_minutes' => 5,
                        'exit_tolerance_minutes' => 5,
                        'settings' => [
                            'standard' => 'solides',
                        ],
                    ]
                );
            }
        }
    }
}