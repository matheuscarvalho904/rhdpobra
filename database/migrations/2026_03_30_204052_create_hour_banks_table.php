<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hour_banks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->integer('balance_minutes')->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique('employee_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hour_banks');
    }
};