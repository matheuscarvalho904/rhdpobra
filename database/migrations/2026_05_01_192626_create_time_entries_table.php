<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\TimeEntryImport;
use App\Models\TimeEntryImportItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Company::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Employee::class)->nullable()->constrained()->nullOnDelete();

            $table->foreignIdFor(TimeEntryImport::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(TimeEntryImportItem::class)->nullable()->constrained()->nullOnDelete();

            $table->string('provider')->default('solides');
            $table->string('source')->default('api'); // api, manual, import

            $table->string('external_id')->nullable();
            $table->string('external_employee_id')->nullable();

            $table->date('entry_date');
            $table->dateTime('entry_datetime');

            $table->string('type')->nullable(); // entrada, saida, unknown
            $table->string('status')->default('valid'); // valid, pending, ignored, adjusted

            $table->json('raw_payload')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index(['company_id', 'entry_date']);
            $table->index(['employee_id', 'entry_date']);
            $table->index(['provider', 'external_employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};