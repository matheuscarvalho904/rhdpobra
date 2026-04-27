<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {

            if (!Schema::hasColumn('employees', 'contract_term_type')) {
                $table->string('contract_term_type')->nullable()->after('contract_type_id');
            }

            if (!Schema::hasColumn('employees', 'contract_term_days')) {
                $table->integer('contract_term_days')->nullable()->after('contract_term_type');
            }

            if (!Schema::hasColumn('employees', 'contract_start_date')) {
                $table->date('contract_start_date')->nullable()->after('contract_term_days');
            }

            if (!Schema::hasColumn('employees', 'contract_end_date')) {
                $table->date('contract_end_date')->nullable()->after('contract_start_date');
            }

        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'contract_term_type',
                'contract_term_days',
                'contract_start_date',
                'contract_end_date',
            ]);
        });
    }
};