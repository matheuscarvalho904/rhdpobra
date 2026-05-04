<?php

use App\Models\Company;
use App\Models\PointIntegration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entry_imports', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Company::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(PointIntegration::class)->nullable()->constrained()->nullOnDelete();

            $table->string('provider')->default('solides');
            $table->date('start_date');
            $table->date('end_date');

            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('imported_records')->default(0);
            $table->unsignedInteger('ignored_records')->default(0);

            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entry_imports');
    }
};