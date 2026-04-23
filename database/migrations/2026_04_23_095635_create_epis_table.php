<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->nullable();
            $table->string('ca_number')->nullable();
            $table->integer('validity_days')->nullable();
            $table->boolean('requires_return')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'name'], 'epi_company_name_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epis');
    }
};