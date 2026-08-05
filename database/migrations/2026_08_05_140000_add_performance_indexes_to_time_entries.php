<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_entry_import_items', function (Blueprint $table) {
            $table->index(
                ['time_entry_import_id', 'provider', 'external_id'],
                'tei_items_import_provider_external_idx'
            );

            $table->index(
                ['time_entry_import_id', 'status'],
                'tei_items_import_status_idx'
            );
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->index(
                ['company_id', 'employee_id', 'entry_date'],
                'time_entries_company_employee_date_idx'
            );

            $table->index(
                ['time_entry_import_id', 'employee_id'],
                'time_entries_import_employee_idx'
            );

            $table->index(
                ['employee_id', 'entry_datetime'],
                'time_entries_employee_datetime_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('time_entry_import_items', function (Blueprint $table) {
            $table->dropIndex('tei_items_import_provider_external_idx');
            $table->dropIndex('tei_items_import_status_idx');
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropIndex('time_entries_company_employee_date_idx');
            $table->dropIndex('time_entries_import_employee_idx');
            $table->dropIndex('time_entries_employee_datetime_idx');
        });
    }
};
