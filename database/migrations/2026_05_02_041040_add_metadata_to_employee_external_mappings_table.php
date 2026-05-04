<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_external_mappings', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('external_name');
        });
    }

    public function down(): void
    {
        Schema::table('employee_external_mappings', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};