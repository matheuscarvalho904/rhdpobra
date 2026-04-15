<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_role_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contract_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_shift_id')->nullable()->constrained()->nullOnDelete();

            $table->string('registration_number', 50);
            $table->unsignedInteger('contract_sequence')->default(1);

            $table->date('admission_date');
            $table->date('termination_date')->nullable();

            $table->decimal('salary', 14, 2)->default(0);

            $table->string('status', 30)->default('ativo');
            // ativo | em_aviso | desligado | suspenso | afastado

            $table->string('termination_reason', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_current')->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'is_current'], 'emp_contracts_employee_current_idx');
            $table->index(['company_id', 'branch_id', 'work_id'], 'emp_contracts_scope_idx');
            $table->index(['registration_number'], 'emp_contracts_registration_idx');
            $table->unique(['employee_id', 'contract_sequence'], 'emp_contracts_employee_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};