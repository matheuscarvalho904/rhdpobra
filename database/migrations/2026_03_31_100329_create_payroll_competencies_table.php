<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_competencies', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELAÇÕES
            |--------------------------------------------------------------------------
            */
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DADOS PRINCIPAIS
            |--------------------------------------------------------------------------
            */
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');

            $table->string('type', 30)->default('monthly');
            // monthly | vacation | thirteenth | termination | advance

            $table->string('description');

            $table->date('period_start');
            $table->date('period_end');
            $table->date('payment_date')->nullable();

            $table->string('status', 30)->default('open');
            // open | processing | calculated | reviewed | closed | canceled

            $table->text('notes')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | REGRA DE UNICIDADE
            |--------------------------------------------------------------------------
            */
            $table->unique(
                ['company_id', 'branch_id', 'month', 'year', 'type'],
                'payroll_comp_unique'
            );

            /*
            |--------------------------------------------------------------------------
            | ÍNDICES
            |--------------------------------------------------------------------------
            */
            $table->index(['month', 'year'], 'payroll_comp_month_year_idx');
            $table->index('status', 'payroll_comp_status_idx');
            $table->index('type', 'payroll_comp_type_idx');
            $table->index(['company_id', 'branch_id'], 'payroll_comp_company_branch_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_competencies');
    }
};