<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_integrations', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Company::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('provider')->default('solides');
            $table->string('name')->default('Sólides Ponto');
            $table->string('base_url')->nullable();
            $table->text('token')->nullable();

            $table->boolean('active')->default(true);
            $table->timestamp('last_sync_at')->nullable();

            $table->json('settings')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['provider', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_integrations');
    }
};