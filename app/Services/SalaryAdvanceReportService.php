<?php

namespace App\Services;

use App\Models\SalaryAdvance;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use RuntimeException;

class SalaryAdvanceReportService
{
    public function __construct(
        protected PixPayloadService $pixPayloadService,
    ) {}

    public function generate(SalaryAdvance $salaryAdvance)
    {
        $salaryAdvance->loadMissing([
            'employee.company',
            'employee.branch',
            'employee.work',
        ]);

        $employee = $salaryAdvance->employee;

        if (! $employee) {
            throw new RuntimeException('Adiantamento sem colaborador vinculado.');
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
            throw new RuntimeException('Colaborador sem chave PIX cadastrada.');
        }

        if (! $pixKeyType) {
            throw new RuntimeException('Tipo de chave PIX não identificado.');
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
            pixKeyType: $pixKeyType,
            beneficiaryName: $beneficiaryName,
            city: $city,
            amount: (float) $salaryAdvance->amount,
            txid: 'ADIANT' . $salaryAdvance->id
        );

        $qrCodeSvg = $this->makeQrCodeSvg($payload);

        $pdf = app('dompdf.wrapper');

        return $pdf->loadView('pdf.salary-advance-payment-report', [
            'salaryAdvance' => $salaryAdvance,
            'employee' => $employee,
            'company' => $employee->company,
            'branch' => $employee->branch,
            'work' => $employee->work,
            'pixKey' => $pixKey,
            'pixKeyType' => $pixKeyType,
            'beneficiaryName' => $beneficiaryName,
            'pixPayload' => $payload,
            'qrCodeSvg' => $qrCodeSvg,
        ])->setPaper('a4', 'portrait');
    }

    public function output(SalaryAdvance $salaryAdvance): string
    {
        return $this->generate($salaryAdvance)->output();
    }

    public function download(SalaryAdvance $salaryAdvance)
    {
        return $this->generate($salaryAdvance)
            ->download('adiantamento-' . $salaryAdvance->id . '.pdf');
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

        if (filter_var($pixKey, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (str_starts_with($pixKey, '+')) {
            return 'phone';
        }

        $digits = preg_replace('/\D+/', '', $pixKey) ?? '';

        if (strlen($digits) === 11 && $digits === $pixKey) {
            return 'cpf';
        }

        if (strlen($digits) === 14 && $digits === $pixKey) {
            return 'cnpj';
        }

        if (strlen($digits) >= 10 && strlen($digits) <= 13) {
            return 'phone';
        }

        return 'random';
    }
}