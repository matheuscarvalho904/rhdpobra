<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_closing_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('time_closing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->integer('expected_minutes')->default(0);
            $table->integer('worked_minutes')->default(0);
            $table->integer('overtime_50_minutes')->default(0);
            $table->integer('overtime_100_minutes')->default(0);
            $table->integer('lateness_minutes')->default(0);
            $table->integer('absence_minutes')->default(0);
            $table->integer('night_minutes')->default(0);
            $table->integer('hour_bank_credit_minutes')->default(0);
            $table->integer('hour_bank_debit_minutes')->default(0);
            $table->integer('dsr_minutes')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['time_closing_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_closing_items');
    }
};