<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_files', function (Blueprint $table) {
            $table->id();

            // RELACIONAMENTO PRINCIPAL
            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            // TIPO DO ARQUIVO
            // contrato, epi, ficha, aditivo, documento, etc
            $table->string('type', 50);

            // NOME DO ARQUIVO
            $table->string('file_name');

            // CAMINHO NO STORAGE
            $table->string('file_path');

            // CONTROLE
            $table->timestamp('generated_at')->nullable();

            // STATUS (opcional, mas útil pra ERP)
            $table->boolean('is_active')->default(true);

            // OBSERVAÇÕES (opcional)
            $table->text('notes')->nullable();

            $table->timestamps();

            // INDEXES (performance)
            $table->index(['employee_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_files');
    }
};