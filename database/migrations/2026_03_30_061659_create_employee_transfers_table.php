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
        Schema::create('employee_transfers', function (Blueprint $table) {
    $table->id();

    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

    $table->foreignId('old_company_id')->nullable()->constrained('companies')->nullOnDelete();
    $table->foreignId('new_company_id')->nullable()->constrained('companies')->nullOnDelete();

    $table->foreignId('old_branch_id')->nullable()->constrained('branches')->nullOnDelete();
    $table->foreignId('new_branch_id')->nullable()->constrained('branches')->nullOnDelete();

    $table->foreignId('old_work_id')->nullable()->constrained('works')->nullOnDelete();
    $table->foreignId('new_work_id')->nullable()->constrained('works')->nullOnDelete();

    // 🔥 CORREÇÃO AQUI
    $table->foreignId('old_department_id')->nullable()->constrained('departments')->nullOnDelete();
    $table->foreignId('new_department_id')->nullable()->constrained('departments')->nullOnDelete();

    $table->foreignId('old_cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
    $table->foreignId('new_cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();

    $table->foreignId('old_job_role_id')->nullable()->constrained('job_roles')->nullOnDelete();
    $table->foreignId('new_job_role_id')->nullable()->constrained('job_roles')->nullOnDelete();

    $table->date('transfer_date');

    $table->string('reason')->nullable();
    $table->text('notes')->nullable();

    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_transfers');
    }
};
