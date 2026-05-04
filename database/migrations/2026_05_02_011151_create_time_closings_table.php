<?php

use App\Models\Company;
use App\Models\PayrollCompetency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_closings', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Company::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignIdFor(PayrollCompetency::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');

            $table->string('status')->default('draft');

            $table->unsignedInteger('employee_count')->default(0);

            $table->decimal('total_worked_hours', 10, 2)->default(0);
            $table->decimal('total_overtime_hours', 10, 2)->default(0);
            $table->decimal('total_overtime_50_hours', 10, 2)->default(0);
            $table->decimal('total_overtime_100_hours', 10, 2)->default(0);
            $table->decimal('total_delay_hours', 10, 2)->default(0);
            $table->decimal('total_absence_days', 10, 2)->default(0);

            $table->timestamp('processed_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'payroll_competency_id'], 'time_closings_company_competency_idx');
            $table->index(['company_id', 'start_date', 'end_date'], 'time_closings_company_period_idx');
            $table->index('status', 'time_closings_status_idx');

            $table->unique(
                ['company_id', 'payroll_competency_id', 'start_date', 'end_date'],
                'time_closings_unique_company_competency_period'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_closings');
    }
};