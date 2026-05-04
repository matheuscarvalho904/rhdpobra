<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Company::class)->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('code')->nullable();

            $table->string('schedule_type')->default('fixed');
            $table->boolean('is_active')->default(true);

            $table->boolean('works_on_holidays')->default(false);
            $table->boolean('uses_time_bank')->default(false);

            $table->unsignedSmallInteger('daily_tolerance_minutes')->default(10);
            $table->unsignedSmallInteger('monthly_tolerance_minutes')->default(0);

            $table->decimal('weekly_hours', 8, 2)->default(44);
            $table->decimal('monthly_hours', 8, 2)->default(220);

            $table->text('notes')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
    ////////

    /////
};