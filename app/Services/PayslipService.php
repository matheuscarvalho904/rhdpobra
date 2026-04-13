<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollRun;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PayslipService
{
    public function generate(PayrollRun $run, Employee $employee)
    {
        $items = $this->getItems($run, $employee);
        $payslip = $this->buildPayslipObject($run, $employee, $items);

        $data = [
            'payslip' => $payslip,
            'items' => $items,
            'employee' => $employee,
            'company' => $run->company,
            'work' => $run->work,
            'payrollRun' => $run,
        ];

        return Pdf::loadView('pdf.payroll.payslip', $data)
            ->setPaper('a4', 'portrait');
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
        $pixKeyType = strtolower((string) ($employee->pix_key_type ?? ''));

        if ($rawPixKey === '' || $netAmount <= 0) {
            return null;
        }

        $pixKey = match ($pixKeyType) {
            'cpf', 'cnpj', 'phone', 'telefone', 'celular' => preg_replace('/\D+/', '', $rawPixKey),
            default => $rawPixKey,
        };

        $pixKey = trim((string) $pixKey);

        if ($pixKey === '') {
            return null;
        }

        $companyName = trim((string) ($run->company->name ?? 'EMPRESA'));
        $city = trim((string) ($run->company->city ?? 'CIDADE'));

        $merchantName = strtoupper(substr($this->sanitizePixText($companyName), 0, 25));
        $merchantCity = strtoupper(substr($this->sanitizePixText($city !== '' ? $city : 'CIDADE'), 0, 15));
        $txid = strtoupper(substr('HOL' . $run->id . 'EMP' . $employee->id, 0, 25));

        $merchantAccountInfo =
            $this->emv('00', 'BR.GOV.BCB.PIX') .
            $this->emv('01', $pixKey);

        $amount = number_format($netAmount, 2, '.', '');

        $payloadWithoutCrc =
            '000201' .
            $this->emv('26', $merchantAccountInfo) .
            '52040000' .
            '5303986' .
            $this->emv('54', $amount) .
            '5802BR' .
            $this->emv('59', $merchantName) .
            $this->emv('60', $merchantCity) .
            $this->emv('62', $this->emv('05', $txid)) .
            '6304';

        return $payloadWithoutCrc . $this->crc16($payloadWithoutCrc);
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

    protected function sanitizePixText(string $value): string
    {
        $value = Str::ascii($value);
        $value = preg_replace('/[^A-Za-z0-9 ]+/', '', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? '';

        return $value;
    }

    protected function emv(string $id, string $value): string
    {
        $length = str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT);

        return $id . $length . $value;
    }

    protected function crc16(string $payload): string
    {
        $polynomial = 0x1021;
        $result = 0xFFFF;

        for ($offset = 0; $offset < strlen($payload); $offset++) {
            $result ^= (ord($payload[$offset]) << 8);

            for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                if (($result & 0x8000) !== 0) {
                    $result = (($result << 1) ^ $polynomial);
                } else {
                    $result = $result << 1;
                }

                $result &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($result), 4, '0', STR_PAD_LEFT));
    }

    protected function fileName(Employee $employee, PayrollRun $run): string
    {
        return 'holerite-' . str($employee->name)->slug() . '-run-' . $run->id . '.pdf';
    }

    protected function money(float $value): float
    {
        return round($value, 2);
    }
}