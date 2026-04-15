<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_terminations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_contract_id')->constrained('employee_contracts')->cascadeOnDelete();

            $table->date('termination_date');
            $table->date('last_worked_date')->nullable();

            $table->string('dismissal_type', 50)->nullable();
            $table->string('termination_reason', 100)->nullable();

            $table->string('notice_type', 30)->nullable();
            // worked | indemnified | home

            $table->date('notice_start_date')->nullable();
            $table->date('notice_end_date')->nullable();
            $table->unsignedInteger('notice_days')->default(0);
            $table->date('projected_end_date')->nullable();

            $table->string('reduction_type', 30)->default('none');
            // none | 2_hours_daily | 7_days_final

            $table->boolean('is_notice_projected')->default(false);

            $table->decimal('notice_amount', 14, 2)->default(0);
            $table->decimal('termination_amount', 14, 2)->default(0);

            $table->string('status', 30)->default('draft');
            // draft | in_progress | closed | cancelled

            $table->text('notes')->nullable();

            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'employee_contract_id'], 'emp_terminations_employee_contract_idx');
            $table->index(['status', 'notice_type'], 'emp_terminations_status_notice_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_terminations');
    }
};