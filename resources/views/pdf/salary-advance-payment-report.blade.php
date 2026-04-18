<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Pagamento de Adiantamento</title>
    <style>
        @page { margin: 20px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        .header,
        .section {
            border: 1px solid #cbd5e1;
            margin-bottom: 14px;
        }

        .header-title,
        .section-title {
            background: #e5e7eb;
            padding: 8px 10px;
            font-weight: bold;
            border-bottom: 1px solid #cbd5e1;
        }

        .body {
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .no-border td {
            border: none;
            padding: 4px 0;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .value {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }

        .pix-box {
            text-align: center;
            padding: 12px;
        }

        .payload-box {
            margin-top: 10px;
            padding: 8px;
            border: 1px dashed #94a3b8;
            background: #f8fafc;
        }

        .small {
            font-size: 9px;
            color: #4b5563;
            word-break: break-all;
            line-height: 1.35;
        }

        .label {
            font-weight: bold;
            color: #111827;
        }

        .subtitle {
            font-size: 10px;
            color: #475569;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    @php
        $formatCpf = function (?string $value): string {
            $digits = preg_replace('/\D+/', '', (string) $value);

            if (strlen($digits) !== 11) {
                return $value ?: '-';
            }

            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        };

        $statusLabel = match ($salaryAdvance->status ?? null) {
            'draft' => 'Rascunho',
            'paid' => 'Pago',
            'canceled' => 'Cancelado',
            'integrated_payroll' => 'Integrado na Folha',
            default => $salaryAdvance->status ?? '-',
        };

        $paymentMethodLabel = match ($salaryAdvance->payment_method ?? null) {
            'pix' => 'PIX',
            'bank_transfer' => 'Transferência',
            'cash' => 'Dinheiro',
            default => $salaryAdvance->payment_method ?? '-',
        };

        $pixTypeLabel = match ($pixKeyType ?? null) {
            'cpf' => 'CPF',
            'cnpj' => 'CNPJ',
            'email' => 'E-mail',
            'phone' => 'Telefone',
            'random' => 'Chave Aleatória',
            default => $pixKeyType ?? '-',
        };
    @endphp

    <div class="header">
        <div class="header-title">Relatório de Pagamento de Adiantamento</div>
        <div class="body">
            <div class="subtitle">
                Documento gerado para conferência e pagamento via PIX.
            </div>

            <table class="no-border" style="margin-top: 8px;">
                <tr>
                    <td><span class="label">Colaborador:</span> {{ $employee->name ?? '-' }}</td>
                    <td><span class="label">CPF:</span> {{ $formatCpf($employee->cpf ?? null) }}</td>
                </tr>
                <tr>
                    <td><span class="label">Empresa:</span> {{ $company?->name ?? '-' }}</td>
                    <td><span class="label">Filial:</span> {{ $branch?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Obra:</span> {{ $work?->name ?? '-' }}</td>
                    <td><span class="label">Data do Adiantamento:</span> {{ optional($salaryAdvance->advance_date)->format('d/m/Y') ?: '-' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Forma de Pagamento:</span> {{ $paymentMethodLabel }}</td>
                    <td><span class="label">Status:</span> {{ $statusLabel }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Dados do Pagamento</div>
        <div class="body">
            <table>
                <tr>
                    <td style="width: 35%;"><strong>Valor</strong></td>
                    <td class="right value">R$ {{ number_format((float) ($salaryAdvance->amount ?? 0), 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Tipo da Chave PIX</strong></td>
                    <td>{{ $pixTypeLabel }}</td>
                </tr>
                <tr>
                    <td><strong>Chave PIX</strong></td>
                    <td>{{ $pixKey ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Favorecido</strong></td>
                    <td>{{ $beneficiaryName ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Documento do Favorecido</strong></td>
                    <td>{{ $salaryAdvance->pix_holder_document ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">QR Code PIX para Pagamento</div>
        <div class="body pix-box">
            <div>
                @if(!empty($qrCodeSvg))
                    <img
                        src="data:image/svg+xml;base64,{{ base64_encode($qrCodeSvg) }}"
                        alt="QR Code PIX"
                        style="width: 180px; height: 180px;"
                    >
                @endif
            </div>

            <p style="margin: 10px 0 6px 0;">
                <strong>Escaneie para pagar</strong>
            </p>

            <div class="payload-box">
                <div class="small">{{ $pixPayload ?? '-' }}</div>
            </div>
        </div>
    </div>
</body>
</html>