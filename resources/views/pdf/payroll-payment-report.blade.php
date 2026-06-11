<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Pagamento da Folha</title>
    <style>
        @page { margin: 14px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111827; }
        h1, h2, h3, h4, p { margin: 0; padding: 0; }
        .header { margin-bottom: 12px; }
        .title { font-size: 17px; font-weight: bold; margin-bottom: 4px; }
        .subtitle { font-size: 10px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 5px; vertical-align: middle; }
        th { background: #f9fafb; text-align: left; font-weight: bold; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .total-box { margin-top: 14px; border: 1px solid #9ca3af; }
        .total-box td { padding: 7px 10px; font-size: 11px; font-weight: bold; }
        .muted { color: #6b7280; }
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
            Competência: {{ $competencyLabel ?? 'Todas' }} | Ordem: Alfabética global
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Colaborador</th>
                <th>Empresa</th>
                <th>Obra</th>
                <th>CPF</th>
                <th>Matrícula</th>
                <th>Cargo</th>
                <th>Filial</th>
                <th>Chave PIX</th>
                <th class="right">Salário Base</th>
                <th class="right">Bruto</th>
                <th class="right">Descontos</th>
                <th class="right">Líquido</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($rows ?? []) as $row)
                <tr>
                    <td>{{ $row['employee_name'] ?? '-' }}</td>
                    <td>{{ $row['company'] ?? '-' }}</td>
                    <td>{{ $row['work'] ?? '-' }}</td>
                    <td>{{ formatCpfReport($row['cpf'] ?? null) }}</td>
                    <td>{{ $row['registration_number'] ?? '-' }}</td>
                    <td>{{ $row['job_role'] ?? '-' }}</td>
                    <td>{{ $row['branch'] ?? '-' }}</td>
                    <td>{{ $row['pix_key'] ?? '-' }}</td>
                    <td class="right">{{ moneyReport($row['base_salary'] ?? 0) }}</td>
                    <td class="right">{{ moneyReport($row['gross_total'] ?? 0) }}</td>
                    <td class="right">{{ moneyReport($row['discounts_total'] ?? 0) }}</td>
                    <td class="right">{{ moneyReport($row['net_total'] ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="center muted">Nenhum colaborador encontrado para os filtros informados.</td>
                </tr>
            @endforelse

            <tr>
                <td colspan="9" class="right bold">Totais Gerais</td>
                <td class="right bold">{{ moneyReport($totalGross ?? 0) }}</td>
                <td class="right bold">{{ moneyReport($totalDiscounts ?? 0) }}</td>
                <td class="right bold">{{ moneyReport($totalNet ?? 0) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="total-box">
        <tr><td>Total Geral Bruto</td><td class="right">{{ moneyReport($totalGross ?? 0) }}</td></tr>
        <tr><td>Total Geral Descontos</td><td class="right">{{ moneyReport($totalDiscounts ?? 0) }}</td></tr>
        <tr><td>Total Geral Líquido</td><td class="right">{{ moneyReport($totalNet ?? 0) }}</td></tr>
        <tr><td>Total Geral FGTS</td><td class="right">{{ moneyReport($totalFgts ?? 0) }}</td></tr>
    </table>
</body>
</html>
