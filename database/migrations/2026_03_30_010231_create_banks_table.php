<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();

            $table->string('code', 10)->nullable();
            $table->string('name');
            $table->string('full_name')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('code');
            $table->index('name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};