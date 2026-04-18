<?php

namespace App\Services;

use App\Models\SalaryAdvance;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SalaryAdvanceBatchReportService
{
    public function __construct(
        protected PixPayloadService $pixPayloadService,
    ) {}

    public function generate(Collection $salaryAdvances)
    {
        if ($salaryAdvances->isEmpty()) {
            throw new RuntimeException('Nenhum adiantamento selecionado para gerar o lote.');
        }

        $salaryAdvances->loadMissing([
            'employee.company',
            'employee.branch',
            'employee.work',
        ]);

        $items = $salaryAdvances->map(function (SalaryAdvance $salaryAdvance) {
            $employee = $salaryAdvance->employee;

            if (! $employee) {
                throw new RuntimeException("Adiantamento #{$salaryAdvance->id} sem colaborador vinculado.");
            }

            $pixKey = trim((string) (
                $salaryAdvance->pix_key
                ?: $employee->pix_key
                ?: ''
            ));

            $pixKeyType = $salaryAdvance->pix_key_type
                ?: $employee->pix_key_type
                ?: $this->resolvePixKeyTypeFromValue($pixKey);

            if ($pixKey === '') {
                throw new RuntimeException("Colaborador {$employee->name} sem chave PIX cadastrada.");
            }

            if (! $pixKeyType) {
                throw new RuntimeException("Tipo de chave PIX não identificado para {$employee->name}.");
            }

            $beneficiaryName = trim((string) (
                $salaryAdvance->pix_holder_name
                ?: $employee->pix_holder_name
                ?: $employee->name
                ?: 'FAVORECIDO'
            ));

            $city = trim((string) ($employee->city ?: 'ARIPUANA'));

            $payload = $this->pixPayloadService->generatePayload(
                pixKey: $pixKey,
                beneficiaryName: $beneficiaryName,
                city: $city,
                amount: (float) $salaryAdvance->amount,
                txid: 'ADIANT' . $salaryAdvance->id
            );

            return [
                'salaryAdvance' => $salaryAdvance,
                'employee' => $employee,
                'company' => $employee->company,
                'branch' => $employee->branch,
                'work' => $employee->work,
                'pixKey' => $pixKey,
                'pixKeyType' => $pixKeyType,
                'beneficiaryName' => $beneficiaryName,
                'pixPayload' => $payload,
                'qrCodeSvg' => $this->makeQrCodeSvg($payload),
            ];
        });

        $pdf = app('dompdf.wrapper');

        return $pdf->loadView('pdf.salary-advance-batch-payment-report', [
            'items' => $items,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');
    }

    public function output(Collection $salaryAdvances): string
    {
        return $this->generate($salaryAdvances)->output();
    }

    protected function makeQrCodeSvg(string $payload): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(220),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($payload);
    }

    protected function resolvePixKeyTypeFromValue(?string $pixKey): ?string
    {
        $pixKey = trim((string) $pixKey);

        if ($pixKey === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $pixKey);

        if (strlen($digits) === 11 && $digits === $pixKey) {
            return 'cpf';
        }

        if (strlen($digits) === 14 && $digits === $pixKey) {
            return 'cnpj';
        }

        if (filter_var($pixKey, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (strlen($digits) >= 10 && strlen($digits) <= 13) {
            return 'phone';
        }

        return 'random';
    }
}