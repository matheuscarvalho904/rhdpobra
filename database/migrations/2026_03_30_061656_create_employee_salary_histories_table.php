<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_salary_histories', function (Blueprint $table) {
    $table->id();

    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

    $table->enum('salary_type', ['monthly','hourly','daily']);
    $table->decimal('previous_salary', 12, 2)->nullable();
    $table->decimal('new_salary', 12, 2);

    $table->date('effective_date');

    $table->string('reason')->nullable();
    $table->text('notes')->nullable();

    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_histories');
    }
};
