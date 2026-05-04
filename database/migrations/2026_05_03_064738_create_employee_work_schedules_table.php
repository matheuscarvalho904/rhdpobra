<?php

use App\Models\Employee;
use App\Models\WorkSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_work_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Employee::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(WorkSchedule::class)->constrained()->cascadeOnDelete();

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->text('notes')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'start_date', 'end_date']);
            $table->index(['work_schedule_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_work_schedules');
    }
};