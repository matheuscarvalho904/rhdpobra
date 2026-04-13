<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,

            CompanySeeder::class,
            BranchSeeder::class,
            WorkSeeder::class,

            DepartmentSeeder::class,
            CostCenterSeeder::class,
            CboCodeSeeder::class,
            JobRoleSeeder::class,
            LaborUnionSeeder::class,
            ContractTypeSeeder::class,
            DocumentTypeSeeder::class,
            BankSeeder::class,

            WorkShiftSeeder::class,
            WorkShiftDaySeeder::class,

            HolidayTypeSeeder::class,
            HolidaySeeder::class,

            FinancialCategorySeeder::class,

            EmployeeStatusSeeder::class,
            SalaryTypeSeeder::class,
            BankAccountTypeSeeder::class,
            DependentRelationshipSeeder::class,
            SalaryAdjustmentReasonSeeder::class,
            TransferReasonSeeder::class,

            AttendanceOccurrenceSeeder::class,
            TimeImportStatusSeeder::class,
            TimeClosingStatusSeeder::class,
            HourBankMovementTypeSeeder::class,

            PayrollEventSeeder::class,

            DemoStructureSeeder::class,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}