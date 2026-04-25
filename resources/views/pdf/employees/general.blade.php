<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Geral de Colaboradores</title>

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

        .subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #e5e7eb;
            font-weight: bold;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            text-align: left;
        }

        .footer {
            margin-top: 15px;
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>Relatório Geral de Colaboradores</h1>
    <div class="subtitle">
        Gerado em {{ now()->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Obra</th>
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
                    <td>{{ $employee->work?->name ?? 'Sem obra' }}</td>
                    <td>{{ $employee->jobRole?->name ?? '-' }}</td>
                    <td>{{ $employee->department?->name ?? '-' }}</td>
                    <td>{{ optional($employee->hire_date)->format('d/m/Y') }}</td>
                    <td>{{ $employee->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Total de colaboradores: {{ $employees->count() }}
    </div>
</body>
</html>