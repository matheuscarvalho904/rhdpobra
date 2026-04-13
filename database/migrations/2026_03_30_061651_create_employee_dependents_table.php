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
        Schema::create('employee_dependents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('employee_dependents', function (Blueprint $table) {
    $table->id();

    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

    $table->string('name');
    $table->string('relationship');
    $table->string('cpf')->nullable();
    $table->date('birth_date')->nullable();

    $table->boolean('is_ir_dependent')->default(false);
    $table->boolean('is_family_allowance_dependent')->default(false);

    $table->text('notes')->nullable();
    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }
};
