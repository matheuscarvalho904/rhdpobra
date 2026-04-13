<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_roles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cbo_code_id')->nullable()->constrained('cbo_codes')->nullOnDelete();

            $table->string('name');
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('code');
            $table->index('name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_roles');
    }
};