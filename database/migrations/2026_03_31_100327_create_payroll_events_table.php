<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_events', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('type', 30);
            $table->string('incidence_type', 30)->nullable();
            $table->string('calculation_type', 30);

            $table->boolean('affects_inss')->default(false);
            $table->boolean('affects_fgts')->default(false);
            $table->boolean('affects_irrf')->default(false);
            $table->boolean('affects_net')->default(true);

            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('type');
            $table->index('calculation_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_events');
    }
};