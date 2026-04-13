<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hour_bank_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hour_bank_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->date('movement_date');
            $table->string('movement_type', 30);
            $table->integer('minutes')->default(0);
            $table->integer('balance_after')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
            $table->index('movement_date');
            $table->index('movement_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hour_bank_items');
    }
};