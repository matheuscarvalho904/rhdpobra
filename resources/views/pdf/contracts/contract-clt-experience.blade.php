<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Experiência</title>

    <style>
        @page {
            margin: 24px 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.2;
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

        .page-break {
            page-break-after: always;
        }

        .center {
            text-align: center;
        }

        .spacer-top {
            margin-top: 40px;
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

        $startDate = $employee->experience_start_date
            ? \Carbon\Carbon::parse($employee->experience_start_date)
            : ($employee->admission_date ? \Carbon\Carbon::parse($employee->admission_date) : now());

        $firstDays = (int) ($employee->experience_days_first ?? 30);
        $secondDays = (int) ($employee->experience_days_second ?? 0);
        $totalDays = (int) ($employee->experience_total_days ?? ($firstDays + $secondDays));

        if ($totalDays <= 0) {
            $totalDays = $firstDays + $secondDays;
        }

        $firstEndDate = (clone $startDate)->addDays(max($firstDays - 1, 0));
        $finalEndDate = (clone $startDate)->addDays(max($totalDays - 1, 0));

        $salary = number_format((float) ($employee->salary ?? 0), 2, ',', '.');

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

        $city = $company->city ?? $branch->city ?? $employee->city ?? 'Aripuanã';
        $signatureDate = $startDate;
    @endphp

    <div class="page">
        <div class="title">Contrato de Experiência</div>

        <div class="paragraph">
            Pelo presente instrumento particular de Contrato de Experiência, a empresa
            <strong>{{ mb_strtoupper($company->name ?? '-') }}</strong>,
            com sede em {{ $companyAddress ?: 'endereço não informado' }},
            inscrita no CNPJ sob nº <strong>{{ $formatCnpj($company->cnpj ?? null) }}</strong>,
            denominada a seguir <strong>EMPREGADORA</strong>,
            e o(a) Sr.(a) <strong>{{ mb_strtoupper($employee->name ?? '-') }}</strong>,
            domiciliado(a) em {{ $employeeAddress ?: 'endereço não informado' }},
            portador(a) do CPF nº <strong>{{ $formatCpf($employee->cpf ?? null) }}</strong>,
            doravante denominado(a) <strong>EMPREGADO(A)</strong>,
            celebram o presente Contrato Individual de Trabalho para fins de experiência,
            conforme legislação trabalhista em vigor, regido pelas cláusulas abaixo e demais disposições legais vigentes.
        </div>

        <div class="clause">
            <strong>1º.</strong> O EMPREGADO trabalhará para a EMPREGADORA na função de
            <strong>{{ mb_strtoupper($jobRole?->name ?? 'FUNÇÃO') }}</strong>
            e mais as funções que vierem a ser objeto de ordens verbais, cartas, ou avisos,
            segundo as necessidades da EMPREGADORA, desde que compatíveis com suas atribuições.
        </div>

        <div class="clause">
            <strong>2º.</strong> O local de trabalho situa-se em
            <strong>{{ $work?->name ?? ($company->city ?? 'local a definir') }}</strong>,
            podendo a EMPREGADORA, a qualquer tempo, transferir o EMPREGADO
            a título temporário ou definitivo, tanto no âmbito da unidade para a qual foi admitido,
            como para outras, em qualquer localidade deste estado ou de outro dentro do país.
        </div>

        <div class="clause">
            <strong>3º.</strong> O horário de trabalho do EMPREGADO será o seguinte:
            início do expediente às <strong>07:00</strong>,
            saída para intervalo às <strong>11:00</strong>,
            entrada do intervalo às <strong>13:00</strong>
            e final do expediente às <strong>17:00</strong>.
        </div>

        <div class="clause">
            <strong>4º.</strong> O EMPREGADO receberá a remuneração de
            <strong>R$ {{ $salary }}</strong>
            por mês, a ser paga até o <strong>5º (quinto) dia útil</strong> do mês subsequente ao trabalhado.
        </div>

        <div class="clause">
            <strong>5º.</strong> O prazo deste contrato é de
            <strong>{{ $firstDays }} {{ $firstDays === 1 ? 'dia' : 'dias' }}</strong>,
            com início em <strong>{{ $startDate->format('d/m/Y') }}</strong>
            e término em <strong>{{ $firstEndDate->format('d/m/Y') }}</strong>.
        </div>

        <div class="clause">
            <strong>6º.</strong> Além dos descontos previstos na Lei, reserva-se a EMPREGADORA
            o direito de descontar do EMPREGADO as importâncias correspondentes aos danos causados por ele,
            nos termos da legislação aplicável.
        </div>

        <div class="clause">
            <strong>7º.</strong> O EMPREGADO fica ciente do Regulamento da EMPREGADORA
            e das Normas de Segurança fornecidas, sob pena de ser punido por falta grave,
            nos termos da legislação vigente e demais disposições inerentes à segurança e medicina do trabalho.
        </div>

        <div class="clause">
            <strong>8º.</strong> Permanecendo o EMPREGADO a serviço após o término da experiência,
            continuará em vigor as cláusulas constantes deste contrato, passando o vínculo a vigorar
            por prazo indeterminado, salvo disposição legal em contrário.
        </div>

        <div class="clause">
            <strong>9º. Cláusula de Transferência de Colaborador:</strong><br>

            <strong>1. Transferência de Local de Trabalho:</strong><br>
            <strong>1.1.</strong> O Colaborador reconhece e concorda que a Empresa reserva o direito de transferi-lo
            para outro local de trabalho, seja dentro da mesma cidade, estado ou país,
            ou para outra localidade, caso seja necessário para as operações da Empresa.<br><br>

            <strong>2. Motivos de Transferência:</strong><br>
            <strong>2.1.</strong> A Empresa pode tomar a decisão de transferir o Colaborador por motivos que incluem,
            mas não se limitam a:<br>
            a) Necessidades operacionais da Empresa;<br>
            b) Expansão ou consolidação de operações;<br>
            c) Reorganização da estrutura organizacional;<br>
            d) Desenvolvimento de carreira do Colaborador;<br>
            e) Promoções ou oportunidades de crescimento profissional.<br>

            <strong>3. Notificação e Consulta:</strong><br>
            <strong>3.1.</strong> A Empresa se esforçará para notificar o Colaborador com a maior antecedência possível
            sobre a transferência, a menos que circunstâncias excepcionais impeçam uma notificação antecipada.<br>
            <strong>3.2.</strong> O Colaborador reconhece que, ao aceitar a oferta de emprego da Empresa,
            está ciente de que pode ser transferido de acordo com esta cláusula.
        </div>

        <div class="footer-text">
            Tendo assim contratado, assinam o presente instrumento, em duas vias,
            na presença das testemunhas abaixo.
        </div>

        <div class="city-date">
            {{ mb_strtoupper($city) }}, {{ $signatureDate->locale('pt_BR')->translatedFormat('d \\d\\e F \\d\\e Y') }}.
        </div>

        <div class="signatures">
            <div class="signature-col">
                <div class="signature-line">
                    EMPREGADORA
                </div>
            </div>

            <div class="signature-col">
                <div class="signature-line">
                    {{ mb_strtoupper($employee->name ?? 'EMPREGADO') }}
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

    @if($secondDays > 0)
        <div class="page-break"></div>

        <div class="page">
            <div class="title">Prorrogação de Contrato de Experiência</div>

            <div class="paragraph center spacer-top">
                O Contrato de Experiência firmado, que deveria terminar em
                <strong>{{ $firstEndDate->format('d/m/Y') }}</strong>,
                fica prorrogado até
                <strong>{{ $finalEndDate->format('d/m/Y') }}</strong>.
            </div>

            <div class="city-date spacer-top">
                ....................................., ........ de ..................................... de ....................
            </div>

            <div class="signatures">
                <div class="signature-col">
                    <div class="signature-line">
                        EMPREGADORA
                    </div>
                </div>

                <div class="signature-col">
                    <div class="signature-line">
                        {{ mb_strtoupper($employee->name ?? 'EMPREGADO') }}
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
    @endif

</body>
</html>