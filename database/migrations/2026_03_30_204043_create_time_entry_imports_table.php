<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entry_imports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('imported_by')->constrained('users')->cascadeOnDelete();

            $table->string('file_name');
            $table->string('status', 30)->default('pending');

            $table->integer('imported_rows')->default(0);
            $table->integer('valid_rows')->default(0);
            $table->integer('invalid_rows')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'branch_id', 'work_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entry_imports');
    }
};