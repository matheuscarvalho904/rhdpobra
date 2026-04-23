<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_epi_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->date('delivery_date');
            $table->string('status', 30)->default('open');

            $table->string('term_file_path')->nullable();
            $table->string('term_file_name')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'delivery_date'], 'epi_delivery_emp_date_idx');
            $table->index(['company_id', 'status'], 'epi_delivery_comp_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_epi_deliveries');
    }
};