<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Holerite</title>
    <style>
        @page {
            margin: 4px;
        }

        * {
            box-sizing: border-box;
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            font-size: 7px;
            color: #0f172a;
        }

        .copy {
            border: 1px solid #1e293b;
            padding: 3px;
            margin-bottom: 3px;
            page-break-inside: avoid;
            page-break-after: avoid;
            min-height: 47%;
            overflow: hidden;
            background: #ffffff;
        }

        .cut-line {
            text-align: center;
            font-size: 6px;
            margin: 1px 0 3px;
            border-top: 1px dashed #94a3b8;
            color: #64748b;
            padding-top: 1px;
            line-height: 1;
        }

        .header-table,
        .info-table,
        .items-table,
        .bases-table,
        .receipt-table,
        .signature-table,
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td,
        .info-table td,
        .items-table th,
        .items-table td,
        .bases-table td,
        .receipt-table td,
        .signature-table td,
        .bottom-table td {
            border: 1px solid #334155;
            padding: 2px 4px;
            vertical-align: middle;
            line-height: 1.1;
        }

        .header-left {
            width: 69%;
            padding: 0 !important;
        }

        .header-right {
            width: 31%;
            text-align: right;
            background: #f8fafc;
        }

        .header-brand {
            width: 100%;
            border-collapse: collapse;
        }

        .header-brand td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .logo-cell {
            width: 68px;
            text-align: center;
            padding: 4px !important;
            border-right: 1px solid #334155 !important;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .logo-box {
            width: 54px;
            height: 54px;
            margin: 0 auto;
            border: 1px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            overflow: hidden;
        }

        .logo-box img {
            max-width: 50px;
            max-height: 50px;
            object-fit: contain;
            display: block;
        }

        .logo-fallback {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
            line-height: 1;
        }

        .company-cell {
            padding: 4px 6px !important;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .company-name {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
            letter-spacing: 0.2px;
        }

        .company-meta {
            font-size: 6.6px;
            color: #334155;
            line-height: 1.2;
        }

        .bank-title {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.4px;
        }

        .bank-subtitle {
            font-size: 6.6px;
            color: #334155;
            margin-top: 1px;
        }

        .copy-tag {
            display: inline-block;
            margin-top: 2px;
            padding: 1px 5px;
            border: 1px solid #cbd5e1;
            background: #e2e8f0;
            font-size: 6.4px;
            font-weight: bold;
            border-radius: 8px;
        }

        .section-gap {
            margin-top: 2px;
        }

        .info-table td {
            background: #ffffff;
        }

        .info-label {
            font-weight: bold;
            color: #0f172a;
        }

        .items-table th {
            background: linear-gradient(180deg, #dbe4f0 0%, #cbd5e1 100%);
            font-weight: bold;
            text-align: left;
            color: #0f172a;
        }

        .items-table td {
            height: 10px;
        }

        .summary-box td {
            font-weight: bold;
            background: #f1f5f9;
        }

        .bases-head td {
            background: linear-gradient(180deg, #dbe4f0 0%, #cbd5e1 100%);
            font-weight: bold;
            text-align: center;
            color: #0f172a;
        }

        .bases-table td {
            padding: 1px 2px;
            font-size: 6.4px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .signature-space {
            height: 12px;
        }

        .nowrap {
            white-space: nowrap;
        }

        .receipt-note {
            font-size: 6.5px;
        }

        .liquid-highlight {
            background: linear-gradient(180deg, #dbeafe 0%, #bfdbfe 100%);
            font-weight: bold;
            font-size: 7.8px;
            color: #0f172a;
        }

        .qr-box {
            width: 95px;
            text-align: center;
            background: #f8fafc;
        }

        .qr-placeholder {
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #94a3b8;
            font-size: 6px;
            color: #64748b;
            padding: 2px;
            background: #fff;
        }

        .pix-info {
            margin-top: 1px;
            font-size: 5.8px;
            line-height: 1.1;
            color: #334155;
        }

        .pix-key {
            word-break: break-all;
        }

        .qr-image {
            margin-top: 1px;
            width: 55px;
            height: 55px;
            object-fit: contain;
            display: inline-block;
            background: #fff;
            padding: 1px;
            border: 1px solid #cbd5e1;
        }

        .premium-note {
            font-size: 5.8px;
            color: #64748b;
        }
    </style>
</head>
<body>
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $employee = $payslip->employee ?? $employee ?? null;
    $company = $payslip->company ?? $company ?? null;
    $work = $payslip->work ?? $work ?? null;
    $payrollRun = $payslip->payrollRun ?? $payrollRun ?? null;
    $competency = $payrollRun?->payrollCompetency ?? $payrollRun?->competency ?? null;

    $employeeName = $employee?->name ?? '-';
    $employeeCpf = $employee?->cpf ?? '-';
    $jobRole = $employee?->jobRole?->name ?? '-';
    $contractType = $employee?->contractType?->name ?? '-';

    $paymentMethod = match ($employee?->payment_method) {
        'pix' => 'PIX',
        'transfer' => 'Transferência',
        'bank_deposit' => 'Depósito Bancário',
        'cash' => 'Dinheiro',
        default => $employee?->payment_method ?? '-',
    };

    $rawPixKey = $employee?->pix_key ?? '-';

    $formatCpf = function (?string $value): string {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if (strlen($digits) !== 11) {
            return (string) $value;
        }

        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
    };

    $formatCnpj = function (?string $value): string {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if (strlen($digits) !== 14) {
            return (string) $value;
        }

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
    };

    $formatPhone = function (?string $value): string {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $digits);
        }

        if (strlen($digits) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $digits);
        }

        return (string) $value;
    };

    $pixKey = match ($employee?->pix_key_type) {
        'cpf' => $formatCpf($rawPixKey),
        'cnpj' => $formatCnpj($rawPixKey),
        'phone' => $formatPhone($rawPixKey),
        default => $rawPixKey,
    };

    $companyName = $company?->name ?? '-';
    $companyDocument = $company?->cnpj ?? $company?->document ?? '-';
    $workName = $work?->name ?? '-';

    // LOGO FIXA NO PUBLIC/STORAGE/LOGOS
    $companyLogo = null;
    $logoFile = public_path('storage/logos/LOGOAMPLA.png');

    if (is_file($logoFile)) {
        $companyLogo = $logoFile;
    }

    $companyInitials = collect(explode(' ', trim((string) $companyName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');

    $admissionDate = $employee?->admission_date
        ? Carbon::parse($employee->admission_date)->format('d/m/Y')
        : '-';

    $paymentDate = $competency?->payment_date
        ? Carbon::parse($competency->payment_date)->format('d/m/Y')
        : '-';

    $competencyText = $competency?->display_name ?? '-';

    $salaryBase = (float) ($payslip->salary_base ?? 0);
    $salaryReference = $payslip->salary_reference ?? '';
    $salaryDescription = $payslip->salary_description ?? 'Salário Base';

    $baseInss = (float) ($payslip->base_inss ?? 0);
    $baseFgts = (float) ($payslip->base_fgts ?? 0);
    $baseIrrf = (float) ($payslip->base_irrf ?? 0);
    $fgtsAmount = (float) ($payslip->fgts ?? 0);

    $grossTotal = (float) ($payslip->total_gross ?? 0);
    $discountTotal = (float) ($payslip->total_discounts ?? 0);
    $netTotal = (float) ($payslip->total_net ?? 0);

    $pixQrCodeDataUri = $payslip->pix_qr_code_data_uri ?? null;

    $items = collect($items ?? []);

    $normalizeText = function (?string $value): string {
        $value = mb_strtoupper((string) $value, 'UTF-8');
        $from = ['Á','À','Â','Ã','Ä','É','È','Ê','Ë','Í','Ì','Î','Ï','Ó','Ò','Ô','Õ','Ö','Ú','Ù','Û','Ü','Ç'];
        $to   = ['A','A','A','A','A','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','C'];
        return str_replace($from, $to, $value);
    };

    $formatReference = function ($reference): string {
        if ($reference === null || $reference === '') {
            return '';
        }

        if (is_string($reference) && str_contains($reference, '/')) {
            return trim($reference);
        }

        if (! is_numeric($reference)) {
            return trim((string) $reference);
        }

        $value = (float) $reference;

        if ($value <= 0) {
            return '';
        }

        return number_format($value, 2, ',', '.');
    };

    $documentTitle = match ($employee?->processing_type) {
        'payroll_clt' => 'Holerite',
        'payroll_rpa' => 'Recibo de Pagamento',
        'internship_payment' => 'Comprovante de Bolsa',
        'accounts_payable' => 'Comprovante de Pagamento',
        default => 'Holerite',
    };

    $salaryBaseItem = $items->first(function ($item) use ($normalizeText) {
        $code = $normalizeText($item->code ?? '');
        $description = $normalizeText($item->description ?? '');

        return in_array($code, ['SALARIO', 'SALARIO_BASE', 'SAL'], true)
            || str_contains($description, 'SALARIO BASE');
    });

    $containsEvent = function ($collection, array $terms) use ($normalizeText) {
        return $collection->contains(function ($item) use ($terms, $normalizeText) {
            $code = $normalizeText($item->code ?? '');
            $description = $normalizeText($item->description ?? '');

            foreach ($terms as $term) {
                $term = $normalizeText($term);

                if ($code === $term || str_contains($description, $term)) {
                    return true;
                }
            }

            return false;
        });
    };

    $displayItems = $items
        ->filter(function ($item) {
            return in_array($item->type ?? null, ['provento', 'desconto'], true);
        })
        ->reject(function ($item) use ($normalizeText) {
            $code = $normalizeText($item->code ?? '');
            $description = $normalizeText($item->description ?? '');

            return in_array($code, ['SALARIO', 'SALARIO_BASE', 'SAL', 'BRUTO', 'DESCONTOS', 'LIQUIDO', 'FGTS'], true)
                || str_contains($description, 'SALARIO BASE')
                || str_contains($description, 'TOTAL BRUTO')
                || str_contains($description, 'TOTAL DE DESCONTOS')
                || str_contains($description, 'VALOR LIQUIDO')
                || str_contains($description, 'FGTS');
        })
        ->values();

    if (! $containsEvent($displayItems, ['INSS'])) {
        $inssValue = (float) ($payslip->inss ?? $payslip->discount_inss ?? 0);

        if ($inssValue > 0) {
            $displayItems->push((object) [
                'code' => 'INSS',
                'description' => 'Desconto INSS',
                'reference' => null,
                'amount' => $inssValue,
                'type' => 'desconto',
            ]);
        }
    }

    if (! $containsEvent($displayItems, ['IRRF'])) {
        $irrfValue = (float) ($payslip->irrf ?? $payslip->discount_irrf ?? 0);

        $displayItems->push((object) [
            'code' => 'IRRF',
            'description' => $irrfValue > 0 ? 'Desconto IRRF' : 'IRRF (Isento)',
            'reference' => null,
            'amount' => $irrfValue,
            'type' => 'desconto',
        ]);
    }

    $proventItems = $displayItems
        ->filter(fn ($item) => ($item->type ?? null) === 'provento')
        ->sortBy(fn ($item) => $normalizeText($item->description ?? ''))
        ->values();

    $discountItems = $displayItems
        ->filter(fn ($item) => ($item->type ?? null) === 'desconto')
        ->sortBy(fn ($item) => $normalizeText($item->description ?? ''))
        ->values();

    $orderedItems = collect();

    if ($salaryBaseItem || $salaryBase > 0) {
        $orderedItems->push((object) [
            'code' => $salaryBaseItem->code ?? 'SAL',
            'description' => $salaryDescription,
            'reference' => $salaryReference,
            'amount' => (float) ($salaryBaseItem->amount ?? $salaryBase),
            'type' => 'provento',
        ]);
    }

    foreach ($proventItems as $item) {
        $orderedItems->push($item);
    }

    foreach ($discountItems as $item) {
        $orderedItems->push($item);
    }

    $filledRows = $orderedItems->count();
    $rowsCount = max($filledRows, 4);
    $rowsCount = min($rowsCount, 6);
@endphp

@foreach ([
    '1ª VIA - FUNCIONÁRIO',
    '2ª VIA - EMPRESA',
] as $copyLabel)

<div class="copy">
    <table class="header-table">
        <tr>
            <td class="header-left">
                <table class="header-brand">
                    <tr>
                        <td class="logo-cell">
                            <div class="logo-box">
                                @if(! empty($companyLogo))
                                    <img src="{{ $companyLogo }}" alt="Logo da empresa">
                                @else
                                    <div class="logo-fallback">{{ $companyInitials !== '' ? $companyInitials : 'LG' }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="company-cell">
                            <div class="company-name">{{ $companyName }}</div>
                            <div class="company-meta"><span class="bold">CNPJ:</span> {{ $formatCnpj($companyDocument) }}</div>
                            <div class="company-meta"><span class="bold">Obra:</span> {{ $workName }}</div>
                            <div class="premium-note">Documento de pagamento gerado pelo ERP</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="header-right">
                <div class="bank-title">{{ $documentTitle }}</div>
                <div class="bank-subtitle">{{ $competencyText }}</div>
                <div class="copy-tag">{{ $copyLabel }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table section-gap">
        <tr>
            <td><span class="info-label">Colaborador:</span> {{ $employeeName }}</td>
            <td><span class="info-label">Cargo:</span> {{ $jobRole }}</td>
            <td><span class="info-label">Contrato:</span> {{ $contractType }}</td>
            <td><span class="info-label">Admissão:</span> {{ $admissionDate }}</td>
        </tr>
        <tr>
            <td><span class="info-label">CPF:</span> {{ $formatCpf($employeeCpf) }}</td>
            <td><span class="info-label">Pagamento:</span> {{ $paymentMethod }}</td>
            <td><span class="info-label">Data Pagamento:</span> {{ $paymentDate }}</td>
            <td><span class="info-label">Chave PIX:</span> {{ $pixKey }}</td>
        </tr>
    </table>

    <table class="items-table section-gap">
        <thead>
            <tr>
                <th>Descrição</th>
                <th style="width: 14%;" class="center">Ref</th>
                <th style="width: 21%;" class="right">Vencimentos</th>
                <th style="width: 21%;" class="right">Descontos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderedItems as $item)
                @php
                    $referenceFormatted = $formatReference($item->reference ?? null);
                    $type = $item->type ?? null;
                    $amount = (float) ($item->amount ?? 0);
                @endphp
                <tr>
                    <td>{{ $item->description ?? '-' }}</td>
                    <td class="center">{{ $referenceFormatted !== '' ? $referenceFormatted : ' ' }}</td>
                    <td class="right">
                        {{ $type === 'provento' ? 'R$ ' . number_format($amount, 2, ',', '.') : '' }}
                    </td>
                    <td class="right">
                        {{ $type === 'desconto' ? 'R$ ' . number_format($amount, 2, ',', '.') : '' }}
                    </td>
                </tr>
            @endforeach

            @for($i = $filledRows; $i < $rowsCount; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="receipt-table section-gap">
        <tr class="summary-box">
            <td class="right" style="width: 58%;">Total de Vencimentos</td>
            <td class="right" style="width: 21%;">R$ {{ number_format($grossTotal, 2, ',', '.') }}</td>
            <td style="width: 21%;"></td>
        </tr>
        <tr class="summary-box">
            <td colspan="2" class="right">Total de Descontos</td>
            <td class="right">R$ {{ number_format($discountTotal, 2, ',', '.') }}</td>
        </tr>
        <tr class="liquid-highlight">
            <td colspan="2" class="right">Líquido a Receber</td>
            <td class="right">R$ {{ number_format($netTotal, 2, ',', '.') }}</td>
        </tr>
    </table>

    <table class="bases-table section-gap">
        <tr class="bases-head">
            <td>Salário Base</td>
            <td>Base INSS</td>
            <td>Base FGTS</td>
            <td>FGTS (Depósito)</td>
            <td>Base IRRF</td>
        </tr>
        <tr>
            <td class="center nowrap">{{ number_format($salaryBase, 2, ',', '.') }}</td>
            <td class="center nowrap">{{ number_format($baseInss, 2, ',', '.') }}</td>
            <td class="center nowrap">{{ number_format($baseFgts, 2, ',', '.') }}</td>
            <td class="center nowrap">R$ {{ number_format($fgtsAmount, 2, ',', '.') }}</td>
            <td class="center nowrap">{{ number_format($baseIrrf, 2, ',', '.') }}</td>
        </tr>
    </table>

    <table class="bottom-table section-gap">
        <tr>
            <td style="width: 79%; padding: 0; border: none;">
                <table class="signature-table" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td colspan="2" class="receipt-note">Declaro ter recebido o valor líquido deste recibo.</td>
                    </tr>
                    <tr>
                        <td style="width: 26%;">____/____/______</td>
                        <td class="signature-space center">Assinatura do Recebedor</td>
                    </tr>
                </table>
            </td>
            <td class="qr-box">
                <div class="bold small">PIX para Pagamento</div>

                @if(! empty($pixQrCodeDataUri))
                    <div style="margin-top: 1px;">
                        <img src="{{ $pixQrCodeDataUri }}" alt="QR Code PIX" class="qr-image">
                    </div>
                @else
                    <div class="qr-placeholder" style="margin-top: 1px;">
                        QR indisponível
                    </div>
                @endif

                <div class="pix-info">
                    <div><span class="bold">Valor:</span> R$ {{ number_format($netTotal, 2, ',', '.') }}</div>
                    <div><span class="bold">Chave:</span></div>
                    <div class="pix-key">{{ $pixKey }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

@if (! $loop->last)
    <div class="cut-line">---------------- CORTE ----------------</div>
@endif

@endforeach
</body>
</html>
