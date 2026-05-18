<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollRun;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PayslipService
{
    public function __construct(
        protected PixPayloadService $pixPayloadService,
    ) {}

    public function generate(PayrollRun $run, Employee $employee)
    {
        $items = $this->getItems($run, $employee);
        $payslip = $this->buildPayslipObject($run, $employee, $items);

        return Pdf::loadView('pdf.payroll.payslip', [
            'payslip' => $payslip,
            'items' => $items,
            'employee' => $employee,
            'company' => $run->company,
            'work' => $run->work,
            'payrollRun' => $run,
        ])->setPaper('a4', 'portrait');
    }

    public function stream(PayrollRun $run, Employee $employee)
    {
        return $this->generate($run, $employee)
            ->stream($this->fileName($employee, $run));
    }

    public function download(PayrollRun $run, Employee $employee)
    {
        return $this->generate($run, $employee)
            ->download($this->fileName($employee, $run));
    }

    protected function getItems(PayrollRun $run, Employee $employee): Collection
    {
        return $run->items()
            ->where('employee_id', $employee->id)
            ->orderBy('id')
            ->get();
    }

    protected function buildPayslipObject(PayrollRun $run, Employee $employee, Collection $items): object
    {
        $salaryBaseItem = $this->findSalaryBaseItem($items);

        $summaryGrossItem = $items->firstWhere('code', 'BRUTO');
        $summaryDiscountItem = $items->firstWhere('code', 'DESCONTOS');
        $summaryNetItem = $items->firstWhere('code', 'LIQUIDO');
        $fgtsItem = $items->firstWhere('code', 'FGTS');
        $inssItem = $items->firstWhere('code', 'INSS');
        $irrfItem = $items->firstWhere('code', 'IRRF');

        $eventGross = $items
            ->filter(fn ($item) => ($item->type ?? null) === 'provento')
            ->sum('amount');

        $eventDiscounts = $items
            ->filter(fn ($item) => ($item->type ?? null) === 'desconto')
            ->sum('amount');

        $salaryBase = $this->money(
            (float) ($salaryBaseItem->amount ?? $employee->salary ?? 0)
        );

        $totalGross = $this->money(
            (float) ($summaryGrossItem->amount ?? $eventGross)
        );

        $totalDiscounts = $this->money(
            (float) ($summaryDiscountItem->amount ?? $eventDiscounts)
        );

        $totalNet = $this->money(
            (float) ($summaryNetItem->amount ?? ($totalGross - $totalDiscounts))
        );

        $fgtsAmount = $this->money((float) ($fgtsItem->amount ?? 0));
        $inssAmount = $this->money((float) ($inssItem->amount ?? 0));
        $irrfAmount = $this->money((float) ($irrfItem->amount ?? 0));

        $baseInss = $employee->has_inss ? $totalGross : 0.0;
        $baseFgts = $employee->has_fgts ? $totalGross : 0.0;
        $baseIrrf = $employee->has_irrf
            ? $this->money(max(0, $totalGross - $inssAmount))
            : 0.0;

        $salaryDescription = $salaryBaseItem->description ?? 'Salário Base';
        $isThirteenth = in_array($run->run_type, [
        'thirteenth_first',
        'thirteenth_second',
    ], true);

    $documentTitle = match ($run->run_type) {
        'thirteenth_first' => 'RECIBO 13º SALÁRIO - 1ª PARCELA',
        'thirteenth_second' => 'RECIBO 13º SALÁRIO - 2ª PARCELA',
        default => 'HOLERITE',
    };

        $pixPayload = $this->buildPixPayload($run, $employee, $totalNet);
        $pixQrCodeDataUri = $this->generatePixQrCodeDataUri($pixPayload);

        logger()->info('PIX HOLERITE DEBUG', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'pix_key_type' => $employee->pix_key_type ?? null,
            'pix_key' => $employee->pix_key ?? null,
            'total_net' => $totalNet,
            'pix_payload_generated' => ! empty($pixPayload),
            'pix_qr_generated' => ! empty($pixQrCodeDataUri),
        ]);

        return (object) [
            'is_thirteenth' => $isThirteenth,
            'document_title' => $documentTitle,
            'employee' => $employee,
            'company' => $run->company,
            'work' => $run->work,
            'payrollRun' => $run,

            'total_gross' => $totalGross,
            'total_discounts' => $totalDiscounts,
            'total_net' => $totalNet,

            'salary_base' => $salaryBase,
            'salary_reference' => $this->resolveSalaryReference(
                $salaryBaseItem->reference ?? null,
                $salaryDescription
            ),
            'salary_description' => $salaryDescription,

            'base_inss' => $baseInss,
            'base_fgts' => $baseFgts,
            'base_irrf' => $baseIrrf,

            'inss_amount' => $inssAmount,
            'irrf_amount' => $irrfAmount,
            'fgts' => $fgtsAmount,

            'pix_payload' => $pixPayload,
            'pix_qr_code_data_uri' => $pixQrCodeDataUri,
        ];
    }

    protected function findSalaryBaseItem(Collection $items): ?object
    {
        return $items->first(function ($item) {
            $code = strtoupper((string) ($item->code ?? ''));
            $description = strtoupper((string) ($item->description ?? ''));

            return in_array($code, ['SALARIO', 'SALARIO_BASE', 'SAL'], true)
                || str_contains($description, 'SALÁRIO BASE')
                || str_contains($description, 'SALARIO BASE');
        });
    }

    protected function resolveSalaryReference(mixed $reference, ?string $description = null): ?string
    {
        if (is_string($reference) && str_contains($reference, '/')) {
            return trim($reference);
        }

        if ($reference !== null && $reference !== '' && is_numeric($reference) && (float) $reference > 0) {
            return number_format((float) $reference, 2, ',', '.');
        }

        $description = (string) ($description ?? '');

        if (preg_match('/\((\d+\s*\/\s*\d+)\)/u', $description, $matches)) {
            return preg_replace('/\s+/', '', $matches[1]);
        }

        return '30,00';
    }

    protected function buildPixPayload(PayrollRun $run, Employee $employee, float $netAmount): ?string
    {
        $rawPixKey = trim((string) ($employee->pix_key ?? ''));
        $pixKeyType = strtolower(trim((string) ($employee->pix_key_type ?? '')));

        if ($rawPixKey === '' || $netAmount <= 0) {
            return null;
        }

        try {
            return $this->pixPayloadService->generatePayload(
                pixKey: $rawPixKey,
                pixKeyType: $pixKeyType,
                beneficiaryName: $employee->pix_holder_name ?: $employee->name ?: 'FAVORECIDO',
                city: $employee->city ?: $run->company?->city ?: 'ARIPUANA',
                amount: $netAmount,
                txid: 'HOL' . $run->id . 'EMP' . $employee->id
            );
        } catch (\Throwable $e) {
            logger()->error('Erro ao gerar payload PIX do holerite', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'pix_key' => $rawPixKey,
                'pix_key_type' => $pixKeyType,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function generatePixQrCodeDataUri(?string $payload): ?string
    {
        if (! $payload) {
            logger()->warning('PIX QR não gerado: payload vazio.');
            return null;
        }

        if (! class_exists(\SimpleSoftwareIO\QrCode\Generator::class)) {
            logger()->error('PIX QR não gerado: classe SimpleSoftwareIO\\QrCode\\Generator não encontrada.');
            return null;
        }

        try {
            $qrCode = new \SimpleSoftwareIO\QrCode\Generator();

            $svg = $qrCode
                ->format('svg')
                ->size(140)
                ->margin(1)
                ->generate($payload);

            $svgString = trim((string) $svg);

            if ($svgString === '') {
                logger()->error('PIX QR não gerado: SVG vazio.', [
                    'payload' => $payload,
                ]);

                return null;
            }

            return 'data:image/svg+xml;base64,' . base64_encode($svgString);
        } catch (\Throwable $e) {
            logger()->error('Erro ao gerar QR Code PIX.', [
                'message' => $e->getMessage(),
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    protected function fileName(Employee $employee, PayrollRun $run): string
    {
        $prefix = match ($run->run_type) {
    'thirteenth_first' => '13-primeira-parcela',
    'thirteenth_second' => '13-segunda-parcela',
    default => 'holerite',
};

return $prefix . '-' . str($employee->name)->slug() . '-run-' . $run->id . '.pdf';
    }

    protected function money(float $value): float
    {
        return round($value, 2);
    }
}