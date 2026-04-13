<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_fixed_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_event_id')->constrained('payroll_events')->cascadeOnDelete();

            $table->decimal('amount', 14, 2)->nullable();
            $table->decimal('percentage', 8, 4)->nullable();
            $table->decimal('quantity', 12, 4)->nullable();

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'is_active']);
            $table->index(['payroll_event_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_fixed_events');
    }
};