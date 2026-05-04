<?php

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_banks', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Company::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Employee::class)->constrained()->cascadeOnDelete();

            $table->decimal('positive_balance_hours', 10, 2)->default(0);
            $table->decimal('negative_balance_hours', 10, 2)->default(0);
            $table->decimal('net_balance_hours', 10, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_movement_at')->nullable();

            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'employee_id']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_banks');
    }
};