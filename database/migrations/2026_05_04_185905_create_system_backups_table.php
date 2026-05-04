<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_backups', function (Blueprint $table) {
    $table->id();

    $table->string('name')->nullable();
    $table->string('disk')->default('local');
    $table->string('path')->nullable();
    $table->string('status')->default('pending');

    $table->unsignedBigInteger('size')->nullable();

    // 🔥 IMPORTANTE
    $table->longText('message')->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();

    $table->timestamp('started_at')->nullable();
    $table->timestamp('finished_at')->nullable();

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('system_backups');
    }
};