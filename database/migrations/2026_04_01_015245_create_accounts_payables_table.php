<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts_payable', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('financial_category_id')->constrained()->cascadeOnDelete();

            $table->string('description');
            $table->decimal('amount', 15, 2);

            $table->date('due_date');
            $table->string('status')->default('pending');

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->timestamps();

            $table->index(['reference_type', 'reference_id'], 'acc_payable_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_payable');
    }
};