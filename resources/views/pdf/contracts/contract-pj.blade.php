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

        $formatCpf = function (?string $value): string {
            $digits = preg_replace('/\D+/', '', (string) $value);

            if (strlen($digits) !== 11) {
                return $value ?: '-';
            }

            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        };

        $formatCnpj = function (?string $value): string {
            $digits = preg_replace('/\D+/', '', (string) $value);

            if (strlen($digits) !== 14) {
                return $value ?: '-';
            }

            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
        };

        $companyAddress = trim(collect([
            $company->address ?? null,
            $company->number ?? null,
            $company->complement ?? null,
            $company->district ?? null,
            $company->city ?? null,
            $company->state ?? null,
        ])->filter()->implode(', '));

        $startDate = $employee->contract_start_date
            ? \Carbon\Carbon::parse($employee->contract_start_date)
            : ($employee->admission_date ? \Carbon\Carbon::parse($employee->admission_date) : now());

        $contractEndDate = $employee->contract_end_date
            ? \Carbon\Carbon::parse($employee->contract_end_date)
            : null;

        $isFixedTerm = ($employee->contract_term_type ?? 'indeterminate') === 'fixed'
            && $employee->contract_term_days
            && $contractEndDate;

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
            <strong>{{ mb_strtoupper($jobRole?->name ?? 'SERVIÇOS ESPECIALIZADOS') }}</strong>,
            podendo atuar em <strong>{{ $work?->name ?? 'local a definir' }}</strong>
            ou em outros locais vinculados à execução do objeto contratual.
        </div>

        <div class="clause">
            <strong>CLÁUSULA II</strong><br><br>
            Pela execução dos serviços, a CONTRATANTE pagará à CONTRATADA a importância de
            <strong>R$ {{ $value }}</strong>,
            mediante emissão do documento fiscal cabível, até o quinto dia útil após sua apresentação,
            conforme processo interno de conferência e liberação.
        </div>

        <div class="clause">
            <strong>CLÁUSULA III</strong><br><br>
            A CONTRATADA executará os serviços com autonomia técnica, administrativa e organizacional,
            não existindo vínculo empregatício entre a CONTRATANTE e a CONTRATADA,
            tampouco entre a CONTRATANTE e os profissionais eventualmente alocados pela CONTRATADA.
        </div>

        <div class="clause">
            <strong>CLÁUSULA IV</strong><br><br>
            Constituem obrigações da CONTRATADA:
            executar os serviços com qualidade e diligência;
            manter regularidade fiscal, trabalhista e previdenciária;
            responder por seus profissionais, encargos e obrigações legais;
            e cumprir as normas técnicas e operacionais aplicáveis à atividade desenvolvida.
        </div>

        <div class="clause">
            <strong>CLÁUSULA V</strong><br><br>
            Os serviços poderão ser executados em qualquer local indicado pela CONTRATANTE,
            inclusive obras, unidades operacionais, canteiros, escritórios ou frentes de trabalho,
            conforme a necessidade do projeto ou contrato principal.
        </div>

        <div class="clause">
            <strong>CLÁUSULA VI</strong><br><br>
            A CONTRATADA compromete-se a cumprir e fazer cumprir todas as normas de segurança do trabalho,
            responsabilizando-se integralmente por seus colaboradores, prepostos e terceiros vinculados à execução dos serviços,
            inclusive quanto ao uso correto de Equipamentos de Proteção Individual (EPI), quando exigidos.
        </div>

        <div class="clause">
            <strong>CLÁUSULA VII</strong><br><br>
            A CONTRATADA responderá integralmente pelos danos materiais, operacionais, técnicos ou financeiros
            que causar à CONTRATANTE ou a terceiros, quando decorrentes de ação, omissão, culpa ou dolo
            no exercício das atividades contratadas.
        </div>

        <div class="clause">
            <strong>CLÁUSULA VIII</strong><br><br>
            A CONTRATADA compromete-se a manter absoluto sigilo sobre quaisquer informações, documentos,
            processos, estratégias, dados técnicos, comerciais, financeiros ou operacionais da CONTRATANTE,
            não podendo reproduzi-los, divulgá-los ou utilizá-los para finalidade diversa da execução contratual.
        </div>

        <div class="clause">
            <strong>CLÁUSULA IX</strong><br><br>
            @if($isFixedTerm)
                O presente contrato inicia-se em <strong>{{ $startDate->format('d/m/Y') }}</strong>,
                com prazo determinado de <strong>{{ (int) $employee->contract_term_days }} dias</strong>,
                encerrando-se automaticamente em <strong>{{ $contractEndDate->format('d/m/Y') }}</strong>,
                salvo prorrogação formal por escrito entre as partes ou rescisão antecipada conforme as condições pactuadas.
            @else
                O presente contrato inicia-se em <strong>{{ $startDate->format('d/m/Y') }}</strong>,
                vigorando por prazo indeterminado, podendo ser rescindido por qualquer das partes
                mediante comunicação prévia, observadas as obrigações pendentes até a data do encerramento.
            @endif
        </div>

        <div class="clause">
            <strong>CLÁUSULA X</strong><br><br>
            A CONTRATADA será exclusiva responsável pelos tributos, encargos fiscais, previdenciários,
            trabalhistas e comerciais inerentes à sua atividade e aos profissionais que utilizar na execução dos serviços,
            inexistindo solidariedade automática da CONTRATANTE, salvo nas hipóteses expressamente previstas em lei.
        </div>

        <div class="clause">
            <strong>CLÁUSULA XI</strong><br><br>
            Para dirimir quaisquer controvérsias oriundas deste contrato,
            fica eleito o foro da comarca da sede da CONTRATANTE,
            com renúncia expressa de qualquer outro, por mais privilegiado que seja.
        </div>

        <div class="footer-text">
            E por estarem justas e contratadas, as partes firmam o presente instrumento em duas vias de igual teor e forma.
        </div>

        <div class="city-date">
            {{ mb_strtoupper($city) }}, {{ $startDate->locale('pt_BR')->translatedFormat('d \\d\\e F \\d\\e Y') }}.
        </div>

        <div class="signatures">
            <div class="signature-col">
                <div class="signature-line">
                    CONTRATANTE
                </div>
            </div>

            <div class="signature-col">
                <div class="signature-line">
                    {{ mb_strtoupper($employee->name ?? 'CONTRATADA') }}
                </div>
            </div>
        </div>

        <div class="witnesses">
            <div class="witness-col">
                <div class="witness-line">
                    1ª TESTEMUNHA
                </div>
            </div>

            <div class="witness-col">
                <div class="witness-line">
                    2ª TESTEMUNHA
                </div>
            </div>
        </div>
    </div>

</body>
</html>