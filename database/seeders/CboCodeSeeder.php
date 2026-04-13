<?php

namespace Database\Seeders;

use App\Models\CboCode;
use Illuminate\Database\Seeder;

class CboCodeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            // ======================
            // OPERACIONAL - OBRA
            // ======================
            ['code' => '717020', 'name' => 'Servente de obras'],
            ['code' => '715210', 'name' => 'Pedreiro'],
            ['code' => '715505', 'name' => 'Carpinteiro'],
            ['code' => '724315', 'name' => 'Armador de estrutura de concreto'],
            ['code' => '710205', 'name' => 'Mestre de obras'],
            ['code' => '710215', 'name' => 'Encarregado de obras'],
            ['code' => '312320', 'name' => 'Técnico em edificações'],
            ['code' => '312315', 'name' => 'Topógrafo'],
            ['code' => '312305', 'name' => 'Técnico em topografia'],

            // ======================
            // MÁQUINAS E FROTA
            // ======================
            ['code' => '715125', 'name' => 'Operador de máquinas de construção civil e mineração'],
            ['code' => '715130', 'name' => 'Operador de motoniveladora'],
            ['code' => '715135', 'name' => 'Operador de escavadeira'],
            ['code' => '715140', 'name' => 'Operador de pá carregadeira'],
            ['code' => '715145', 'name' => 'Operador de rolo compactador'],
            ['code' => '715120', 'name' => 'Operador de trator'],
            ['code' => '782510', 'name' => 'Motorista de caminhão'],
            ['code' => '782305', 'name' => 'Motorista de caminhão basculante'],
            ['code' => '782310', 'name' => 'Motorista de caminhão betoneira'],
            ['code' => '782315', 'name' => 'Motorista operador de munck'],

            // ======================
            // MANUTENÇÃO
            // ======================
            ['code' => '910105', 'name' => 'Mecânico de manutenção de máquinas pesadas'],
            ['code' => '911305', 'name' => 'Mecânico de manutenção de veículos automotores'],
            ['code' => '911310', 'name' => 'Eletricista de veículos'],
            ['code' => '724105', 'name' => 'Soldador'],
            ['code' => '724205', 'name' => 'Caldeireiro'],
            ['code' => '724220', 'name' => 'Montador de estruturas metálicas'],

            // ======================
            // SEGURANÇA E APOIO
            // ======================
            ['code' => '351605', 'name' => 'Técnico em segurança do trabalho'],
            ['code' => '517405', 'name' => 'Vigilante'],
            ['code' => '514320', 'name' => 'Vigia'],
            ['code' => '516210', 'name' => 'Auxiliar de limpeza'],
            ['code' => '513505', 'name' => 'Cozinheiro geral'],
            ['code' => '513205', 'name' => 'Auxiliar de cozinha'],

            // ======================
            // ADMINISTRATIVO
            // ======================
            ['code' => '411010', 'name' => 'Assistente administrativo'],
            ['code' => '411005', 'name' => 'Auxiliar administrativo'],
            ['code' => '414105', 'name' => 'Almoxarife'],
            ['code' => '414110', 'name' => 'Auxiliar de almoxarifado'],
            ['code' => '413110', 'name' => 'Apontador de obras'],
            ['code' => '252405', 'name' => 'Analista de recursos humanos'],
            ['code' => '252210', 'name' => 'Analista de planejamento'],
            ['code' => '252305', 'name' => 'Analista financeiro'],
            ['code' => '252315', 'name' => 'Analista contábil'],

            // ======================
            // ENGENHARIA
            // ======================
            ['code' => '214205', 'name' => 'Engenheiro civil'],
            ['code' => '214210', 'name' => 'Engenheiro de produção'],
            ['code' => '214305', 'name' => 'Engenheiro de segurança do trabalho'],
            ['code' => '214915', 'name' => 'Engenheiro ambiental'],

            // ======================
            // GESTÃO
            // ======================
            ['code' => '141405', 'name' => 'Gerente de obras'],
            ['code' => '141410', 'name' => 'Gerente administrativo'],
            ['code' => '141415', 'name' => 'Gerente financeiro'],
            ['code' => '142105', 'name' => 'Supervisor de produção'],
            ['code' => '142110', 'name' => 'Supervisor administrativo'],

            // ======================
            // ESPECÍFICOS DE OBRA ASFALTO
            // ======================
            ['code' => '715535', 'name' => 'Operador de usina de asfalto'],
            ['code' => '715540', 'name' => 'Operador de vibroacabadora'],
            ['code' => '715545', 'name' => 'Operador de fresadora'],
            ['code' => '715550', 'name' => 'Rasteleiro de asfalto'],
        ];

        foreach ($items as $item) {
            CboCode::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}