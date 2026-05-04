<?php

use App\Models\Employee;
use App\Models\TimeEntryImport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entry_import_items', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(TimeEntryImport::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Employee::class)->nullable()->constrained()->nullOnDelete();

            $table->string('provider')->default('solides');

            $table->string('external_id')->nullable();
            $table->string('external_employee_id')->nullable();
            $table->string('external_employee_name')->nullable();

            $table->date('entry_date')->nullable();
            $table->dateTime('entry_datetime')->nullable();

            $table->string('type')->nullable(); // entrada, saida, unknown
            $table->string('status')->default('imported'); // imported, ignored, employee_not_found

            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->index(['provider', 'external_id']);
            $table->index(['external_employee_id']);
            $table->index(['entry_date']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entry_import_items');
    }
};