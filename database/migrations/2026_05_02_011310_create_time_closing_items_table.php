<?php

use App\Models\Employee;
use App\Models\TimeClosing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_closing_items', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(TimeClosing::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Employee::class)->nullable()->constrained()->nullOnDelete();

            $table->decimal('worked_hours', 10, 2)->default(0);
            $table->decimal('expected_hours', 10, 2)->default(0);
            $table->decimal('overtime_hours', 10, 2)->default(0);
            $table->decimal('delay_hours', 10, 2)->default(0);
            $table->decimal('absence_days', 10, 2)->default(0);

            $table->unsignedInteger('entries_count')->default(0);
            $table->unsignedInteger('days_with_entries')->default(0);

            $table->string('status')->default('processed'); // processed, warning, error
            $table->text('notes')->nullable();
            $table->json('daily_summary')->nullable();

            $table->timestamps();

            $table->unique(['time_closing_id', 'employee_id']);
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_closing_items');
        ///

        ///
    }
};