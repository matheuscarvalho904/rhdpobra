<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Prestação de Serviços - Pessoa Jurídica</title>

    <style>
        @page {
            margin: 24px 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.55;
            color: #111827;
        }

        .page {
            width: 100%;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .paragraph {
            text-align: justify;
            margin-bottom: 8px;
        }

        .clause {
            margin-bottom: 10px;
            text-align: justify;
        }

        .footer-text {
            margin-top: 18px;
            text-align: justify;
        }

        .city-date {
            margin-top: 18px;
        }

        .signatures {
            margin-top: 34px;
            width: 100%;
        }

        .signature-col {
            width: 45%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
            margin-right: 4%;
        }

        .signature-line {
            margin-top: 48px;
            border-top: 1px solid #111827;
            padding-top: 6px;
            font-size: 10px;
        }

        .witnesses {
            margin-top: 20px;
            width: 100%;
        }

        .witness-col {
            width: 45%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
            margin-right: 4%;
        }

        .witness-line {
            margin-top: 44px;
            border-top: 1px solid #111827;
            padding-top: 6px;
            font-size: 10px;
        }
    </style>
</head>
<body>

@php
    \Carbon\Carbon::setLocale('pt_BR');

    $formatCnpj = function (?string $value): string {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if (strlen($digits) !== 14) {
            return $value ?: '-';
        }

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
    };

    // 🔥 CORREÇÃO IMPORTANTE
    $formatCpf = function (?string $value): string {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if (strlen($digits) !== 11) {
            return $value ?: '-';
        }

        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
    };

    $companyAddress = trim(collect([
        $company->address ?? null,
        $company->number ?? null,
        $company->complement ?? null,
        $company->district ?? null,
        $company->city ?? null,
        $company->state ?? null,
    ])->filter()->implode(', '));

    $startDate = $employee->admission_date
        ? \Carbon\Carbon::parse($employee->admission_date)
        : now();

    // 🔥 NOVA LÓGICA DE PRAZO
    $contractStartDate = $employee->service_contract_start_date
        ? \Carbon\Carbon::parse($employee->service_contract_start_date)
        : $startDate;

    $contractEndDate = $employee->service_contract_end_date
        ? \Carbon\Carbon::parse($employee->service_contract_end_date)
        : null;

    $contractTermLabel = match ($employee->service_contract_term) {
        '30_days' => '30 dias',
        '60_days' => '60 dias',
        '90_days' => '90 dias',
        '180_days' => '180 dias',
        '12_months' => '12 meses',
        'indefinite' => 'prazo indeterminado',
        default => 'prazo indeterminado',
    };

    $value = number_format((float) ($employee->salary ?? 0), 2, ',', '.');
    $city = $company->city ?? $branch->city ?? $employee->city ?? 'Aripuanã';
@endphp

<div class="page">
    <div class="title">Contrato de Prestação de Serviços – Pessoa Jurídica</div>

    <div class="paragraph">
        Pelo presente instrumento e na melhor forma de direito, as partes:
    </div>

    <div class="paragraph">
        <strong>1. CONTRATANTE:</strong>
        <strong>{{ mb_strtoupper($company->name ?? '-') }}</strong>,
        com sede em {{ $companyAddress ?: 'endereço não informado' }},
        inscrita no CNPJ sob nº <strong>{{ $formatCnpj($company->cnpj ?? null) }}</strong>,
        representada neste ato por seu responsável legal 
        <strong>{{ mb_strtoupper($company->legal_representative_name ?? 'REPRESENTANTE LEGAL') }}</strong>,

        @if($company->legal_representative_role)
            {{ mb_strtoupper($company->legal_representative_role) }},
        @endif

        @if($company->legal_representative_cpf)
            portador do CPF nº <strong>{{ $formatCpf($company->legal_representative_cpf) }}</strong>,
        @endif

        @if($company->legal_representative_rg)
            e RG nº <strong>{{ $company->legal_representative_rg }}</strong>.
        @endif
    </div>

    <div class="paragraph">
        <strong>2. CONTRATADA:</strong>
        <strong>{{ mb_strtoupper($employee->name ?? '-') }}</strong>,
        doravante denominada CONTRATADA.
    </div>

    <div class="clause">
        <strong>CLÁUSULA I</strong><br><br>
        A CONTRATADA prestará serviços à CONTRATANTE na área de
        <strong>{{ mb_strtoupper($jobRole?->name ?? 'SERVIÇOS ESPECIALIZADOS') }}</strong>.
    </div>

    <div class="clause">
        <strong>CLÁUSULA II</strong><br><br>
        Valor de <strong>R$ {{ $value }}</strong>.
    </div>

    <div class="clause">
        <strong>CLÁUSULA III</strong><br><br>
        Não existe vínculo empregatício.
    </div>

    <!-- 🔥 CLÁUSULA CORRIGIDA -->
    <div class="clause">
        <strong>CLÁUSULA IX</strong><br><br>

        O presente contrato inicia-se em <strong>{{ $contractStartDate->format('d/m/Y') }}</strong>,

        @if(($employee->service_contract_term ?? null) === 'indefinite' || ! $contractEndDate)

            vigorando por <strong>prazo indeterminado</strong>, podendo ser rescindido por qualquer das partes
            mediante comunicação prévia.

        @else

            vigorando pelo prazo de <strong>{{ $contractTermLabel }}</strong>,
            com término previsto para <strong>{{ $contractEndDate->format('d/m/Y') }}</strong>.

            <br><br>

            podendo ser prorrogado mediante acordo entre as partes.

        @endif
    </div>

    <div class="clause">
        <strong>CLÁUSULA X</strong><br><br>
        Responsabilidade tributária da contratada.
    </div>

    <div class="clause">
        <strong>CLÁUSULA XI</strong><br><br>
        Foro da comarca da empresa.
    </div>

    <div class="city-date">
        {{ mb_strtoupper($city) }}, {{ $contractStartDate->format('d/m/Y') }}.
    </div>

    <div class="signatures">
        <div class="signature-col">
            <div class="signature-line">CONTRATANTE</div>
        </div>

        <div class="signature-col">
            <div class="signature-line">{{ mb_strtoupper($employee->name ?? 'CONTRATADA') }}</div>
        </div>
    </div>

</div>

</body>
</html>