<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELAÇÕES
            |--------------------------------------------------------------------------
            */
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_role_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cbo_code_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('labor_union_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contract_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_shift_id')->nullable()->constrained()->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DADOS CADASTRAIS
            |--------------------------------------------------------------------------
            */
            $table->string('code', 30)->nullable();
            $table->string('name');
            $table->string('social_name')->nullable();

            $table->string('cpf', 14)->nullable();
            $table->string('rg', 30)->nullable();
            $table->string('rg_issuer', 20)->nullable();
            $table->string('pis', 20)->nullable();
            $table->string('ctps', 30)->nullable();
            $table->string('ctps_series', 20)->nullable();

            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('birthplace', 100)->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_name')->nullable();

            /*
            |--------------------------------------------------------------------------
            | CONTATO
            |--------------------------------------------------------------------------
            */
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | ENDEREÇO
            |--------------------------------------------------------------------------
            */
            $table->string('zip_code', 10)->nullable();
            $table->string('address')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();

            /*
            |--------------------------------------------------------------------------
            | DADOS FUNCIONAIS / CONTRATUAIS
            |--------------------------------------------------------------------------
            */
            $table->date('admission_date')->nullable();
            $table->date('termination_date')->nullable();

            $table->string('status', 30)->default('active');
            $table->boolean('is_active')->default(true);

            $table->decimal('salary', 14, 2)->default(0);
            $table->decimal('salary_advance_amount', 14, 2)->default(0);
            $table->string('payment_method', 30)->nullable();

            /*
            |--------------------------------------------------------------------------
            | DADOS BANCÁRIOS
            |--------------------------------------------------------------------------
            */
            $table->string('bank_agency', 20)->nullable();
            $table->string('bank_account', 30)->nullable();
            $table->string('bank_account_digit', 10)->nullable();
            $table->string('bank_account_type', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | PIX
            |--------------------------------------------------------------------------
            */
            $table->string('pix_key_type', 20)->nullable();
            $table->string('pix_key')->nullable();
            $table->string('pix_holder_name')->nullable();
            $table->string('pix_holder_document', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | REGRAS DE PROCESSAMENTO
            |--------------------------------------------------------------------------
            */
            $table->string('processing_type', 30)->default('payroll_clt');
            $table->boolean('generates_payroll')->default(true);
            $table->boolean('generates_accounts_payable')->default(false);
            $table->boolean('allows_payslip')->default(true);

            $table->boolean('has_fgts')->default(true);
            $table->boolean('has_inss')->default(true);
            $table->boolean('has_irrf')->default(true);

            $table->decimal('fgts_rate', 5, 2)->default(8.00);

            $table->boolean('inss_optional')->default(false);
            $table->boolean('with_inss')->default(true);

            /*
            |--------------------------------------------------------------------------
            | CONTROLE
            |--------------------------------------------------------------------------
            */
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | ÍNDICES
            |--------------------------------------------------------------------------
            */
            $table->unique(['company_id', 'code'], 'employees_company_code_unique');

            $table->index('name');
            $table->index('cpf');
            $table->index('status');
            $table->index('processing_type');
            $table->index(['company_id', 'branch_id', 'work_id'], 'employees_company_branch_work_idx');
            $table->index(['company_id', 'is_active'], 'employees_company_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};