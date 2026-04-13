<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_access_scopes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'company_id']);
            $table->index(['user_id', 'branch_id']);
            $table->index(['user_id', 'work_id']);
            $table->index(['user_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_access_scopes');
    }
};