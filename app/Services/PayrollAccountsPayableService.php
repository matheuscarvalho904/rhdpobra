<?php

namespace App\Services;

use App\Models\FinancialCategory;
use App\Models\PayrollRun;
use App\Models\AccountsPayable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayrollAccountsPayableService
{
    public function integrate(PayrollRun $payrollRun): void
    {
        if ($payrollRun->status !== 'closed') {
            throw new RuntimeException('Somente folhas fechadas podem ser integradas ao financeiro.');
        }

        DB::transaction(function () use ($payrollRun) {

            // Evita duplicidade
            $alreadyExists = AccountsPayable::query()
                ->where('reference_type', 'payroll_run')
                ->where('reference_id', $payrollRun->id)
                ->exists();

            if ($alreadyExists) {
                throw new RuntimeException('Esta folha já foi integrada ao financeiro.');
            }

            // Categoria financeira
            $category = FinancialCategory::query()
                ->where('name', 'Folha de Pagamento')
                ->first();

            if (! $category) {
                throw new RuntimeException('Categoria "Folha de Pagamento" não encontrada.');
            }

            // Data de vencimento
            $dueDate = $payrollRun->competency->payment_date
                ?? $payrollRun->competency->period_end;

            // Descrição
            $description = sprintf(
                'Folha %02d/%04d - %s',
                $payrollRun->competency->month,
                $payrollRun->competency->year,
                $payrollRun->company?->name ?? ''
            );

            AccountsPayable::create([
                'company_id' => $payrollRun->company_id,
                'branch_id' => $payrollRun->branch_id,
                'work_id' => $payrollRun->work_id,

                'financial_category_id' => $category->id,

                'description' => $description,
                'amount' => $payrollRun->net_total,

                'due_date' => $dueDate,
                'status' => 'pending',

                'reference_type' => 'payroll_run',
                'reference_id' => $payrollRun->id,
            ]);

            // Atualiza status
            $payrollRun->update([
                'status' => 'integrated_financial',
            ]);
        });
    }
}