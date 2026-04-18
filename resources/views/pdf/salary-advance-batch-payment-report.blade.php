<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lote de Adiantamento PIX</title>
    <style>
        @page { margin: 12px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111827;
        }

        .voucher {
            border: 1px solid #cbd5e1;
            margin-bottom: 10px;
            padding: 0;
        }

        .voucher:last-child {
            margin-bottom: 0;
        }

        .title {
            background: #e5e7eb;
            padding: 6px 8px;
            font-weight: bold;
            border-bottom: 1px solid #cbd5e1;
            font-size: 11px;
        }

        .body {
            padding: 8px;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .grid td,
        .grid th {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .grid th {
            background: #f3f4f6;
            text-align: left;
            width: 18%;
        }

        .value {
            font-size: 14px;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .qr-image {
            width: 110px;
            height: 110px;
        }

        .payload-box {
            margin-top: 6px;
            padding: 6px;
            border: 1px dashed #94a3b8;
            background: #f8fafc;
            font-size: 7px;
            color: #475569;
            word-break: break-all;
            line-height: 1.2;
        }

        .small {
            font-size: 8px;
            color: #475569;
        }

        .page-break {
            page-break-after: always;
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

        $pixTypeLabel = function (?string $value): string {
            return match ($value) {
                'cpf' => 'CPF',
                'cnpj' => 'CNPJ',
                'email' => 'E-mail',
                'phone' => 'Telefone',
                'random' => 'Chave Aleatória',
                default => $value ?: '-',
            };
        };

        $statusLabel = function (?string $value): string {
            return match ($value) {
                'draft' => 'Rascunho',
                'paid' => 'Pago',
                'canceled' => 'Cancelado',
                'integrated_payroll' => 'Integrado na Folha',
                default => $value ?: '-',
            };
        };

        $chunks = collect($items)->chunk(3);
    @endphp

    @foreach($chunks as $chunkIndex => $chunk)
        @foreach($chunk as $item)
            <div class="voucher">
                <div class="title">Comprovante de Adiantamento PIX</div>

                <div class="body">
                    <div class="small" style="margin-bottom: 6px;">
                        Gerado em {{ $generatedAt->format('d/m/Y H:i') }}
                    </div>

                    <table class="grid">
                        <tr>
                            <th>Colaborador</th>
                            <td>{{ $item['employee']->name ?? '-' }}</td>
                            <th>CPF</th>
                            <td>{{ $formatCpf($item['employee']->cpf ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Empresa</th>
                            <td>{{ $item['company']?->name ?? '-' }}</td>
                            <th>Obra</th>
                            <td>{{ $item['work']?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Data</th>
                            <td>{{ optional($item['salaryAdvance']->advance_date)->format('d/m/Y') ?: '-' }}</td>
                            <th>Status</th>
                            <td>{{ $statusLabel($item['salaryAdvance']->status ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Valor</th>
                            <td class="value">R$ {{ number_format((float) ($item['salaryAdvance']->amount ?? 0), 2, ',', '.') }}</td>
                            <th>Tipo PIX</th>
                            <td>{{ $pixTypeLabel($item['pixKeyType'] ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Chave PIX</th>
                            <td colspan="3">{{ $item['pixKey'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Favorecido</th>
                            <td colspan="3">{{ $item['beneficiaryName'] ?? '-' }}</td>
                        </tr>
                    </table>

                    <div class="center">
                        @if(!empty($item['qrCodeSvg']))
                            <img
                                src="data:image/svg+xml;base64,{{ base64_encode($item['qrCodeSvg']) }}"
                                alt="QR Code PIX"
                                class="qr-image"
                            >
                        @endif

                        <div style="margin-top: 4px;"><strong>Escaneie para pagar</strong></div>
                    </div>

                    <div class="payload-box">
                        {{ $item['pixPayload'] ?? '-' }}
                    </div>
                </div>
            </div>
        @endforeach

        @if (! $loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>