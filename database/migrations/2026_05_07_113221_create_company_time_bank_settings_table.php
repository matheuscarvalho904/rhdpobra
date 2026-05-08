<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_time_bank_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Company::class)->constrained()->cascadeOnDelete();

            $table->boolean('enabled')->default(false);

            $table->string('default_overtime_destination')->default('payroll');
            // payroll, time_bank, mixed

            $table->decimal('monthly_bank_limit', 10, 2)->default(20);

            $table->boolean('excess_to_payroll')->default(true);
            $table->boolean('compensate_delays_with_balance')->default(true);
            $table->boolean('allow_negative_balance')->default(false);

            $table->integer('expiration_days')->default(180);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_time_bank_settings');
    }
};