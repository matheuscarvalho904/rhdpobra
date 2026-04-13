<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_shift_days', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_shift_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('week_day');
            $table->time('start_time')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('expected_hours', 5, 2)->nullable();
            $table->boolean('is_off')->default(false);

            $table->timestamps();

            $table->unique(['work_shift_id', 'week_day']);
            $table->index('week_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_shift_days');
    }
};