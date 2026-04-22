<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Termo de Compromisso de Estágio</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; line-height: 1.55; }
        .header { text-align: center; margin-bottom: 22px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; }
        .subtitle { font-size: 11px; color: #4b5563; }
        .paragraph { text-align: justify; margin-bottom: 12px; }
        .signature-box { width: 45%; display: inline-block; text-align: center; vertical-align: top; margin-right: 4%; }
        .signature-line { margin-top: 50px; border-top: 1px solid #111827; padding-top: 6px; font-size: 10px; }
        .signatures { margin-top: 42px; width: 100%; }
    </style>
</head>
<body>
    @php
        $formatCpf = function (?string $value): string {
            $digits = preg_replace('/\D+/', '', (string) $value);
            if (strlen($digits) !== 11) return $value ?: '-';
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        };

        $bolsa = number_format((float) ($employee->salary ?? 0), 2, ',', '.');
    @endphp

    <div class="header">
        <div class="title">Termo de Compromisso de Estágio</div>
        <div class="subtitle">Estágio</div>
    </div>

    <div class="paragraph">
        Pelo presente termo, de um lado <strong>{{ $company->name ?? '-' }}</strong>, doravante denominada CONCEDENTE,
        e de outro <strong>{{ $employee->name ?? '-' }}</strong>, CPF nº
        <strong>{{ $formatCpf($employee->cpf ?? null) }}</strong>, doravante denominado(a) ESTAGIÁRIO(A),
        ajustam o presente compromisso de estágio.
    </div>

    <div class="paragraph">
        <strong>CLÁUSULA 1ª – OBJETO.</strong>
        O estágio será desenvolvido na área de
        <strong>{{ $jobRole?->name ?? 'atividade de apoio e aprendizado' }}</strong>,
        podendo ocorrer em {{ $work?->name ?? 'local definido pela concedente' }}.
    </div>

    <div class="paragraph">
        <strong>CLÁUSULA 2ª – BOLSA.</strong>
        Pela atividade de estágio, será paga bolsa no valor de
        <strong>R$ {{ $bolsa }}</strong>,
        quando aplicável, conforme política da concedente.
    </div>

    <div class="paragraph">
        <strong>CLÁUSULA 3ª – JORNADA.</strong>
        A jornada seguirá o regime definido pela concedente,
        atualmente identificado como <strong>{{ $workShift?->name ?? 'jornada a definir' }}</strong>,
        respeitando a natureza do estágio.
    </div>

    <div class="paragraph">
        <strong>CLÁUSULA 4ª – VIGÊNCIA.</strong>
        O presente termo inicia-se em <strong>{{ optional($employee->admission_date)->format('d/m/Y') ?: '-' }}</strong>,
        com duração conforme interesse das partes e regras aplicáveis ao estágio.
    </div>

    <div class="paragraph">
        <strong>CLÁUSULA 5ª – FINALIDADE PEDAGÓGICA.</strong>
        O estágio possui finalidade educativa e de aprendizagem prática, não se confundindo automaticamente com contrato de trabalho celetista.
    </div>

    <div class="paragraph">{{ $cityDate }}.</div>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">{{ $company->name ?? 'CONCEDENTE' }}<br>CONCEDENTE</div>
        </div>

        <div class="signature-box">
            <div class="signature-line">{{ $employee->name ?? 'ESTAGIÁRIO(A)' }}<br>ESTAGIÁRIO(A)</div>
        </div>
    </div>
</body>
</html>