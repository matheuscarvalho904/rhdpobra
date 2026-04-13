<?php

namespace Database\Seeders;

use App\Models\WorkShift;
use App\Models\WorkShiftDay;
use Illuminate\Database\Seeder;

class WorkShiftDaySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdministrativo44h();
        $this->seedObra44h();
        $this->seed12x36Diurno();
        $this->seed12x36Noturno();
        $this->seed6x1Operacional();
        $this->seed5x2Administrativo();
        $this->seedTurnoNoturno();
    }

    private function upsertDay(
        WorkShift $shift,
        int $weekDay,
        ?string $startTime,
        ?string $breakStart,
        ?string $breakEnd,
        ?string $endTime,
        ?float $expectedHours,
        bool $isOff
    ): void {
        WorkShiftDay::updateOrCreate(
            [
                'work_shift_id' => $shift->id,
                'week_day' => $weekDay,
            ],
            [
                'start_time' => $startTime,
                'break_start' => $breakStart,
                'break_end' => $breakEnd,
                'end_time' => $endTime,
                'expected_hours' => $expectedHours,
                'is_off' => $isOff,
            ]
        );
    }

    private function seedAdministrativo44h(): void
    {
        $shift = WorkShift::where('code', 'ADM-44H')->first();
        if (! $shift) return;

        for ($day = 1; $day <= 5; $day++) {
            $this->upsertDay($shift, $day, '07:00:00', '11:00:00', '13:00:00', '17:48:00', 8.8, false);
        }

        $this->upsertDay($shift, 6, '07:00:00', null, null, '11:00:00', 4.0, false);
        $this->upsertDay($shift, 0, null, null, null, null, null, true);
    }

    private function seedObra44h(): void
    {
        $shift = WorkShift::where('code', 'OBRA-44H')->first();
        if (! $shift) return;

        for ($day = 1; $day <= 5; $day++) {
            $this->upsertDay($shift, $day, '07:00:00', '11:00:00', '13:00:00', '16:48:00', 8.8, false);
        }

        $this->upsertDay($shift, 6, '07:00:00', null, null, '11:00:00', 4.0, false);
        $this->upsertDay($shift, 0, null, null, null, null, null, true);
    }

    private function seed12x36Diurno(): void
    {
        $shift = WorkShift::where('code', '12X36-DIA')->first();
        if (! $shift) return;

        foreach ([0, 1, 2, 3, 4, 5, 6] as $day) {
            $this->upsertDay($shift, $day, '06:00:00', '12:00:00', '13:00:00', '18:00:00', 11.0, false);
        }
    }

    private function seed12x36Noturno(): void
    {
        $shift = WorkShift::where('code', '12X36-NOITE')->first();
        if (! $shift) return;

        foreach ([0, 1, 2, 3, 4, 5, 6] as $day) {
            $this->upsertDay($shift, $day, '18:00:00', '00:00:00', '01:00:00', '06:00:00', 11.0, false);
        }
    }

    private function seed6x1Operacional(): void
    {
        $shift = WorkShift::where('code', '6X1-OPER')->first();
        if (! $shift) return;

        for ($day = 1; $day <= 6; $day++) {
            $this->upsertDay($shift, $day, '07:00:00', '11:00:00', '13:00:00', '15:20:00', 7.33, false);
        }

        $this->upsertDay($shift, 0, null, null, null, null, null, true);
    }

    private function seed5x2Administrativo(): void
    {
        $shift = WorkShift::where('code', '5X2-ADM')->first();
        if (! $shift) return;

        for ($day = 1; $day <= 5; $day++) {
            $this->upsertDay($shift, $day, '07:30:00', '11:30:00', '13:00:00', '17:30:00', 8.0, false);
        }

        $this->upsertDay($shift, 6, null, null, null, null, null, true);
        $this->upsertDay($shift, 0, null, null, null, null, null, true);
    }

    private function seedTurnoNoturno(): void
    {
        $shift = WorkShift::where('code', 'NOTURNO')->first();
        if (! $shift) return;

        for ($day = 1; $day <= 5; $day++) {
            $this->upsertDay($shift, $day, '22:00:00', '02:00:00', '03:00:00', '06:48:00', 7.8, false);
        }

        $this->upsertDay($shift, 6, '22:00:00', null, null, '02:00:00', 4.0, false);
        $this->upsertDay($shift, 0, null, null, null, null, null, true);
    }
}