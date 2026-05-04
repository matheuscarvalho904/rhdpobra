<?php

use App\Models\PayrollCompetency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_closings', function (Blueprint $table) {
            $table->foreignIdFor(PayrollCompetency::class)
                ->nullable()
                ->after('company_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('time_closings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payroll_competency_id');
        });
    }
};