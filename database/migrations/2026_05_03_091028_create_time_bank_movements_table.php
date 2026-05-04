<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollCompetency;
use App\Models\TimeBank;
use App\Models\TimeClosing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_bank_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Company::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Employee::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(TimeBank::class)->constrained()->cascadeOnDelete();

            $table->foreignIdFor(TimeClosing::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(PayrollCompetency::class)->nullable()->constrained()->nullOnDelete();

            $table->string('type'); 
            // credit, debit, adjustment, payout, expiration

            $table->string('origin')->default('manual');
            // manual, time_closing, payroll, expiration, adjustment

            $table->decimal('hours', 10, 2);
            $table->decimal('balance_after', 10, 2)->default(0);

            $table->date('movement_date');
            $table->date('expires_at')->nullable();

            $table->string('status')->default('confirmed');
            // pending, confirmed, canceled

            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'movement_date']);
            $table->index(['time_closing_id', 'origin']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_bank_movements');
    }
};