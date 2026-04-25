<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Colaboradores por Obra</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 13px;
            background: #e5e7eb;
            padding: 6px;
            margin-top: 18px;
            margin-bottom: 0;
        }

        .subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            text-align: left;
        }

        .total {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Relatório de Colaboradores por Obra</h1>
    <div class="subtitle">
        Gerado em {{ now()->format('d/m/Y H:i') }}
    </div>

    @php
        $totalGeral = 0;
    @endphp

    @foreach($groups as $workName => $employees)
        @php
            $totalGeral += $employees->count();
        @endphp

        <h2>{{ $workName }} — {{ $employees->count() }} colaborador(es)</h2>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Cargo</th>
                    <th>Departamento</th>
                    <th>Admissão</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                    <tr>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->cpf }}</td>
                        <td>{{ $employee->jobRole?->name ?? '-' }}</td>
                        <td>{{ $employee->department?->name ?? '-' }}</td>
                        <td>{{ optional($employee->hire_date)->format('d/m/Y') }}</td>
                        <td>{{ $employee->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="total">
        Total geral de colaboradores: {{ $totalGeral }}
    </div>
</body>
</html>