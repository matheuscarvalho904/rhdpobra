<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('has_experience_period')->default(false)->after('contract_type_id');
            $table->string('experience_model', 30)->nullable()->after('has_experience_period');
            $table->unsignedInteger('experience_days_first')->nullable()->after('experience_model');
            $table->unsignedInteger('experience_days_second')->nullable()->after('experience_days_first');
            $table->unsignedInteger('experience_total_days')->nullable()->after('experience_days_second');
            $table->date('experience_start_date')->nullable()->after('experience_total_days');
            $table->date('experience_end_date')->nullable()->after('experience_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'has_experience_period',
                'experience_model',
                'experience_days_first',
                'experience_days_second',
                'experience_total_days',
                'experience_start_date',
                'experience_end_date',
            ]);
        });
    }
};