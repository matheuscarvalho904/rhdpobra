<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $entities = [
            'companies',
            'branches',
            'works',
            'departments',
            'cost_centers',
            'cbo_codes',
            'job_roles',
            'labor_unions',
            'contract_types',
            'document_types',
            'banks',
            'work_shifts',
            'holidays',
            'financial_categories',
            'users',
            'user_access_scopes',
        ];

        $actions = [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
            'export',
            'import',
            'process',
            'close',
            'reopen',
            'approve',
        ];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$entity}.{$action}", 'web');
            }
        }
    }
}