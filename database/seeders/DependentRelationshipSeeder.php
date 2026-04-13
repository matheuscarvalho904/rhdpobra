<?php

namespace Database\Seeders;

use App\Models\DependentRelationship;
use Illuminate\Database\Seeder;

class DependentRelationshipSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Filho(a)', 'code' => 'child'],
            ['name' => 'Cônjuge', 'code' => 'spouse'],
            ['name' => 'Companheiro(a)', 'code' => 'partner'],
            ['name' => 'Enteado(a)', 'code' => 'stepchild'],
            ['name' => 'Pai', 'code' => 'father'],
            ['name' => 'Mãe', 'code' => 'mother'],
            ['name' => 'Irmão(ã)', 'code' => 'sibling'],
            ['name' => 'Outros', 'code' => 'other'],
        ];

        foreach ($items as $item) {
            DependentRelationship::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}