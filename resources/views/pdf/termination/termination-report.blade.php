<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Termo / Demonstrativo de Rescisão</title>
    <style>
        @page { margin: 16px; }

        * {
            box-sizing: border-box;
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            font-size: 10px;
            color: #0f172a;
        }

        .header {
            border: 1px solid #0f172a;
            margin-bottom: 10px;
        }

        .header-top {
            background: #dbe4f0;
            padding: 8px 10px;
            border-bottom: 1px solid #0f172a;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .subtitle {
            font-size: 10px;
            color: #334155;
        }

        .header-body {
            padding: 8px 10px;
        }

        .section {
            border: 1px solid #0f172a;
            margin-bottom: 10px;
        }

        .section-title {
            background: #e2e8f0;
            padding: 6px 8px;
            font-size: 11px;
            font-weight: bold;
            border-bottom: 1px solid #0f172a;
        }

        .section-body {
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #0f172a;
            padding: 5px 6px;
            vertical-align: middle;
        }

        th {
            background: #f1f5f9;
            font-weight: bold;
            text-align: left;
        }

        .info-table td {
            width: 25%;
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

        .summary-table td {
            font-weight: bold;
        }

        .summary-label {
            width: 70%;
        }

        .net-row td {
            background: #dbeafe;
            font-size: 12px;
            font-weight: bold;
        }

        .signature-grid {
            width: 100%;
            margin-top: 26px;
        }

        .signature-box {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
        }

        .signature-line {
            margin-top: 42px;
            border-top: 1px solid #0f172a;
            padding-top: 6px;
        }

        .footer-note {
            margin-top: 14px;
            font-size: 9px;
            color: #475569;
        }
    </style>
</head>
<body>
    @php
        $employeeName = $employee?->name ?? '-';
        $employeeCpf = $employee?->cpf ?? '-';
        $companyName = $company?->name ?? '-';
        $companyDocument = $company?->cnpj ?? $company?->document ?? '-';
        $branchName = $branch?->name ?? '-';
        $workName = $work?->name ?? '-';
        $jobRoleName = $jobRole?->name ?? '-';
        $registrationNumber = $contract?->registration_number ?? '-';

        $formatCpf = function (?string $value): string {
            $digits = preg_replace('/\D+/', '', (string) $value);

            if (strlen($digits) !== 11) {
                return (string) $value;
            }

            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        };

        $noticeTypeLabel = match ($termination->notice_type ?? null) {
            'worked' => 'Aviso Trabalhado',
            'indemnified' => 'Aviso Indenizado',
            'home' => 'Aviso em Casa',
            default => '-',
        };
    @endphp

    <div class="header">
        <div class="header-top">
            <div class="title">Termo / Demonstrativo de Rescisão</div>
            <div class="subtitle">Demonstrativo interno de verbas rescisórias calculadas pelo sistema</div>
        </div>

        <div class="header-body">
            <table class="info-table">
                <tr>
                    <td><span class="bold">Empresa:</span> {{ $companyName }}</td>
                    <td><span class="bold">Documento:</span> {{ $companyDocument }}</td>
                    <td><span class="bold">Filial:</span> {{ $branchName }}</td>
                    <td><span class="bold">Obra:</span> {{ $workName }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Dados do Colaborador</div>
        <div class="section-body">
            <table class="info-table">
                <tr>
                    <td><span class="bold">Colaborador:</span> {{ $employeeName }}</td>
                    <td><span class="bold">CPF:</span> {{ $formatCpf($employeeCpf) }}</td>
                    <td><span class="bold">Matrícula:</span> {{ $registrationNumber }}</td>
                    <td><span class="bold">Cargo:</span> {{ $jobRoleName }}</td>
                </tr>
                <tr>
                    <td><span class="bold">Data Desligamento:</span> {{ optional($termination->termination_date)->format('d/m/Y') }}</td>
                    <td><span class="bold">Tipo Aviso:</span> {{ $noticeTypeLabel }}</td>
                    <td><span class="bold">Dias Aviso:</span> {{ $termination->notice_days ?? 0 }}</td>
                    <td><span class="bold">Data Projetada:</span> {{ optional($termination->projected_end_date)->format('d/m/Y') ?: '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Verbas Rescisórias</div>
        <div class="section-body">
            <table>
                <thead>
                    <tr>
                        <th style="width: 14%;">Código</th>
                        <th>Descrição</th>
                        <th style="width: 12%;" class="center">Ref.</th>
                        <th style="width: 18%;" class="right">Proventos</th>
                        <th style="width: 18%;" class="right">Descontos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item['code'] ?? '-' }}</td>
                            <td>{{ $item['description'] ?? '-' }}</td>
                            <td class="center">
                                {{ isset($item['reference']) ? number_format((float) $item['reference'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="right">
                                {{ ($item['type'] ?? null) === 'provento'
                                    ? 'R$ ' . number_format((float) ($item['amount'] ?? 0), 2, ',', '.')
                                    : '' }}
                            </td>
                            <td class="right">
                                {{ ($item['type'] ?? null) === 'desconto'
                                    ? 'R$ ' . number_format((float) ($item['amount'] ?? 0), 2, ',', '.')
                                    : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="center">Nenhum item calculado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top: 8px;">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label">Total Bruto</td>
                        <td class="right">R$ {{ number_format((float) ($result['gross_amount'] ?? 0), 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Total Descontos</td>
                        <td class="right">R$ {{ number_format((float) ($result['total_discounts'] ?? 0), 2, ',', '.') }}</td>
                    </tr>
                    <tr class="net-row">
                        <td class="summary-label">Líquido da Rescisão</td>
                        <td class="right">R$ {{ number_format((float) ($result['net_amount'] ?? 0), 2, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Bases e Encargos</div>
        <div class="section-body">
            <table>
                <tr>
                    <td><span class="bold">Base INSS:</span> R$ {{ number_format((float) ($result['base_inss'] ?? 0), 2, ',', '.') }}</td>
                    <td><span class="bold">Base IRRF:</span> R$ {{ number_format((float) ($result['base_irrf'] ?? 0), 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><span class="bold">FGTS do mês:</span> R$ {{ number_format((float) ($result['fgts_month_amount'] ?? 0), 2, ',', '.') }}</td>
                    <td><span class="bold">Multa 40% FGTS:</span> R$ {{ number_format((float) ($result['fgts_fine_amount'] ?? 0), 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><span class="bold">Saldo FGTS informado:</span> R$ {{ number_format((float) ($result['fgts_balance'] ?? 0), 2, ',', '.') }}</td>
                    <td><span class="bold">Base multa FGTS:</span> R$ {{ number_format((float) ($result['fgts_fine_base'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-line">Assinatura do Responsável</div>
        </div>

        <div class="signature-box" style="float: right;">
            <div class="signature-line">Assinatura do Colaborador</div>
        </div>
    </div>

    <div class="footer-note">
        Este documento é um demonstrativo gerado para conferência interna das verbas rescisórias.
    </div>
</body>
</html>