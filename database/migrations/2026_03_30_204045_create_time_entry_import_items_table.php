<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entry_import_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('time_entry_import_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();

            $table->string('code')->nullable();
            $table->string('employee_name')->nullable();

            $table->date('entry_date')->nullable();

            $table->time('entry_1')->nullable();
            $table->time('exit_1')->nullable();
            $table->time('entry_2')->nullable();
            $table->time('exit_2')->nullable();

            $table->json('row_data')->nullable();
            $table->text('error_message')->nullable();

            $table->boolean('is_valid')->default(false);
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index('code');
            $table->index('entry_date');
            $table->index('is_valid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entry_import_items');
    }
};