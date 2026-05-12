<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(Company::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'company_id']);
            $table->index(['company_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};