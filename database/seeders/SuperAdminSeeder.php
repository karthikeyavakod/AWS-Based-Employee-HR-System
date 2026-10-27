<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Password@12345'),
                'status' => 'active',
            ],
        );

        $role = Role::where('slug', 'super-admin')->firstOrFail();

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}
