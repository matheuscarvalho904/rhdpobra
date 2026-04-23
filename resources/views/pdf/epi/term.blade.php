<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Termo de Entrega de EPI</title>

    <style>
        @page {
            margin: 24px 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #111827;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .paragraph {
            text-align: justify;
            margin-bottom: 10px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .small {
            font-size: 10px;
            color: #374151;
        }

        .signatures {
            margin-top: 38px;
            width: 100%;
        }

        .signature-box {
            width: 45%;
            display: inline-block;
            text-align: center;
            vertical-align: top;
            margin-right: 4%;
        }

        .signature-line {
            margin-top: 55px;
            border-top: 1px solid #111827;
            padding-top: 6px;
            font-size: 10px;
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

        $formatDate = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '-';

        $companyName = $company->name ?? '-';
        $companyCity = $company->city ?? $employee->city ?? 'Aripuanã';
    @endphp

    <div class="title">Termo de Entrega de Equipamento de Proteção Individual - EPI</div>

    <div class="paragraph">
        Pelo presente termo, a empresa <strong>{{ $companyName }}</strong> declara que entregou ao colaborador
        <strong>{{ $employee->name ?? '-' }}</strong>, inscrito no CPF sob nº
        <strong>{{ $formatCpf($employee->cpf ?? null) }}</strong>, os Equipamentos de Proteção Individual
        abaixo descritos, comprometendo-se o colaborador ao uso correto, guarda, conservação e devolução,
        quando aplicável, nos termos das normas internas da empresa e da legislação vigente.
    </div>

    <div class="section-title">Dados da Entrega</div>

    <table>
        <tr>
            <th style="width: 18%;">Colaborador</th>
            <td style="width: 32%;">{{ $employee->name ?? '-' }}</td>
            <th style="width: 18%;">CPF</th>
            <td style="width: 32%;">{{ $formatCpf($employee->cpf ?? null) }}</td>
        </tr>
        <tr>
            <th>Cargo</th>
            <td>{{ $jobRole?->name ?? '-' }}</td>
            <th>Obra</th>
            <td>{{ $work?->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Data da Entrega</th>
            <td>{{ $formatDate($delivery->delivery_date) }}</td>
            <th>Status</th>
            <td>
                @switch($delivery->status)
                    @case('open') Aberta @break
                    @case('closed') Fechada @break
                    @default {{ $delivery->status ?? '-' }}
                @endswitch
            </td>
        </tr>
    </table>

    <div class="section-title">Itens Entregues</div>

    <table>
        <thead>
            <tr>
                <th style="width: 28%;">EPI</th>
                <th style="width: 14%;">Código</th>
                <th style="width: 14%;">CA</th>
                <th style="width: 10%;">Qtd.</th>
                <th style="width: 17%;">Prev. Devolução</th>
                <th style="width: 17%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->epi?->name ?? '-' }}</td>
                    <td>{{ $item->epi?->code ?? '-' }}</td>
                    <td>{{ $item->epi?->ca_number ?? '-' }}</td>
                    <td>{{ $item->quantity ?? 1 }}</td>
                    <td>{{ $formatDate($item->expected_return_date) }}</td>
                    <td>
                        @switch($item->status)
                            @case('delivered') Entregue @break
                            @case('returned') Devolvido @break
                            @case('lost') Perdido @break
                            @case('replaced') Substituído @break
                            @default {{ $item->status ?? '-' }}
                        @endswitch
                    </td>
                </tr>
                @if ($item->notes)
                    <tr>
                        <td colspan="6" class="small">
                            <strong>Observações do item:</strong> {{ $item->notes }}
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="paragraph">
        O colaborador declara estar ciente de que o uso dos EPIs é obrigatório sempre que exigido pela atividade,
        comprometendo-se a utilizá-los de forma correta e contínua, responsabilizando-se pela sua guarda e conservação.
        Compromete-se ainda a comunicar imediatamente qualquer dano, perda, extravio ou necessidade de substituição.
    </div>

    <div class="paragraph">
        O colaborador declara ciência de que o descumprimento das normas de segurança, bem como a recusa injustificada
        do uso dos Equipamentos de Proteção Individual fornecidos, poderá ensejar a aplicação de medidas disciplinares,
        nos termos da legislação trabalhista vigente.
    </div>

    @if($delivery->notes)
        <div class="paragraph">
            <strong>Observações da entrega:</strong> {{ $delivery->notes }}
        </div>
    @endif

    <div class="paragraph">
        {{ mb_strtoupper($companyCity) }}, {{ now()->locale('pt_BR')->translatedFormat('d \\d\\e F \\d\\e Y') }}.
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                {{ $companyName }}<br>
                EMPRESA
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-line">
                {{ $employee->name ?? 'COLABORADOR' }}<br>
                COLABORADOR
            </div>
        </div>
    </div>
</body>
</html>