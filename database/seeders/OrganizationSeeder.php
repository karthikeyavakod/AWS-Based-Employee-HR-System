<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'HR' => ['name' => 'Human Resources', 'description' => 'People operations, recruitment, and employee relations.'],
            'ENG' => ['name' => 'Engineering', 'description' => 'Software development and technical operations.'],
            'FIN' => ['name' => 'Finance', 'description' => 'Financial planning, accounting, and payroll.'],
            'SAL' => ['name' => 'Sales', 'description' => 'Customer acquisition and account management.'],
            'OPS' => ['name' => 'Operations', 'description' => 'Business operations and process management.'],
        ];

        foreach ($departments as $code => $data) {
            Department::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'status' => 'active',
                ],
            );
        }

        $designations = [
            'HR' => [
                ['name' => 'HR Manager', 'code' => 'HR-MGR'],
                ['name' => 'HR Executive', 'code' => 'HR-EXEC'],
            ],
            'ENG' => [
                ['name' => 'Engineering Manager', 'code' => 'ENG-MGR'],
                ['name' => 'Senior Software Engineer', 'code' => 'SSE'],
                ['name' => 'Software Engineer', 'code' => 'SE'],
            ],
            'FIN' => [
                ['name' => 'Finance Manager', 'code' => 'FIN-MGR'],
                ['name' => 'Accountant', 'code' => 'ACC'],
            ],
            'SAL' => [
                ['name' => 'Sales Manager', 'code' => 'SAL-MGR'],
                ['name' => 'Sales Executive', 'code' => 'SAL-EXEC'],
            ],
            'OPS' => [
                ['name' => 'Operations Manager', 'code' => 'OPS-MGR'],
                ['name' => 'Operations Executive', 'code' => 'OPS-EXEC'],
            ],
        ];

        foreach ($designations as $departmentCode => $items) {
            $department = Department::where('code', $departmentCode)->firstOrFail();

            foreach ($items as $item) {
                Designation::updateOrCreate(
                    ['department_id' => $department->id, 'name' => $item['name']],
                    [
                        'code' => $item['code'],
                        'status' => 'active',
                    ],
                );
            }
        }

        $employeeRole = Role::where('slug', 'employee')->firstOrFail();
        $employees = [
            [
                'email' => 'priya.sharma@example.com',
                'name' => 'Priya Sharma',
                'employee_code' => 'EMP-001',
                'department' => 'HR',
                'designation' => 'HR Manager',
                'phone' => '+91-9876500001',
            ],
            [
                'email' => 'rahul.mehta@example.com',
                'name' => 'Rahul Mehta',
                'employee_code' => 'EMP-002',
                'department' => 'ENG',
                'designation' => 'Engineering Manager',
                'phone' => '+91-9876500002',
            ],
            [
                'email' => 'anita.deshmukh@example.com',
                'name' => 'Anita Deshmukh',
                'employee_code' => 'EMP-003',
                'department' => 'ENG',
                'designation' => 'Senior Software Engineer',
                'phone' => '+91-9876500003',
            ],
            [
                'email' => 'vikram.singh@example.com',
                'name' => 'Vikram Singh',
                'employee_code' => 'EMP-004',
                'department' => 'FIN',
                'designation' => 'Accountant',
                'phone' => '+91-9876500004',
            ],
            [
                'email' => 'sneha.patel@example.com',
                'name' => 'Sneha Patel',
                'employee_code' => 'EMP-005',
                'department' => 'SAL',
                'designation' => 'Sales Executive',
                'phone' => '+91-9876500005',
            ],
            [
                'email' => 'arjun.nair@example.com',
                'name' => 'Arjun Nair',
                'employee_code' => 'EMP-006',
                'department' => 'OPS',
                'designation' => 'Operations Executive',
                'phone' => '+91-9876500006',
            ],
        ];

        foreach ($employees as $index => $data) {
            $department = Department::where('code', $data['department'])->firstOrFail();
            $designation = Designation::where('department_id', $department->id)
                ->where('name', $data['designation'])
                ->firstOrFail();

            $nameParts = explode(' ', $data['name'], 2);
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('Password@12345'),
                    'status' => 'active',
                    'must_change_password' => false,
                ],
            );
            $user->roles()->syncWithoutDetaching([$employeeRole->id]);

            Employee::updateOrCreate(
                ['employee_code' => $data['employee_code']],
                [
                    'user_id' => $user->id,
                    'department_id' => $department->id,
                    'designation_id' => $designation->id,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'phone' => $data['phone'],
                    'gender' => null,
                    'date_of_birth' => null,
                    'joining_date' => now()->subMonths(6 + $index)->toDateString(),
                    'employment_type' => 'full_time',
                    'status' => 'active',
                ],
            );
        }
    }
}
