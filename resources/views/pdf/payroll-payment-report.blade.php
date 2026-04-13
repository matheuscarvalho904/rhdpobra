<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Pagamento da Folha</title>
    <style>
        @page {
            margin: 18px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        h1, h2, h3, h4, p {
            margin: 0;
            padding: 0;
        }

        .header {
            margin-bottom: 14px;
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

        .company-box {
            margin-top: 18px;
            border: 1px solid #d1d5db;
        }

        .company-title {
            background: #f3f4f6;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: bold;
            border-bottom: 1px solid #d1d5db;
        }

        .content-box {
            padding: 10px;
        }

        .work-title {
            margin: 12px 0 6px;
            font-size: 11px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px 7px;
            vertical-align: middle;
        }

        th {
            background: #f9fafb;
            text-align: left;
            font-weight: bold;
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

        .total-box {
            margin-top: 18px;
            border: 1px solid #9ca3af;
        }

        .total-box td {
            padding: 8px 10px;
            font-size: 12px;
            font-weight: bold;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    @php
        if (! function_exists('formatCpfReport')) {
            function formatCpfReport($value): string
            {
                $digits = preg_replace('/\D/', '', (string) $value);

                if (strlen($digits) !== 11) {
                    return $value ?: '-';
                }

                return substr($digits, 0, 3) . '.'
                    . substr($digits, 3, 3) . '.'
                    . substr($digits, 6, 3) . '-'
                    . substr($digits, 9, 2);
            }
        }

        if (! function_exists('moneyReport')) {
            function moneyReport($value): string
            {
                return 'R$ ' . number_format((float) ($value ?? 0), 2, ',', '.');
            }
        }
    @endphp

    <div class="header">
        <div class="title">Relatório de Pagamento da Folha</div>
        <div class="subtitle">
            Competência: {{ $competencyLabel ?? 'Todas' }}
        </div>
    </div>

    @forelse(($rows ?? []) as $companyName => $worksGroup)
        <div class="company-box">
            <div class="company-title">
                Empresa: {{ $companyName }}
            </div>

            <div class="content-box">
                @foreach(($worksGroup ?? []) as $workName => $workData)
                    <div class="work-title">
                        Obra: {{ $workName }}
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>CPF</th>
                                <th>Matrícula</th>
                                <th>Cargo</th>
                                <th>Filial</th>
                                <th>Chave PIX</th>
                                <th class="right">Bruto</th>
                                <th class="right">Descontos</th>
                                <th class="right">Líquido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($workData['rows'] ?? []) as $row)
                                <tr>
                                    <td>{{ $row['employee_name'] ?? '-' }}</td>
                                    <td>{{ formatCpfReport($row['cpf'] ?? null) }}</td>
                                    <td>{{ $row['registration_number'] ?? '-' }}</td>
                                    <td>{{ $row['job_role'] ?? '-' }}</td>
                                    <td>{{ $row['branch'] ?? '-' }}</td>
                                    <td>{{ $row['pix_key'] ?? '-' }}</td>
                                    <td class="right">{{ moneyReport($row['gross_total'] ?? 0) }}</td>
                                    <td class="right">{{ moneyReport($row['discounts_total'] ?? 0) }}</td>
                                    <td class="right">{{ moneyReport($row['net_total'] ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="center muted">
                                        Nenhum colaborador encontrado nesta obra.
                                    </td>
                                </tr>
                            @endforelse

                            <tr>
                                <td colspan="6" class="right bold">Total da Obra</td>
                                <td class="right bold">{{ moneyReport($workData['total_gross'] ?? 0) }}</td>
                                <td class="right bold">{{ moneyReport($workData['total_discounts'] ?? 0) }}</td>
                                <td class="right bold">{{ moneyReport($workData['total_net'] ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            </div>
        </div>
    @empty
        <p>Nenhum registro encontrado para os filtros informados.</p>
    @endforelse

    <table class="total-box">
        <tr>
            <td>Total Geral Bruto</td>
            <td class="right">{{ moneyReport($totalGross ?? 0) }}</td>
        </tr>
        <tr>
            <td>Total Geral Descontos</td>
            <td class="right">{{ moneyReport($totalDiscounts ?? 0) }}</td>
        </tr>
        <tr>
            <td>Total Geral Líquido</td>
            <td class="right">{{ moneyReport($totalNet ?? 0) }}</td>
        </tr>
        <tr>
            <td>Total Geral FGTS</td>
            <td class="right">{{ moneyReport($totalFgts ?? 0) }}</td>
        </tr>
    </table>
</body>
</html>