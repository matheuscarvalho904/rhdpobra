<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();

            $table->decimal('salary_base', 15, 2)->default(0);

            $table->decimal('total_gross', 15, 2)->default(0);
            $table->decimal('deduction_total', 15, 2)->default(0);
            $table->decimal('net_total', 15, 2)->default(0);

            $table->decimal('base_inss', 15, 2)->default(0);
            $table->decimal('inss', 15, 2)->default(0);

            $table->decimal('base_fgts', 15, 2)->default(0);
            $table->decimal('fgts', 15, 2)->default(0);

            $table->decimal('base_irrf', 15, 2)->default(0);
            $table->decimal('irrf', 15, 2)->default(0);

            $table->timestamp('printed_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->string('file_path')->nullable();

            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id'], 'unique_payslip_per_employee_run');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};