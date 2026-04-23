<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_epi_delivery_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_epi_delivery_id')
                ->constrained('employee_epi_deliveries')
                ->cascadeOnDelete();

            $table->foreignId('epi_id')
                ->constrained('epis')
                ->cascadeOnDelete();

            $table->integer('quantity')->default(1);
            $table->date('expected_return_date')->nullable();
            $table->date('returned_at')->nullable();
            $table->string('status', 30)->default('delivered');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(
                ['employee_epi_delivery_id', 'status'],
                'epi_delivery_items_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_epi_delivery_items');
    }
};