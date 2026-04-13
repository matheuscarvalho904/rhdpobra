<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_competency_id')->nullable()->constrained('payroll_competencies')->nullOnDelete();

            $table->date('advance_date');
            $table->decimal('amount', 14, 2)->default(0);

            $table->string('status', 30)->default('draft');
            $table->string('payment_method', 30)->default('pix');

            $table->string('pix_key_type', 30)->nullable();
            $table->string('pix_key')->nullable();
            $table->string('pix_holder_name')->nullable();
            $table->string('pix_holder_document', 30)->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('integrated_payroll_at')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'advance_date'], 'sal_adv_emp_date_idx');
            $table->index(['status', 'advance_date'], 'sal_adv_status_date_idx');
            $table->index(['payroll_competency_id', 'status'], 'sal_adv_comp_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
    }
};