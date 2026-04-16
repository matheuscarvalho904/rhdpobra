<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Rescisão</title>

<style>
@page { margin: 10px; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    margin: 0;
}

.via {
    width: 100%;
    height: 49%;
    border: 1px solid #000;
    padding: 8px;
    margin-bottom: 6px;
}

.header {
    border-bottom: 1px solid #000;
    margin-bottom: 6px;
}

.title {
    font-size: 12px;
    font-weight: bold;
}

.subtitle {
    font-size: 9px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

td, th {
    border: 1px solid #000;
    padding: 3px;
}

th {
    background: #eee;
}

.right { text-align: right; }
.center { text-align: center; }

.summary {
    font-weight: bold;
}

.net {
    background: #dbeafe;
    font-size: 10px;
    font-weight: bold;
}

.signature {
    margin-top: 20px;
    text-align: center;
}

.line {
    margin-top: 30px;
    border-top: 1px solid #000;
}
</style>

</head>
<body>

@for($i = 1; $i <= 2; $i++)
<div class="via">

    <div class="header">
        <div class="title">
            Termo de Rescisão — {{ $i === 1 ? 'Via Empresa' : 'Via Colaborador' }}
        </div>
        <div class="subtitle">
            {{ $company?->name ?? '-' }}
        </div>
    </div>

    <table>
        <tr>
            <td><b>Colaborador:</b> {{ $employee->name }}</td>
            <td><b>CPF:</b> {{ $employee->cpf }}</td>
            <td><b>Matrícula:</b> {{ $contract->registration_number ?? '-' }}</td>
        </tr>
        <tr>
            <td><b>Data:</b> {{ optional($termination->termination_date)->format('d/m/Y') }}</td>
            <td><b>Aviso:</b> {{ $termination->notice_type }}</td>
            <td><b>Dias:</b> {{ $termination->notice_days }}</td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>Cód</th>
                <th>Descrição</th>
                <th class="center">Ref</th>
                <th class="right">Proventos</th>
                <th class="right">Descontos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['code'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td class="center">{{ number_format($item['reference'],2,',','.') }}</td>

                    <td class="right">
                        {{ $item['type'] === 'provento' ? 'R$ '.number_format($item['amount'],2,',','.') : '' }}
                    </td>

                    <td class="right">
                        {{ $item['type'] === 'desconto' ? 'R$ '.number_format($item['amount'],2,',','.') : '' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <table class="summary">
        <tr>
            <td>Total Bruto</td>
            <td class="right">R$ {{ number_format($result['gross_amount'],2,',','.') }}</td>
        </tr>
        <tr>
            <td>Descontos</td>
            <td class="right">R$ {{ number_format($result['total_discounts'],2,',','.') }}</td>
        </tr>
        <tr class="net">
            <td>Líquido</td>
            <td class="right">R$ {{ number_format($result['net_amount'],2,',','.') }}</td>
        </tr>
    </table>

    <div class="signature">
        <div class="line">Assinatura</div>
    </div>

</div>
@endfor

</body>
</html>