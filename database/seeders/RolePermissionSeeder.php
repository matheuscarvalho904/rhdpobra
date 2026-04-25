<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissions = Permission::pluck('name')->all();

        $admin = Role::findByName('Administrador', 'web');
        $admin->syncPermissions($allPermissions);

        $rh = Role::findByName('RH', 'web');
        $rh->syncPermissions([
            'employees.view_any',
            'employees.view',
            'employees.create',
            'employees.update',
            'companies.view_any',
            'companies.view',
            'branches.view_any',
            'branches.view',
            'works.view_any',
            'works.view',
            'departments.view_any',
            'departments.view',
            'departments.create',
            'departments.update',
            'cost_centers.view_any',
            'cost_centers.view',
            'job_roles.view_any',
            'job_roles.view',
            'job_roles.create',
            'job_roles.update',
            'cbo_codes.view_any',
            'cbo_codes.view',
            'labor_unions.view_any',
            'labor_unions.view',
            'contract_types.view_any',
            'contract_types.view',
            'document_types.view_any',
            'document_types.view',
            'document_types.create',
            'document_types.update',
            'banks.view_any',
            'banks.view',
            'work_shifts.view_any',
            'work_shifts.view',
            'holidays.view_any',
            'holidays.view',
            'users.view_any',
            'users.view',
            'user_access_scopes.view_any',
            'user_access_scopes.view',
        ]);

        $dp = Role::findByName('DP', 'web');
        $dp->syncPermissions([
            'employees.view_any',
            'employees.view',
            'employees.create',
            'employees.update',
            'companies.view_any',
            'companies.view',
            'branches.view_any',
            'branches.view',
            'works.view_any',
            'works.view',
            'departments.view_any',
            'departments.view',
            'cost_centers.view_any',
            'cost_centers.view',
            'job_roles.view_any',
            'job_roles.view',
            'cbo_codes.view_any',
            'cbo_codes.view',
            'labor_unions.view_any',
            'labor_unions.view',
            'contract_types.view_any',
            'contract_types.view',
            'document_types.view_any',
            'document_types.view',
            'banks.view_any',
            'banks.view',
            'work_shifts.view_any',
            'work_shifts.view',
            'holidays.view_any',
            'holidays.view',
            'financial_categories.view_any',
            'financial_categories.view',
        ]);

        $financeiro = Role::findByName('Financeiro', 'web');
        $financeiro->syncPermissions([
            'companies.view_any',
            'companies.view',
            'branches.view_any',
            'branches.view',
            'works.view_any',
            'works.view',
            'cost_centers.view_any',
            'cost_centers.view',
            'financial_categories.view_any',
            'financial_categories.view',
            'financial_categories.create',
            'financial_categories.update',
        ]);

        $encarregado = Role::findByName('Encarregado', 'web');
        $encarregado->syncPermissions([

            'works.view_any',
            'works.view',
            'departments.view_any',
            'departments.view',
            'job_roles.view_any',
            'job_roles.view',
            'work_shifts.view_any',
            'work_shifts.view',
            'holidays.view_any',
            'holidays.view',
        ]);

        $gestor = Role::findByName('Gestor de Obra', 'web');
        $gestor->syncPermissions([
            'companies.view_any',
            'companies.view',
            'branches.view_any',
            'branches.view',
            'works.view_any',
            'works.view',
            'departments.view_any',
            'departments.view',
            'cost_centers.view_any',
            'cost_centers.view',
            'job_roles.view_any',
            'job_roles.view',
            'work_shifts.view_any',
            'work_shifts.view',
            'holidays.view_any',
            'holidays.view',
        ]);

        $consulta = Role::findByName('Consulta', 'web');
        $consulta->syncPermissions([
            'employees.view_any',
            'employees.view',
            'employees.create',
            'employees.update',
            'companies.view_any',
            'companies.view',
            'branches.view_any',
            'branches.view',
            'works.view_any',
            'works.view',
            'departments.view_any',
            'departments.view',
            'cost_centers.view_any',
            'cost_centers.view',
            'cbo_codes.view_any',
            'cbo_codes.view',
            'job_roles.view_any',
            'job_roles.view',
            'labor_unions.view_any',
            'labor_unions.view',
            'contract_types.view_any',
            'contract_types.view',
            'document_types.view_any',
            'document_types.view',
            'banks.view_any',
            'banks.view',
            'work_shifts.view_any',
            'work_shifts.view',
            'holidays.view_any',
            'holidays.view',
            'financial_categories.view_any',
            'financial_categories.view',
            'users.view_any',
            'users.view',
            'user_access_scopes.view_any',
            'user_access_scopes.view',
        ]);
    }
}