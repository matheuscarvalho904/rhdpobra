<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Work;
use Illuminate\Database\Seeder;

class DemoStructureSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@obrapeople.com.br')->first();
        $company = Company::where('code', 'DARD')->first();
        $branch = Branch::where('company_id', $company?->id)->where('code', 'MATRIZ')->first();
        $work = Work::where('company_id', $company?->id)->where('code', 'ESCOLA')->first();
        $department = Department::where('code', 'ADM')->first();

        if (! $admin || ! $company) {
            return;
        }

        UserAccessScope::updateOrCreate(
            [
                'user_id' => $admin->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'work_id' => $work?->id,
                'department_id' => $department?->id,
            ],
            [
                'user_id' => $admin->id,
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'work_id' => $work?->id,
                'department_id' => $department?->id,
            ]
        );
    }
}