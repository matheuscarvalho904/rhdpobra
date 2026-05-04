<?php

use App\Models\WorkSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedule_days', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(WorkSchedule::class)->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('weekday'); // 0 domingo, 1 segunda ... 6 sábado
            $table->boolean('is_working_day')->default(true);

            $table->time('first_start')->nullable();
            $table->time('first_end')->nullable();
            $table->time('second_start')->nullable();
            $table->time('second_end')->nullable();

            $table->decimal('expected_hours', 8, 2)->default(0);

            $table->decimal('overtime_50_after_hours', 8, 2)->nullable();
            $table->decimal('overtime_100_after_hours', 8, 2)->nullable();

            $table->boolean('holiday_keeps_schedule')->default(false);
            $table->boolean('holiday_generates_overtime_100')->default(true);

            $table->unsignedSmallInteger('entry_tolerance_minutes')->default(5);
            $table->unsignedSmallInteger('exit_tolerance_minutes')->default(5);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->unique(['work_schedule_id', 'weekday']);
            $table->index(['weekday', 'is_working_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedule_days');
    }
};