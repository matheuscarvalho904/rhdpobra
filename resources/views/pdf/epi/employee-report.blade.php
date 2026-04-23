<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de EPI do Colaborador</title>

    <style>
        @page { margin: 24px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 16px;
        }

        .info {
            margin-bottom: 12px;
        }

        .section {
            margin-top: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .small {
            font-size: 10px;
            color: #4b5563;
        }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');

        $formatCpf = function (?string $value): string {
            $digits = preg_replace('/\D+/', '', (string) $value);

            if (strlen($digits) !== 11) {
                return $value ?: '-';
            }

            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        };
    @endphp

    <h1>Relatório de EPIs do Colaborador</h1>

    <div class="info">
        <p><strong>Colaborador:</strong> {{ $employee->name ?? '-' }}</p>
        <p><strong>CPF:</strong> {{ $formatCpf($employee->cpf ?? null) }}</p>
        <p><strong>Cargo:</strong> {{ $employee->jobRole?->name ?? '-' }}</p>
        <p><strong>Obra:</strong> {{ $employee->work?->name ?? '-' }}</p>
        <p><strong>Empresa:</strong> {{ $company?->name ?? '-' }}</p>
    </div>

    @forelse($deliveries as $delivery)
        <div class="section">
            <p>
                <strong>Entrega:</strong> {{ optional($delivery->delivery_date)->format('d/m/Y') ?: '-' }}
                &nbsp; | &nbsp;
                <strong>Status:</strong>
                @switch($delivery->status)
                    @case('open') Aberta @break
                    @case('closed') Fechada @break
                    @default {{ $delivery->status ?? '-' }}
                @endswitch
            </p>

            <table>
                <thead>
                    <tr>
                        <th>EPI</th>
                        <th>Qtd.</th>
                        <th>Status</th>
                        <th>Prev. Devolução</th>
                        <th>Devolvido em</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($delivery->items as $item)
                        <tr>
                            <td>{{ $item->epi?->name ?? '-' }}</td>
                            <td>{{ $item->quantity ?? 1 }}</td>
                            <td>
                                @switch($item->status)
                                    @case('delivered') Entregue @break
                                    @case('returned') Devolvido @break
                                    @case('lost') Perdido @break
                                    @case('replaced') Substituído @break
                                    @default {{ $item->status ?? '-' }}
                                @endswitch
                            </td>
                            <td>{{ optional($item->expected_return_date)->format('d/m/Y') ?: '-' }}</td>
                            <td>{{ optional($item->returned_at)->format('d/m/Y') ?: '-' }}</td>
                        </tr>
                        @if($item->notes)
                            <tr>
                                <td colspan="5" class="small">
                                    <strong>Observações do item:</strong> {{ $item->notes }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            @if($delivery->notes)
                <p class="small">
                    <strong>Observações da entrega:</strong> {{ $delivery->notes }}
                </p>
            @endif
        </div>
    @empty
        <p>Nenhuma entrega de EPI encontrada para este colaborador.</p>
    @endforelse
</body>
</html>