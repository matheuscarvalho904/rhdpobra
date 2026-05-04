<?php

use App\Models\Company;
use App\Models\PayrollEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_payroll_event_mappings', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Company::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type'); 
            // overtime_50, overtime_100, delay, absence, dsr_overtime, night_additional

            $table->foreignIdFor(PayrollEvent::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'type']);
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_payroll_event_mappings');
    }

    //mapear teste//
};