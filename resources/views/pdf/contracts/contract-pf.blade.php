<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Prestação de Serviços - Pessoa Física</title>

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

        $employeeAddress = trim(collect([
            $employee->address ?? null,
            $employee->number ?? null,
            $employee->complement ?? null,
            $employee->district ?? null,
            $employee->city ?? null,
            $employee->state ?? null,
        ])->filter()->implode(', '));

        $startDate = $employee->admission_date
            ? \Carbon\Carbon::parse($employee->admission_date)
            : now();

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

        $salary = number_format((float) ($employee->salary ?? 0), 2, ',', '.');
        $city = $company->city ?? $branch->city ?? $employee->city ?? 'Aripuanã';
    @endphp

    <div class="page">
        <div class="title">Contrato de Prestação de Serviços – Pessoa Física</div>

        <div class="paragraph">
            Pelo presente instrumento e na melhor forma de direito, as partes:
        </div>

        <div class="paragraph">
            <strong>1. CONTRATANTE:</strong>
            <strong>{{ mb_strtoupper($company->name ?? '-') }}</strong>,
            com sede em {{ $companyAddress ?: 'endereço não informado' }},
            inscrita no CNPJ sob nº <strong>{{ $formatCnpj($company->document ?? null) }}</strong>,
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
            <strong>2. CONTRATADO(A):</strong>
            <strong>{{ mb_strtoupper($employee->name ?? '-') }}</strong>,
            portador(a) do CPF/MF nº <strong>{{ $formatCpf($employee->cpf ?? null) }}</strong>,
            residente e domiciliado(a) em {{ $employeeAddress ?: 'endereço não informado' }}.
        </div>

        <div class="clause">
            <strong>CLÁUSULA I</strong><br><br>
            O(A) CONTRATADO(A) obriga-se a prestar serviços ao CONTRATANTE na função de
            <strong>{{ mb_strtoupper($jobRole?->name ?? 'PRESTAÇÃO DE SERVIÇOS') }}</strong>,
            podendo atuar em <strong>{{ $work?->name ?? 'local a definir' }}</strong>,
            ou em outros locais determinados conforme a necessidade operacional, desde que compatíveis com a natureza da contratação.
        </div>

        <div class="clause">
            <strong>CLÁUSULA II</strong><br><br>
            A prestação dos serviços ocorrerá em alinhamento operacional com o CONTRATANTE,
            inclusive quanto a dias e horários estimados de execução, sem que isso caracterize controle de jornada típico,
            subordinação jurídica ou vínculo empregatício.
        </div>

        <div class="clause">
            <strong>CLÁUSULA III</strong><br><br>
            Pela execução dos serviços, o(a) CONTRATADO(A) receberá a importância de
            <strong>R$ {{ $salary }}</strong>,
            a ser paga até o quinto dia útil do mês subsequente à efetiva prestação dos serviços,
            mediante recibo, comprovação ou instrumento equivalente adotado pela empresa.
        </div>

        <div class="clause">
            <strong>CLÁUSULA IV</strong><br><br>
            O presente instrumento possui natureza estritamente civil de prestação de serviços,
            inexistindo entre as partes qualquer vínculo empregatício, subordinação hierárquica típica,
            habitualidade obrigatória ou enquadramento automático nas disposições da Consolidação das Leis do Trabalho – CLT.
        </div>

        <div class="clause">
            <strong>CLÁUSULA V</strong><br><br>
            O(A) CONTRATADO(A) declara estar ciente de que os serviços poderão ser executados na localidade da contratação
            ou em outras cidades, obras, unidades operacionais ou frentes de serviço do CONTRATANTE,
            conforme a necessidade do serviço contratado.
        </div>

        <div class="clause">
            <strong>CLÁUSULA VI</strong><br><br>
            O(A) CONTRATADO(A) será integralmente responsável pelos tributos, contribuições previdenciárias,
            fiscais e demais encargos incidentes sobre sua atividade profissional,
            inexistindo qualquer responsabilidade do CONTRATANTE sobre obrigações próprias da atividade autônoma exercida.
        </div>

        <div class="clause">
            <strong>CLÁUSULA VII</strong><br><br>
            O(A) CONTRATADO(A) compromete-se a observar as normas internas do CONTRATANTE, especialmente aquelas ligadas
            à segurança do trabalho, à disciplina operacional e à preservação do ambiente de trabalho.
            Quando aplicável, deverá utilizar corretamente os Equipamentos de Proteção Individual (EPI) fornecidos ou exigidos.
        </div>

        <div class="clause">
            <strong>CLÁUSULA VIII</strong><br><br>
            O(A) CONTRATADO(A) responderá integralmente por prejuízos causados ao CONTRATANTE ou a terceiros,
            quando decorrentes de ato doloso ou culposo praticado no exercício das atividades contratadas,
            obrigando-se ao ressarcimento dos danos comprovadamente apurados.
        </div>

        <div class="clause">
            <strong>CLÁUSULA IX</strong><br><br>
            O(A) CONTRATADO(A) obriga-se a manter absoluto sigilo sobre informações, documentos, processos, dados técnicos,
            financeiros, operacionais ou estratégicos do CONTRATANTE,
            comprometendo-se a não divulgar, reproduzir ou utilizar tais informações para fins diversos da execução deste contrato,
            inclusive após seu encerramento.
        </div>

        <div class="clause">
            <strong>CLÁUSULA X</strong><br><br>

            O presente contrato inicia-se em <strong>{{ $contractStartDate->format('d/m/Y') }}</strong>,

            @if(($employee->service_contract_term ?? null) === 'indefinite' || ! $contractEndDate)
                vigorando por <strong>prazo indeterminado</strong>, podendo ser rescindido por qualquer das partes,
                mediante comunicação prévia e sem caracterização de vínculo trabalhista,
                observadas as obrigações eventualmente pendentes até a data do encerramento.
            @else
                vigorando pelo prazo de <strong>{{ $contractTermLabel }}</strong>,
                com término previsto para <strong>{{ $contractEndDate->format('d/m/Y') }}</strong>,
                podendo ser prorrogado mediante acordo entre as partes, por termo aditivo,
                renovação contratual ou continuidade expressamente aceita da prestação dos serviços.

                <br><br>

                Ao término do prazo, inexistindo manifestação contrária das partes,
                poderá haver nova pactuação contratual, preservada a natureza civil da contratação,
                sem caracterização de vínculo empregatício.
            @endif
        </div>

        <div class="clause">
            <strong>CLÁUSULA XI</strong><br><br>
            Para dirimir quaisquer controvérsias oriundas deste contrato,
            fica eleito o foro da comarca da sede do CONTRATANTE,
            com renúncia expressa de qualquer outro, por mais privilegiado que seja.
        </div>

        <div class="footer-text">
            E por estarem de pleno acordo, as partes firmam o presente instrumento em duas vias de igual teor e forma.
        </div>

        <div class="city-date">
            {{ mb_strtoupper($city) }}, {{ $contractStartDate->locale('pt_BR')->translatedFormat('d \\d\\e F \\d\\e Y') }}.
        </div>

        <div class="signatures">
            <div class="signature-col">
                <div class="signature-line">
                    {{ mb_strtoupper($company->legal_representative_name ?? 'REPRESENTANTE LEGAL') }}<br>
                    {{ mb_strtoupper($company->legal_representative_role ?? 'CONTRATANTE') }}
                </div>
            </div>

            <div class="signature-col">
                <div class="signature-line">
                    {{ mb_strtoupper($employee->name ?? 'CONTRATADO') }}
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