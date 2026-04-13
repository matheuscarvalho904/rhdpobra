<?php

namespace Database\Seeders;

use App\Models\CboCode;
use App\Models\JobRole;
use Illuminate\Database\Seeder;

class JobRoleSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Ajudante Geral', 'code' => 'AJUDANTE-GERAL', 'cbo' => null],
            ['name' => 'Servente', 'code' => 'SERVENTE', 'cbo' => '717020'],
            ['name' => 'Pedreiro', 'code' => 'PEDREIRO', 'cbo' => '715210'],
            ['name' => 'Carpinteiro', 'code' => 'CARPINTEIRO', 'cbo' => '715505'],
            ['name' => 'Armador', 'code' => 'ARMADOR', 'cbo' => '724315'],
            ['name' => 'Pintor', 'code' => 'PINTOR', 'cbo' => null],
            ['name' => 'Eletricista', 'code' => 'ELETRICISTA', 'cbo' => '951105'],
            ['name' => 'Encanador', 'code' => 'ENCANADOR', 'cbo' => null],
            ['name' => 'Soldador', 'code' => 'SOLDADOR', 'cbo' => '724105'],
            ['name' => 'Auxiliar de Produção', 'code' => 'AUX-PRODUCAO', 'cbo' => null],
            ['name' => 'Auxiliar Administrativo', 'code' => 'AUX-ADM', 'cbo' => null],
            ['name' => 'Assistente Administrativo', 'code' => 'ASSIST-ADM', 'cbo' => '411010'],
            ['name' => 'Assistente de RH', 'code' => 'ASSIST-RH', 'cbo' => null],
            ['name' => 'Analista de RH', 'code' => 'ANALISTA-RH', 'cbo' => '252405'],
            ['name' => 'Analista de DP', 'code' => 'ANALISTA-DP', 'cbo' => null],
            ['name' => 'Assistente Financeiro', 'code' => 'ASSIST-FIN', 'cbo' => null],
            ['name' => 'Analista Financeiro', 'code' => 'ANALISTA-FIN', 'cbo' => null],
            ['name' => 'Comprador', 'code' => 'COMPRADOR', 'cbo' => null],
            ['name' => 'Almoxarife', 'code' => 'ALMOXARIFE', 'cbo' => '414105'],
            ['name' => 'Mecânico', 'code' => 'MECANICO', 'cbo' => '910105'],
            ['name' => 'Lubrificador', 'code' => 'LUBRIFICADOR', 'cbo' => null],
            ['name' => 'Borracheiro', 'code' => 'BORRACHEIRO', 'cbo' => null],
            ['name' => 'Motorista', 'code' => 'MOTORISTA', 'cbo' => null],
            ['name' => 'Motorista de Caminhão', 'code' => 'MOTORISTA-CAMINHAO', 'cbo' => '782510'],
            ['name' => 'Operador de Máquinas', 'code' => 'OPERADOR-MAQUINAS', 'cbo' => '715125'],
            ['name' => 'Operador de Escavadeira', 'code' => 'OPERADOR-ESCAVADEIRA', 'cbo' => '715125'],
            ['name' => 'Operador de Motoniveladora', 'code' => 'OPERADOR-MOTONIVELADORA', 'cbo' => '715125'],
            ['name' => 'Operador de Pá Carregadeira', 'code' => 'OPERADOR-PA-CARREGADEIRA', 'cbo' => '715125'],
            ['name' => 'Operador de Rolo', 'code' => 'OPERADOR-ROLO', 'cbo' => '715125'],
            ['name' => 'Operador de Usina', 'code' => 'OPERADOR-USINA', 'cbo' => null],
            ['name' => 'Encarregado de Obra', 'code' => 'ENCARREGADO-OBRA', 'cbo' => '710215'],
            ['name' => 'Mestre de Obras', 'code' => 'MESTRE-OBRAS', 'cbo' => '710205'],
            ['name' => 'Engenheiro Civil', 'code' => 'ENGENHEIRO-CIVIL', 'cbo' => '214205'],
            ['name' => 'Engenheiro de Produção', 'code' => 'ENGENHEIRO-PRODUCAO', 'cbo' => null],
            ['name' => 'Técnico de Segurança do Trabalho', 'code' => 'TECNICO-SESMT', 'cbo' => '351605'],
            ['name' => 'Técnico em Edificações', 'code' => 'TECNICO-EDIFICACOES', 'cbo' => '312320'],
            ['name' => 'Topógrafo', 'code' => 'TOPOGRAFO', 'cbo' => '312315'],
            ['name' => 'Apontador', 'code' => 'APONTADOR', 'cbo' => null],
            ['name' => 'Vigia', 'code' => 'VIGIA', 'cbo' => '514320'],
            ['name' => 'Zelador', 'code' => 'ZELADOR', 'cbo' => null],
        ];

        foreach ($items as $item) {
            $cbo = $item['cbo']
                ? CboCode::where('code', $item['cbo'])->first()
                : null;

            JobRole::updateOrCreate(
                ['code' => $item['code']],
                [
                    'cbo_code_id' => $cbo?->id,
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}