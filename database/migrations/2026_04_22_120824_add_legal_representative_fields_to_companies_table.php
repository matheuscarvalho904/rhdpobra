<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('legal_representative_name')->nullable()->after('name');
            $table->string('legal_representative_cpf', 14)->nullable()->after('legal_representative_name');
            $table->string('legal_representative_rg', 30)->nullable()->after('legal_representative_cpf');
            $table->string('legal_representative_role', 100)->nullable()->after('legal_representative_rg');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'legal_representative_name',
                'legal_representative_cpf',
                'legal_representative_rg',
                'legal_representative_role',
            ]);
        });
    }
};