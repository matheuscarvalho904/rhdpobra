<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Trabalho</title>

    <style>
        @page {
            margin: 18px 22px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.20;
            color: #111827;
        }

        .page {
            width: 100%;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .paragraph {
            text-align: justify;
            margin-bottom: 5px;
        }

        .clause {
            margin-bottom: 6px;
            text-align: justify;
        }

        .footer-text {
            margin-top: 10px;
            text-align: justify;
        }

        .city-date {
            margin-top: 10px;
        }

        .signatures {
            margin-top: 18px;
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
            margin-top: 28px;
            border-top: 1px solid #111827;
            padding-top: 5px;
            font-size: 9.5px;
        }

        .witnesses {
            margin-top: 10px;
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
            margin-top: 24px;
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

        $admissionDate = $employee->admission_date
            ? \Carbon\Carbon::parse($employee->admission_date)
            : now();

        $city = $company->city ?? $branch->city ?? $employee->city ?? 'Aripuanã';
    @endphp

    <div class="page">
        <div class="title">Contrato de Trabalho</div>

        <div class="paragraph">
            Pelo presente instrumento e na melhor forma de direito, as partes:
        </div>

        <div class="paragraph">
            <strong>1. EMPREGADOR:</strong>
            <strong>{{ mb_strtoupper($company->name ?? '-') }}</strong>,
            com sede em {{ $companyAddress ?: 'endereço não informado' }},
            inscrita no CNPJ sob nº <strong>{{ $formatCnpj($company->cnpj ?? null) }}</strong>,
            representada neste ato por seu responsável legal.
        </div>

        <div class="paragraph">
            <strong>2. EMPREGADO:</strong>
            <strong>{{ mb_strtoupper($employee->name ?? '-') }}</strong>,
            portador do CPF/MF nº <strong>{{ $formatCpf($employee->cpf ?? null) }}</strong>,
            residente e domiciliado em {{ $employeeAddress ?: 'endereço não informado' }}.
        </div>

        <div class="clause">
            <strong>CLÁUSULA I</strong><br><br>
            O EMPREGADO acima designado obriga-se a prestar seus serviços no quadro de funcionários do EMPREGADOR
            para exercer as funções de <strong>{{ mb_strtoupper($jobRole?->name ?? 'FUNÇÃO') }}</strong>,
            mediante a remuneração mensal de <strong>R$ {{ $salary }}</strong>,
            a ser paga até o quinto dia útil de cada mês. Ressalva-se ao EMPREGADOR o direito de proceder à transferência
            do empregado para outro cargo ou função que entenda que este demonstre melhor capacidade de adaptação,
            desde que compatível com sua condição pessoal.
        </div>

        <div class="clause">
            <strong>CLÁUSULA II</strong><br><br>
            A prestação do serviço se dará de segunda a sexta, no horário de 7:00hs às 11:00hs e de 13:00hs às 17:00hs,
            assegurado o direito ao gozo do intervalo para a realização de suas refeições, podendo a jornada sofrer alterações
            conforme a necessidade operacional da empresa, respeitados os limites legais.
        </div>

        <div class="clause">
            <strong>CLÁUSULA III</strong><br><br>
            O EMPREGADO está ciente e concorda que a prestação de seus serviços se dará tanto na localidade da celebração
            do Contrato de Trabalho como em qualquer outra cidade, capital ou vila do território nacional,
            nos termos da legislação aplicável e conforme necessidade da EMPREGADORA.
        </div>

        <div class="clause">
            <strong>CLÁUSULA IV</strong><br><br>
            O EMPREGADO declara estar recebendo ou tomando ciência, no ato da assinatura deste contrato,
            do Regulamento Interno da Empresa, cujas cláusulas passam a integrar este contrato de trabalho,
            e que a violação de qualquer delas implicará em sanção, cuja graduação dependerá da gravidade da ocorrência,
            podendo culminar inclusive na rescisão do contrato de trabalho.
        </div>

        <div class="clause">
            <strong>CLÁUSULA V</strong><br><br>
            O EMPREGADO, sempre que causar algum prejuízo ao empregador, resultante de qualquer conduta dolosa ou culposa,
            ficará obrigado a ressarcir ao EMPREGADOR por todos os danos causados, ficando desde já autorizado o desconto
            da importância correspondente ao prejuízo, nos termos da legislação vigente.
        </div>

        <div class="clause">
            <strong>CLÁUSULA VI</strong><br><br>
            O EMPREGADO fica ciente das normas de segurança do trabalho e compromete-se a utilizar corretamente
            os Equipamentos de Proteção Individual (EPI) fornecidos pela empresa, bem como a observar todas as orientações
            técnicas e operacionais relacionadas à segurança, saúde e medicina do trabalho.
        </div>

        <div class="clause">
            <strong>CLÁUSULA VII</strong><br><br>
            Fica instituído o regime de compensação de jornada e banco de horas, quando aplicável,
            nos termos da legislação vigente e das normas internas da empresa.
        </div>

        <div class="clause">
            <strong>CLÁUSULA VIII</strong><br><br>
            O presente Contrato de Trabalho vigorará por prazo indeterminado, com início em
            <strong>{{ $admissionDate->format('d/m/Y') }}</strong>, produzindo todos os seus efeitos legais a partir desta data.
        </div>

        <div class="clause">
            <strong>CLÁUSULA IX</strong><br><br>
            Cláusula de Transferência de Colaborador:<br><br>

            <strong>1. Transferência de Local de Trabalho:</strong><br>
            <strong>1.1.</strong> O Colaborador reconhece e concorda que a Empresa reserva o direito de transferi-lo
            para outro local de trabalho, seja dentro da mesma cidade, estado ou país, ou para outra localidade,
            caso seja necessário para as operações da Empresa.<br><br>

            <strong>2. Motivos de Transferência:</strong><br>
            <strong>2.1.</strong> A Empresa poderá tomar a decisão de transferir o Colaborador por motivos que incluem,
            mas não se limitam a:<br>
            a) Necessidades operacionais da Empresa;<br>
            b) Expansão ou consolidação de operações;<br>
            c) Reorganização da estrutura organizacional;<br>
            d) Desenvolvimento de carreira do Colaborador;<br>
            e) Promoções ou oportunidades de crescimento profissional.<br><br>

            <strong>3. Notificação e Consulta:</strong><br>
            <strong>3.1.</strong> A Empresa se esforçará para notificar o Colaborador com a maior antecedência possível
            sobre a transferência, salvo circunstâncias excepcionais.<br>
            <strong>3.2.</strong> O Colaborador reconhece que, ao aceitar a oferta de emprego da Empresa,
            está ciente de que pode ser transferido de acordo com esta cláusula.
        </div>

        <div class="footer-text">
            E por estarem de pleno acordo, as partes contratantes assinam o presente Contrato em duas vias,
            ficando a primeira em poder do EMPREGADOR e a segunda com o EMPREGADO,
            que dela dará o competente recibo.
        </div>

        <div class="city-date">
            {{ mb_strtoupper($city) }}, {{ $admissionDate->locale('pt_BR')->translatedFormat('d \\d\\e F \\d\\e Y') }}.
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

</body>
</html>