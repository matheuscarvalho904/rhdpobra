<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {

            // 🔵 Prazo contrato PF/PJ
            if (! Schema::hasColumn('employees', 'service_contract_term')) {
                $table->string('service_contract_term', 30)
                    ->nullable()
                    ->after('experience_end_date');
            }

            if (! Schema::hasColumn('employees', 'service_contract_start_date')) {
                $table->date('service_contract_start_date')
                    ->nullable()
                    ->after('service_contract_term');
            }

            if (! Schema::hasColumn('employees', 'service_contract_end_date')) {
                $table->date('service_contract_end_date')
                    ->nullable()
                    ->after('service_contract_start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {

            if (Schema::hasColumn('employees', 'service_contract_term')) {
                $table->dropColumn('service_contract_term');
            }

            if (Schema::hasColumn('employees', 'service_contract_start_date')) {
                $table->dropColumn('service_contract_start_date');
            }

            if (Schema::hasColumn('employees', 'service_contract_end_date')) {
                $table->dropColumn('service_contract_end_date');
            }
        });
    }
};