<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->foreignId('attendance_occurrence_id')
                ->nullable()
                ->constrained('attendance_occurrences')
                ->nullOnDelete();

            $table->date('entry_date');

            $table->time('entry_1')->nullable();
            $table->time('exit_1')->nullable();
            $table->time('entry_2')->nullable();
            $table->time('exit_2')->nullable();

            $table->integer('expected_minutes')->default(0);
            $table->integer('worked_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->integer('lateness_minutes')->default(0);
            $table->integer('absence_minutes')->default(0);
            $table->integer('night_minutes')->default(0);

            $table->boolean('is_manual')->default(true);
            $table->string('source', 30)->default('manual');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['employee_id', 'entry_date']);
            $table->index(['company_id', 'branch_id', 'work_id']);
            $table->index('entry_date');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};