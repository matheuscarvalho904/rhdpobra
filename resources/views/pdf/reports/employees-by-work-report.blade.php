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
            margin-bottom: 6px;
        }

        h2 {
            font-size: 13px;
            margin: 18px 0 8px;
        }

        .small {
            font-size: 9px;
            color: #4b5563;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
        }

        th {
            background: #e5e7eb;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Relatório de Colaboradores por Obra</h1>
    <p class="small">Gerado em {{ now()->format('d/m/Y H:i') }}</p>

    @forelse($groupedEmployees as $workName => $employees)
        <h2>{{ $workName }} ({{ $employees->count() }})</h2>

        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Colaborador</th>
                    <th>Cargo</th>
                    <th>Contrato</th>
                    <th>Status</th>
                    <th>Admissão</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                    <tr>
                        <td>{{ $employee->code ?? '-' }}</td>
                        <td>{{ $employee->name ?? '-' }}</td>
                        <td>{{ $employee->jobRole?->name ?? '-' }}</td>
                        <td>{{ $employee->contractType?->name ?? '-' }}</td>
                        <td>{{ $employee->status ?? '-' }}</td>
                        <td>{{ optional($employee->admission_date)->format('d/m/Y') ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>Nenhum colaborador encontrado.</p>
    @endforelse
</body>
</html>