<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();

            $table->foreignId('holiday_type_id')->constrained()->restrictOnDelete();

            $table->string('name');
            $table->date('holiday_date');
            $table->string('state', 2)->nullable();
            $table->string('city')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('holiday_date');
            $table->index(['state', 'city']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};