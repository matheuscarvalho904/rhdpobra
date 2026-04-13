<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_types');
    }
};