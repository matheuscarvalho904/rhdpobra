<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Aprendizagem</title>

    <style>
        @page { margin: 18px 22px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.22;
            color: #111827;
        }

        .page { width: 100%; }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .paragraph,
        .clause,
        .footer-text {
            text-align: justify;
            margin-bottom: 6px;
        }

        .city-date {
            margin-top: 14px;
        }

        .signatures,
        .witnesses {
            margin-top: 18px;
            width: 100%;
        }

        .signature-col,
        .witness-col {
            width: 45%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
            margin-right: 4%;
        }

        .signature-line,
        .witness-line {
            margin-top: 28px;
            border-top: 1px solid #111827;
            padding-top: 5px;
            font-size: 9.5px;
        }
    </style>
</head>
<body>
@php
    \Carbon\Carbon::setLocale('pt_BR');

    $formatCpf = function (?string $value): string {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if (strlen($digits) !== 11) return $value ?: '-';
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
    };

    $formatCnpj = function (?string $value): string {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if (strlen($digits) !== 14) return $value ?: '-';
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
    };

    $salary = number_format((float) ($employee->salary ?? 0), 2, ',', '.');

    $companyAddress = trim(collect([
        $company->address ?? null,
        $company->number ?? null,
        $company->district ?? null,
        $company->city ?? null,
        $company->state ?? null,
    ])->filter()->implode(', '));

    $employeeAddress = trim(collect([
        $employee->address ?? null,
        $employee->number ?? null,
        $employee->district ?? null,
        $employee->city ?? null,
        $employee->state ?? null,
    ])->filter()->implode(', '));

    $admissionDate = $employee->admission_date
        ? \Carbon\Carbon::parse($employee->admission_date)
        : now();

    $endDate = $admissionDate->copy()->addMonths(24);

    $city = $company->city ?? $employee->city ?? 'Aripuanã';
@endphp

<div class="page">

    <div class="title">Contrato de Aprendizagem</div>

    <div class="paragraph">
        Pelo presente instrumento particular de CONTRATO DE APRENDIZAGEM, firmado nos termos da legislação trabalhista vigente, especialmente art. 428 da CLT, entre:
    </div>

    <div class="paragraph">
    <strong>EMPREGADORA:</strong>
    <strong>{{ mb_strtoupper($company->name ?? '-') }}</strong>,
    pessoa jurídica de direito privado, inscrita no CNPJ sob nº
    <strong>{{ $formatCnpj($company->cnpj ?? null) }}</strong>,
    com sede em {{ $companyAddress ?: 'endereço não informado' }},
    neste ato representada por seu representante legal
    <strong>{{ mb_strtoupper($company->legal_representative_name ?? 'NÃO INFORMADO') }}</strong>,
    inscrito no CPF sob nº
    <strong>{{ $formatCpf($company->legal_representative_cpf ?? null) }}</strong>,
    na qualidade de
    <strong>{{ mb_strtoupper($company->legal_representative_role ?? 'REPRESENTANTE LEGAL') }}</strong>.
</div>

    <div class="paragraph">
        <strong>APRENDIZ:</strong>
        <strong>{{ mb_strtoupper($employee->name ?? '-') }}</strong>,
        inscrito no CPF sob nº <strong>{{ $formatCpf($employee->cpf ?? null) }}</strong>,
        residente em {{ $employeeAddress ?: 'endereço não informado' }}.
    </div>

    <div class="clause">
        <strong>CLÁUSULA I – OBJETO</strong><br><br>
        O presente contrato tem por objeto a contratação do APRENDIZ para formação técnico-profissional metódica, compatível com seu desenvolvimento físico, moral e psicológico, mediante atividades práticas e teóricas supervisionadas.
    </div>

    <div class="clause">
        <strong>CLÁUSULA II – FUNÇÃO</strong><br><br>
        O APRENDIZ exercerá a função de <strong>{{ mb_strtoupper($jobRole?->name ?? 'APRENDIZ') }}</strong>.
    </div>

    <div class="clause">
        <strong>CLÁUSULA III – JORNADA</strong><br><br>
        A jornada será compatível com a legislação aplicável ao programa de aprendizagem, respeitando os limites legais e atividades educacionais.
    </div>

    <div class="clause">
        <strong>CLÁUSULA IV – REMUNERAÇÃO</strong><br><br>
        O APRENDIZ perceberá remuneração mensal de <strong>R$ {{ $salary }}</strong>, observando-se a legislação vigente.
    </div>

    <div class="clause">
        <strong>CLÁUSULA V – PRAZO</strong><br><br>
        Este contrato é celebrado por prazo determinado, iniciando-se em <strong>{{ $admissionDate->format('d/m/Y') }}</strong>
        e encerrando-se em <strong>{{ $endDate->format('d/m/Y') }}</strong>, salvo hipóteses legais.
    </div>

    <div class="clause">
        <strong>CLÁUSULA VI – OBRIGAÇÕES</strong><br><br>
        O APRENDIZ compromete-se a cumprir suas atividades práticas e teóricas, observar normas internas, segurança do trabalho e orientações da EMPREGADORA.
    </div>

    <div class="clause">
        <strong>CLÁUSULA VII – RESCISÃO</strong><br><br>
        O contrato poderá ser rescindido nas hipóteses previstas em lei.
    </div>

    <div class="footer-text">
        E por estarem justos e contratados, firmam o presente instrumento em duas vias.
    </div>

    <div class="city-date">
        {{ mb_strtoupper($city) }}, {{ $admissionDate->translatedFormat('d \\d\\e F \\d\\e Y') }}
    </div>

    <div class="signatures">
        <div class="signature-col">
    <div class="signature-line">
        {{ mb_strtoupper($company->legal_representative_name ?? 'REPRESENTANTE LEGAL') }}<br>
        {{ mb_strtoupper($company->legal_representative_role ?? 'REPRESENTANTE LEGAL') }}<br>
        {{ mb_strtoupper($company->name ?? 'EMPREGADORA') }}
    </div>

        <div class="signature-col">
            <div class="signature-line">
                {{ mb_strtoupper($employee->name ?? 'APRENDIZ') }}
            </div>
        </div>
    </div>

    <div class="witnesses">
        <div class="witness-col">
            <div class="witness-line">1ª TESTEMUNHA</div>
        </div>

        <div class="witness-col">
            <div class="witness-line">2ª TESTEMUNHA</div>
        </div>
    </div>

</div>
</body>
</html>