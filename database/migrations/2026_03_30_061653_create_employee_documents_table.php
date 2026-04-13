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
        Schema::create('employee_documents', function (Blueprint $table) {
    $table->id();

    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
    $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();

    $table->string('document_number');
    $table->date('issue_date')->nullable();
    $table->date('expiration_date')->nullable();

    $table->string('issuing_agency')->nullable();
    $table->string('issuing_state', 2)->nullable();

    $table->text('notes')->nullable();
    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
