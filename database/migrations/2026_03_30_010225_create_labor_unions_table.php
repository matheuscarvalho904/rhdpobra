<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labor_unions', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code', 30)->nullable();
            $table->string('document', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('code');
            $table->index('name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labor_unions');
    }
};