<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // limpar cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | PERMISSIONS
        |--------------------------------------------------------------------------
        */
        $permissions = [

            // USERS
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // COMPANIES
            'companies.view',
            'companies.create',
            'companies.update',
            'companies.delete',

            // WORKS
            'works.view',
            'works.create',
            'works.update',
            'works.delete',

            // EMPLOYEES
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',
            'employees.generate_contract',
            'employees.epi_report',

            // CONTRACTS
            'contracts.view',
            'contracts.generate',

            // EPI
            'epis.view',
            'epis.create',
            'epis.update',
            'epis.delete',
            'epis.deliver',
            'epis.generate_term',
            'epis.report',

            // PAYROLL
            'payroll.view',
            'payroll.process',
            'payroll.close',
            'payroll.payslip',

            // ADVANCES
            'salary_advances.view',
            'salary_advances.create',
            'salary_advances.update',
            'salary_advances.mark_paid',
            'salary_advances.report',

            // TIME
            'time_entries.view',
            'time_entries.create',
            'time_entries.update',
            'time_entries.close',

            // TERMINATIONS
            'terminations.view',
            'terminations.create',
            'terminations.process',
            'terminations.report',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $rh = Role::firstOrCreate(['name' => 'RH']);
        $dp = Role::firstOrCreate(['name' => 'DP']);
        $financeiro = Role::firstOrCreate(['name' => 'Financeiro']);
        $encarregado = Role::firstOrCreate(['name' => 'Encarregado']);
        $operador = Role::firstOrCreate(['name' => 'Operador']);
        $consulta = Role::firstOrCreate(['name' => 'Consulta']);

        /*
        |--------------------------------------------------------------------------
        | PERMISSIONS BY ROLE
        |--------------------------------------------------------------------------
        */

        // ADMIN = tudo
        $admin->syncPermissions(Permission::all());

        // RH
        $rh->syncPermissions([
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.generate_contract',
            'employees.epi_report',

            'contracts.view',
            'contracts.generate',

            'epis.view',
            'epis.create',
            'epis.update',
            'epis.deliver',
            'epis.generate_term',
            'epis.report',
        ]);

        // DP
        $dp->syncPermissions([
            'employees.view',

            'payroll.view',
            'payroll.process',
            'payroll.close',
            'payroll.payslip',

            'time_entries.view',
            'time_entries.create',
            'time_entries.update',
            'time_entries.close',

            'terminations.view',
            'terminations.create',
            'terminations.process',
            'terminations.report',
        ]);

        // FINANCEIRO
        $financeiro->syncPermissions([
            'salary_advances.view',
            'salary_advances.create',
            'salary_advances.update',
            'salary_advances.mark_paid',
            'salary_advances.report',

            'payroll.view',
        ]);

        // ENCARREGADO
        $encarregado->syncPermissions([
            'employees.view',
            'epis.view',
            'epis.deliver',
        ]);

        // OPERADOR
        $operador->syncPermissions([
            'employees.view',
            'employees.create',
            'employees.update',

            'epis.view',
            'epis.deliver',
        ]);

        // CONSULTA
        $consulta->syncPermissions([
            'employees.view',
            'epis.view',
            'payroll.view',
        ]);
    }
}