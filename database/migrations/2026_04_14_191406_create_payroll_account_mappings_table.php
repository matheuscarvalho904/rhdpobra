<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_account_mappings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payroll_event_id')->nullable()->constrained()->nullOnDelete();

            $table->string('event_code', 50)->nullable();
            $table->string('event_type', 30)->nullable();
            // provento | desconto | informativo | resumo | encargo

            $table->string('debit_account', 50)->nullable();
            $table->string('credit_account', 50)->nullable();

            $table->string('history_template', 255)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['company_id', 'branch_id', 'work_id'], 'pam_company_branch_work_idx');
            $table->index(['payroll_event_id', 'event_code'], 'pam_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_account_mappings');
    }
};