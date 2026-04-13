<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório de Pagamento de Adiantamento</title>

    <style>
        @page {
            margin: 22px 24px 28px 24px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }

        .header {
            border-bottom: 2px solid #1f2937;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            margin: 0 0 6px 0;
        }

        .subtitle {
            font-size: 10px;
            color: #4b5563;
            line-height: 1.5;
        }

        .filters-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .filters-table td {
            padding: 4px 6px;
            vertical-align: top;
            border: 1px solid #d1d5db;
        }

        .filters-label {
            width: 110px;
            font-weight: bold;
            background: #f3f4f6;
            color: #111827;
        }

        .company-block {
            margin-top: 16px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .company-title {
            background: #1f2937;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 8px 10px;
            border: 1px solid #111827;
        }

        .work-title {
            background: #e5e7eb;
            color: #111827;
            font-size: 11px;
            font-weight: bold;
            padding: 7px 10px;
            border: 1px solid #9ca3af;
            border-top: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table {
            margin-bottom: 12px;
        }

        .report-table thead th {
            background: #374151;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #4b5563;
            padding: 7px 6px;
            text-align: center;
        }

        .report-table tbody td {
            border: 1px solid #d1d5db;
            padding: 6px 6px;
            font-size: 10px;
            vertical-align: middle;
        }

        .report-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .report-table tfoot td {
            border: 1px solid #9ca3af;
            padding: 7px 8px;
            font-size: 10px;
            font-weight: bold;
            background: #f3f4f6;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }

        .total-box {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
        }

        .total-box td {
            border: 1px solid #9ca3af;
            padding: 10px 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .total-label {
            text-align: right;
            background: #dbeafe;
            color: #111827;
        }

        .total-value {
            text-align: right;
            background: #eff6ff;
            color: #111827;
            width: 180px;
        }

        .footer {
            position: fixed;
            bottom: -12px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #d1d5db;
            padding-top: 6px;
        }

        .no-data {
            border: 1px solid #d1d5db;
            padding: 14px;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
            background: #f9fafb;
        }
    </style>
</head>
<body>
    @php
        $paymentMethodLabels = [
            'pix' => 'PIX',
            'bank_transfer' => 'Transferência',
            'cash' => 'Dinheiro',
        ];

        $statusLabels = [
            'draft' => 'Rascunho',
            'paid' => 'Pago',
            'canceled' => 'Cancelado',
            'integrated_payroll' => 'Integrado na Folha',
        ];
    @endphp

    <div class="header">
        <div class="title">Relatório de Pagamento de Adiantamento</div>
        <div class="subtitle">
            Documento gerado em {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <table class="filters-table">
        <tr>
            <td class="filters-label">Período</td>
            <td>
                {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                até
                {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </td>
            <td class="filters-label">Pagamento</td>
            <td>{{ $paymentMethodLabels[$paymentMethod] ?? 'Todos' }}</td>
        </tr>
        <tr>
            <td class="filters-label">Status</td>
            <td>{{ $statusLabels[$status] ?? 'Todos' }}</td>
            <td class="filters-label">Total Geral</td>
            <td>R$ {{ number_format($totalAmount, 2, ',', '.') }}</td>
        </tr>
    </table>

    @forelse($rows as $company => $works)
        <div class="company-block">
            <div class="company-title">Empresa: {{ $company }}</div>

            @foreach($works as $work => $data)
                <div class="work-title">Obra: {{ $work }}</div>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width: 23%;">Colaborador</th>
                            <th style="width: 11%;">Matrícula</th>
                            <th style="width: 14%;">Cargo</th>
                            <th style="width: 10%;">Data</th>
                            <th style="width: 10%;">Tipo PIX</th>
                            <th style="width: 14%;">Chave PIX</th>
                            <th style="width: 10%;">Documento</th>
                            <th style="width: 8%;">Valor</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($data['rows'] as $row)
                            <tr>
                                <td class="text-left">{{ $row['employee_name'] ?: '-' }}</td>
                                <td class="text-center">{{ $row['code'] ?: '-' }}</td>
                                <td class="text-left">{{ $row['job_role'] ?: '-' }}</td>
                                <td class="text-center">{{ $row['advance_date'] ?: '-' }}</td>
                                <td class="text-center">{{ $row['pix_key_type'] ? strtoupper($row['pix_key_type']) : '-' }}</td>
                                <td class="text-left">{{ $row['pix_key'] ?: '-' }}</td>
                                <td class="text-center">{{ $row['pix_holder_document'] ?: '-' }}</td>
                                <td class="text-right">R$ {{ number_format((float) $row['amount'], 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center muted">Nenhum registro encontrado para esta obra.</td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="7" class="text-right">Total da Obra</td>
                            <td class="text-right">R$ {{ number_format((float) $data['total'], 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endforeach
        </div>
    @empty
        <div class="no-data">
            Nenhum dado encontrado para os filtros informados.
        </div>
    @endforelse

    <table class="total-box">
        <tr>
            <td class="total-label">TOTAL GERAL</td>
            <td class="total-value">R$ {{ number_format((float) $totalAmount, 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        Relatório de Pagamento de Adiantamento
    </div>
</body>
</html>