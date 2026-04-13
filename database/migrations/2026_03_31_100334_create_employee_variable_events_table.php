<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_variable_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_competency_id')->constrained('payroll_competencies')->cascadeOnDelete();
            $table->foreignId('payroll_event_id')->constrained('payroll_events')->cascadeOnDelete();

            $table->decimal('amount', 14, 2)->nullable();
            $table->decimal('percentage', 8, 4)->nullable();
            $table->decimal('quantity', 12, 4)->nullable();

            $table->string('reference', 255)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['employee_id', 'payroll_competency_id'],
                'emp_var_evt_emp_comp_idx'
            );

            $table->index(
                ['payroll_event_id', 'payroll_competency_id'],
                'emp_var_evt_evt_comp_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_variable_events');
    }
};