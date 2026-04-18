<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Pagamento de Adiantamentos</title>
    <style>
        @page { margin: 18px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
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
            font-size: 10px;
            color: #4b5563;
        }

        .filter-box,
        .section-box {
            border: 1px solid #cbd5e1;
            margin-bottom: 12px;
        }

        .box-title {
            background: #e5e7eb;
            padding: 6px 8px;
            font-weight: bold;
            border-bottom: 1px solid #cbd5e1;
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
            padding: 5px 6px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .company-title,
        .work-title {
            font-weight: bold;
            margin: 10px 0 6px;
        }

        .total-box {
            margin-top: 14px;
            border: 1px solid #cbd5e1;
            background: #eff6ff;
            padding: 10px;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Relatório de Pagamento de Adiantamentos</div>
        <div class="subtitle">
            Gerado em {{ $generatedAt->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="filter-box">
        <div class="box-title">Filtros Aplicados</div>
        <div class="box-body">
            <table>
                <tr>
                    <td><strong>Empresa:</strong> {{ $filters['company'] }}</td>
                    <td><strong>Filial:</strong> {{ $filters['branch'] }}</td>
                    <td><strong>Obra:</strong> {{ $filters['work'] }}</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong> {{ $filters['status'] }}</td>
                    <td><strong>Pagamento:</strong> {{ $filters['payment_method'] }}</td>
                    <td><strong>Período:</strong> {{ $filters['date_from'] }} até {{ $filters['date_to'] }}</td>
                </tr>
            </table>
        </div>
    </div>

    @foreach($rows as $company => $works)
        <div class="section-box">
            <div class="box-title">Empresa: {{ $company }}</div>
            <div class="box-body">
                @foreach($works as $work => $data)
                    <div class="work-title">Obra: {{ $work }}</div>

                    <table>
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>Matrícula</th>
                                <th>Cargo</th>
                                <th>Data</th>
                                <th>Pagamento</th>
                                <th>Tipo PIX</th>
                                <th>Chave PIX</th>
                                <th>Documento</th>
                                <th>Status</th>
                                <th class="right">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['rows'] as $row)
                                <tr>
                                    <td>{{ $row['employee_name'] }}</td>
                                    <td>{{ $row['code'] }}</td>
                                    <td>{{ $row['job_role'] }}</td>
                                    <td>{{ $row['advance_date'] }}</td>
                                    <td>{{ $row['payment_method'] }}</td>
                                    <td>{{ $row['pix_key_type'] }}</td>
                                    <td>{{ $row['pix_key'] }}</td>
                                    <td>{{ $row['pix_holder_document'] }}</td>
                                    <td>{{ $row['status'] }}</td>
                                    <td class="right">R$ {{ number_format($row['amount'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="9" class="right"><strong>Total da Obra</strong></td>
                                <td class="right"><strong>R$ {{ number_format($data['total'], 2, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="total-box">
        Total Geral do Relatório: R$ {{ number_format($totalAmount, 2, ',', '.') }}
    </div>
</body>
</html>