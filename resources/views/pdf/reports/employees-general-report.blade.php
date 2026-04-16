<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Geral de Colaboradores</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; }
        h1 { font-size: 16px; margin-bottom: 8px; }
        .small { font-size: 8px; color: #4b5563; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 5px; }
        th { background: #e5e7eb; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Relatório Geral de Colaboradores</h1>
    <p class="small">Gerado em {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Colaborador</th>
                <th>Empresa</th>
                <th>Filial</th>
                <th>Obra</th>
                <th>Cargo</th>
                <th>Contrato</th>
                <th>Status</th>
                <th>Admissão</th>
                <th class="right">Salário</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td>{{ $employee->code ?? '-' }}</td>
                    <td>{{ $employee->name ?? '-' }}</td>
                    <td>{{ $employee->company?->name ?? '-' }}</td>
                    <td>{{ $employee->branch?->name ?? '-' }}</td>
                    <td>{{ $employee->work?->name ?? '-' }}</td>
                    <td>{{ $employee->jobRole?->name ?? '-' }}</td>
                    <td>{{ $employee->contractType?->name ?? '-' }}</td>
                    <td>{{ $employee->status ?? '-' }}</td>
                    <td>{{ optional($employee->admission_date)->format('d/m/Y') ?: '-' }}</td>
                    <td class="right">R$ {{ number_format((float) ($employee->salary ?? 0), 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">Nenhum colaborador encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>