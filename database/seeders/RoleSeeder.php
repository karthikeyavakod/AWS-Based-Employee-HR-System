<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system access.',
            ],
            [
                'name' => 'HR Manager',
                'slug' => 'hr-manager',
                'description' => 'Manages HR operations and employee administration.',
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Standard employee self-service access.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                ],
            );
        }
    }
}
