<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_external_mappings', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Employee::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('provider')->default('solides');

            $table->string('external_employee_id')->nullable();
            $table->string('external_code')->nullable();
            $table->string('external_name')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'provider']);
            $table->index(['provider', 'external_employee_id']);
            $table->index(['provider', 'external_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_external_mappings');
    }
};