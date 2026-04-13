<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELAÇÕES
            |--------------------------------------------------------------------------
            */
            $table->foreignId('payroll_competency_id')
                ->constrained('payroll_competencies')
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('work_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | TIPO DE PROCESSAMENTO DA FOLHA
            |--------------------------------------------------------------------------
            | payroll_clt
            | payroll_apprentice
            | payroll_rpa
            | internship_payment
            | accounts_payable
            */
            $table->string('run_type', 30)
                ->default('payroll_clt')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | DADOS PRINCIPAIS
            |--------------------------------------------------------------------------
            */
            $table->string('description');
            $table->string('status', 30)->default('open');
            // open | processing | processed | closed | error

            /*
            |--------------------------------------------------------------------------
            | TOTAIS
            |--------------------------------------------------------------------------
            */
            $table->decimal('total_gross', 14, 2)->default(0);
            $table->decimal('total_discounts', 14, 2)->default(0);
            $table->decimal('total_net', 14, 2)->default(0);
            $table->decimal('total_fgts', 14, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | PROCESSAMENTO
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('processed_employees')->default(0);
            $table->text('error_message')->nullable();

            $table->timestamp('processed_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | OBSERVAÇÕES
            |--------------------------------------------------------------------------
            */
            $table->text('notes')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | ÍNDICES
            |--------------------------------------------------------------------------
            */
            $table->index('status');
            $table->index(['company_id', 'branch_id', 'work_id']);
            $table->index(['company_id', 'run_type']);
            $table->index(['payroll_competency_id', 'run_type']);

            /*
            |--------------------------------------------------------------------------
            | REGRA DE UNICIDADE
            |--------------------------------------------------------------------------
            */
            $table->unique(
                ['payroll_competency_id', 'company_id', 'branch_id', 'work_id', 'run_type'],
                'payroll_runs_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};