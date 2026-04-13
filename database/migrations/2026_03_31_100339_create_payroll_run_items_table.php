<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_run_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_run_id')
                ->constrained('payroll_runs')
                ->cascadeOnDelete();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->foreignId('payroll_event_id')
                ->nullable()
                ->constrained('payroll_events')
                ->nullOnDelete();

            $table->string('code', 30)->nullable();
            $table->string('description');

            // provento | desconto | base
            $table->string('type', 30);

            // base_salary | fixed_event | variable_event | salary_advance | calculation
            $table->string('source', 50)->nullable();

            // campos auxiliares para cálculo e conferência
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('reference', 12, 2)->default(0);
            $table->decimal('base_amount', 14, 2)->default(0);
            $table->decimal('unit_amount', 14, 2)->default(0);
            $table->decimal('amount', 14, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['payroll_run_id', 'employee_id'], 'pri_run_employee_idx');
            $table->index(['payroll_run_id', 'employee_id', 'type'], 'pri_run_employee_type_idx');
            $table->index(['employee_id', 'type'], 'pri_employee_type_idx');
            $table->index(['payroll_event_id'], 'pri_event_idx');
            $table->index(['code'], 'pri_code_idx');
            $table->index(['source'], 'pri_source_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_run_items');
    }
};