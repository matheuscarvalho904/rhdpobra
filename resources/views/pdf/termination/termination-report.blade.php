<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Rescisão</title>
    <style>
        @page { margin: 20px; }

        * {
            box-sizing: border-box;
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            font-size: 11px;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        h1, h2, h3, p {
            margin: 0;
            padding: 0;
        }

        .header {
            margin-bottom: 18px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 11px;
            color: #4b5563;
        }

        .box {
            border: 1px solid #cbd5e1;
            margin-bottom: 14px;
        }

        .box-title {
            background: #e2e8f0;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 12px;
        }

        .box-body {
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: middle;
        }

        th {
            background: #f1f5f9;
            text-align: left;
        }

        .grid-info td {
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

        .summary td {
            font-weight: bold;
        }

        .net {
            background: #dbeafe;
            font-size: 12px;
            font-weight: bold;
        }

        .mt {
            margin-top: 10px;
        }

        .small {
            font-size: 10px;
            color: #475569;
        }

        .signature {
            margin-top: 28px;
        }

        .signature-line {
            margin-top: 42px;
            border-top: 1px solid #111827;
            width: 280px;
            padding-top: 6px;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $employeeName = $employee?->name ?? '-';
        $employeeCpf = $employee?->cpf ?? '-';
        $companyName = $company?->name ?? '-';
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
    @endphp

    <div class="header">
        <div class="title">Relatório de Rescisão</div>
        <div class="subtitle">
            Demonstrativo de verbas rescisórias
        </div>
    </div>

    <div class="box">
        <div class="box-title">Dados do Colaborador</div>
        <div class="box-body">
            <table class="grid-info">
                <tr>
                    <td><span class="bold">Colaborador:</span> {{ $employeeName }}</td>
                    <td><span class="bold">CPF:</span> {{ $formatCpf($employeeCpf) }}</td>
                    <td><span class="bold">Matrícula:</span> {{ $registrationNumber }}</td>
                    <td><span class="bold">Cargo:</span> {{ $jobRoleName }}</td>
                </tr>
                <tr>
                    <td><span class="bold">Empresa:</span> {{ $companyName }}</td>
                    <td><span class="bold">Filial:</span> {{ $branchName }}</td>
                    <td><span class="bold">Obra:</span> {{ $workName }}</td>
                    <td><span class="bold">Tipo Aviso:</span> {{ $termination->notice_type ?? '-' }}</td>
                </tr>
                <tr>
                    <td><span class="bold">Data Desligamento:</span> {{ optional($termination->termination_date)->format('d/m/Y') }}</td>
                    <td><span class="bold">Data Projetada:</span> {{ optional($termination->projected_end_date)->format('d/m/Y') ?: '-' }}</td>
                    <td><span class="bold">Dias Aviso:</span> {{ $termination->notice_days ?? 0 }}</td>
                    <td><span class="bold">Status:</span> {{ $termination->status ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Itens da Rescisão</div>
        <div class="box-body">
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Código</th>
                        <th>Descrição</th>
                        <th style="width: 15%;" class="center">Ref.</th>
                        <th style="width: 18%;" class="right">Proventos</th>
                        <th style="width: 18%;" class="right">Descontos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item['code'] ?? '-' }}</td>
                            <td>{{ $item['description'] ?? '-' }}</td>
                            <td class="center">{{ isset($item['reference']) ? number_format((float) $item['reference'], 2, ',', '.') : '-' }}</td>
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
        </div>
    </div>

    <div class="box">
        <div class="box-title">Resumo Financeiro</div>
        <div class="box-body">
            <table class="summary">
                <tr>
                    <td style="width: 50%;">Total Bruto</td>
                    <td class="right">R$ {{ number_format((float) ($result['gross_amount'] ?? 0), 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Descontos</td>
                    <td class="right">R$ {{ number_format((float) ($result['total_discounts'] ?? 0), 2, ',', '.') }}</td>
                </tr>
                <tr class="net">
                    <td>Líquido da Rescisão</td>
                    <td class="right">R$ {{ number_format((float) ($result['net_amount'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Bases e FGTS</div>
        <div class="box-body">
            <table>
                <tr>
                    <td><span class="bold">Base INSS:</span> R$ {{ number_format((float) ($result['base_inss'] ?? 0), 2, ',', '.') }}</td>
                    <td><span class="bold">Base IRRF:</span> R$ {{ number_format((float) ($result['base_irrf'] ?? 0), 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><span class="bold">FGTS do mês:</span> R$ {{ number_format((float) ($result['fgts_month_amount'] ?? 0), 2, ',', '.') }}</td>
                    <td><span class="bold">Multa 40% FGTS:</span> R$ {{ number_format((float) ($result['fgts_fine_amount'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            </table>
            <p class="small mt">
                Este relatório é um demonstrativo interno para conferência das verbas rescisórias calculadas pelo sistema.
            </p>
        </div>
    </div>

    <div class="signature">
        <div class="signature-line">
            Assinatura / Conferência
        </div>
    </div>
</body>
</html>