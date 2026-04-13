<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('trade_name')->nullable();
            $table->string('code', 30)->nullable()->unique();
            $table->string('document', 20)->nullable()->unique();
            $table->string('state_registration', 30)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->string('zip_code', 10)->nullable();
            $table->string('address')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('trade_name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};