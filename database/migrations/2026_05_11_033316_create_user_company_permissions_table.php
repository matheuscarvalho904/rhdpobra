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
        Schema::create('user_company_permissions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Company::class)->constrained()->cascadeOnDelete();

            $table->string('module');
            $table->string('action');

            $table->boolean('allowed')->default(false);

            $table->timestamps();

            $table->unique(['user_id', 'company_id', 'module', 'action'], 'ucp_unique');
            $table->index(['user_id', 'company_id']);
            $table->index(['module', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_company_permissions');
    }
};